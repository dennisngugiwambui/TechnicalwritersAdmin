<?php

namespace App\Services;

use App\Models\Finance;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MpesaService
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $shortcode;
    protected $passkey;
    protected $b2cShortcode;
    protected $b2cInitiator;
    protected $b2cPassword;
    protected $callbackUrl;
    protected $timeoutUrl;
    protected $resultUrl;
    protected $environment;
    protected $exchangeRate;

    public function __construct()
    {
        $this->environment = config('mpesa.environment', 'sandbox');
        $this->baseUrl = $this->environment === 'production' 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->b2cShortcode = config('mpesa.b2c_shortcode', $this->shortcode);
        $this->b2cInitiator = config('mpesa.b2c_initiator');
        $this->b2cPassword = config('mpesa.b2c_password');
        $this->callbackUrl = config('mpesa.callback_url');
        $this->timeoutUrl = config('mpesa.timeout_url');
        $this->resultUrl = config('mpesa.result_url');
        $this->exchangeRate = config('mpesa.exchange_rate', 120);
    }

    /**
     * Get access token from M-Pesa API
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        // Check if token is cached
        if (Cache::has('mpesa_access_token')) {
            return Cache::get('mpesa_access_token');
        }

        try {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
            
            $result = $response->json();
            
            if (isset($result['access_token'])) {
                // Cache token for 50 minutes (tokens last 1 hour)
                Cache::put('mpesa_access_token', $result['access_token'], 50 * 60);
                return $result['access_token'];
            }
            
            Log::error('M-Pesa access token error: ' . json_encode($result));
            return null;
        } catch (\Exception $e) {
            Log::error('M-Pesa access token exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process payment to a writer via M-Pesa
     *
     * @param int $transactionId Finance transaction ID
     * @return array
     */
    public function processPayment($transactionId)
    {
        try {
            // Get transaction details
            $transaction = Finance::with('user')->findOrFail($transactionId);
            
            // Ensure transaction is pending and is a withdrawal
            if ($transaction->status !== Finance::STATUS_PENDING || 
                $transaction->transaction_type !== Finance::TYPE_WITHDRAWAL) {
                return [
                    'success' => false,
                    'message' => 'Invalid transaction status or type'
                ];
            }
            
            // Get writer profile for payment details
            $writer = $transaction->user;
            $writerProfile = $writer->writerProfile;
            
            if (!$writerProfile) {
                return [
                    'success' => false,
                    'message' => 'Writer profile not found'
                ];
            }
            
            // Get phone number from payment details
            $phoneNumber = $this->extractPhoneNumber($writerProfile->payment_details);
            
            if (!$phoneNumber) {
                return [
                    'success' => false,
                    'message' => 'Valid phone number not found in payment details'
                ];
            }
            
            // Calculate amount in local currency if exchange rate is set
            if (!$transaction->local_currency_amount && $transaction->amount > 0) {
                $transaction->exchange_rate = $this->exchangeRate;
                $transaction->local_currency_amount = round($transaction->amount * $this->exchangeRate, 2);
                $transaction->save();
            }
            
            $amount = intval($transaction->local_currency_amount ?? ($transaction->amount * $this->exchangeRate));
            
            // B2C payment
            $result = $this->sendB2CPayment($phoneNumber, $amount, $transaction);
            
            if ($result['success']) {
                // Update transaction status
                $transaction->status = Finance::STATUS_PROCESSING;
                $transaction->payment_reference = $result['conversation_id'] ?? null;
                $transaction->save();
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('M-Pesa payment processing error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send B2C payment to recipient
     *
     * @param string $phoneNumber
     * @param float $amount
     * @param \App\Models\Finance $transaction
     * @return array
     */
    protected function sendB2CPayment($phoneNumber, $amount, $transaction)
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Failed to get access token'
            ];
        }
        
        // Format phone number (remove + and country code if present)
        $phoneNumber = preg_replace('/^\+?254/', '254', $phoneNumber);
        $phoneNumber = preg_replace('/^\+?0/', '254', $phoneNumber);
        
        // Ensure minimum amount (M-Pesa requires at least 10 KES)
        $amount = max(10, intval($amount));
        
        // Generate security credential
        $securityCredential = $this->generateSecurityCredential();
        
        // Generate a unique transaction ID
        $commandId = 'BusinessPayment'; // Options: SalaryPayment, BusinessPayment, PromotionPayment
        $transactionId = 'WP' . time() . rand(100, 999);
        
        try {
            $response = Http::withToken($token)
                ->post($this->baseUrl . '/mpesa/b2c/v1/paymentrequest', [
                    'InitiatorName' => $this->b2cInitiator,
                    'SecurityCredential' => $securityCredential,
                    'CommandID' => $commandId,
                    'Amount' => $amount,
                    'PartyA' => $this->b2cShortcode,
                    'PartyB' => $phoneNumber,
                    'Remarks' => 'Payment for writing services - ID: ' . $transaction->id,
                    'QueueTimeOutURL' => $this->timeoutUrl,
                    'ResultURL' => $this->resultUrl,
                    'Occasion' => 'Writer Payment'
                ]);
            
            $result = $response->json();
            
            Log::info('M-Pesa B2C response: ' . json_encode($result));
            
            if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'message' => 'Payment initiated successfully',
                    'conversation_id' => $result['ConversationID'] ?? null,
                    'originator_conversation_id' => $result['OriginatorConversationID'] ?? null,
                    'response_code' => $result['ResponseCode'],
                    'response_description' => $result['ResponseDescription']
                ];
            }
            
            return [
                'success' => false,
                'message' => $result['ResponseDescription'] ?? 'Unknown error occurred',
                'response_code' => $result['ResponseCode'] ?? null,
                'response_description' => $result['ResponseDescription'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('M-Pesa B2C request error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending B2C payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate security credential using the certificate
     *
     * @return string
     */
    protected function generateSecurityCredential()
    {
        // For sandbox mode, use the test credentials
        if ($this->environment === 'sandbox') {
            return 'Safaricom123!';
        }
        
        // For production, encrypt the initiator password with the cert
        $cert = file_get_contents(storage_path('certs/mpesa_cert.cer'));
        $publicKey = openssl_pkey_get_public($cert);
        
        $encrypted = '';
        openssl_public_encrypt($this->b2cPassword, $encrypted, $publicKey);
        
        return base64_encode($encrypted);
    }

    /**
     * Extract phone number from payment details
     *
     * @param string $paymentDetails
     * @return string|null
     */
    protected function extractPhoneNumber($paymentDetails)
    {
        // Try to parse JSON payment details
        $details = json_decode($paymentDetails, true);
        
        if (is_array($details) && isset($details['phone'])) {
            return $details['phone'];
        }
        
        // Try to extract phone number using regex
        if (preg_match('/(\+?254|0)\d{9}/', $paymentDetails, $matches)) {
            return $matches[0];
        }
        
        return null;
    }

    /**
     * Process callback from M-Pesa
     *
     * @param array $callbackData
     * @return bool
     */
    public function processCallback($callbackData)
    {
        try {
            Log::info('M-Pesa callback received: ' . json_encode($callbackData));
            
            // Check if this is a B2C result
            if (isset($callbackData['Result'])) {
                return $this->processB2CResult($callbackData['Result']);
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process B2C result
     *
     * @param array $result
     * @return bool
     */
    protected function processB2CResult($result)
    {
        // Check for required fields
        if (!isset($result['ResultCode'], $result['ResultDesc'])) {
            Log::error('Invalid B2C result format: ' . json_encode($result));
            return false;
        }
        
        $conversationId = $result['ConversationID'] ?? null;
        $resultCode = $result['ResultCode'];
        $resultDesc = $result['ResultDesc'];
        
        // Find the transaction by conversation ID
        $transaction = Finance::where('payment_reference', $conversationId)
            ->where('status', Finance::STATUS_PROCESSING)
            ->first();
        
        if (!$transaction) {
            Log::error('Transaction not found for conversation ID: ' . $conversationId);
            return false;
        }
        
        // Update transaction based on result
        if ($resultCode == 0) {
            // Success
            $transaction->status = Finance::STATUS_COMPLETED;
            
            // Get recipient details from result params
            if (isset($result['ResultParameters'], $result['ResultParameters']['ResultParameter'])) {
                $params = collect($result['ResultParameters']['ResultParameter']);
                
                // Find the transaction ID parameter
                $transactionIdParam = $params->firstWhere('Key', 'TransactionID');
                if ($transactionIdParam && isset($transactionIdParam['Value'])) {
                    $transaction->payment_reference = $transactionIdParam['Value'];
                }
                
                // Get recipient details for record
                $recipientParam = $params->firstWhere('Key', 'ReceiverPartyPublicName');
                if ($recipientParam && isset($recipientParam['Value'])) {
                    $transaction->description .= ' - Sent to: ' . $recipientParam['Value'];
                }
            }
            
            // Update balance_after when the transaction is completed
            $currentBalance = Finance::getCurrentBalance($transaction->user_id);
            $transaction->balance_after = $currentBalance - $transaction->amount;
            
            // Process will credit the uer's writer profile with amount earned
            $writer = User::find($transaction->user_id);
            if ($writer && $writer->writerProfile) {
                $writer->writerProfile->increment('earnings', $transaction->amount);
            }
        } else {
            // Failed
            $transaction->status = Finance::STATUS_FAILED;
            $transaction->description .= ' - Failed: ' . $resultDesc;
        }
        
        $transaction->processed_at = now();
        $transaction->save();
        
        Log::info('B2C transaction updated: ' . $transaction->id . ' - Status: ' . $transaction->status);
        
        return true;
    }

    /**
     * Get the current USD to KES exchange rate
     *
     * @return float
     */
    public function getExchangeRate()
    {
        return $this->exchangeRate;
    }

    /**
     * Set the exchange rate
     *
     * @param float $rate
     * @return void
     */
    public function setExchangeRate($rate)
    {
        $this->exchangeRate = $rate;
    }

    /**
     * Update the exchange rate in configuration
     *
     * @param float $rate
     * @return bool
     */
    public function updateExchangeRate($rate)
    {
        try {
            // Validate the rate
            if (!is_numeric($rate) || $rate <= 0) {
                return false;
            }
            
            // Update environment file
            $path = base_path('.env');
            if (file_exists($path)) {
                $content = file_get_contents($path);
                
                // Replace or add the exchange rate
                if (strpos($content, 'MPESA_EXCHANGE_RATE=') !== false) {
                    $content = preg_replace('/MPESA_EXCHANGE_RATE=(.*)/', 'MPESA_EXCHANGE_RATE=' . $rate, $content);
                } else {
                    $content .= "\nMPESA_EXCHANGE_RATE=" . $rate;
                }
                
                file_put_contents($path, $content);
            }
            
            // Update the runtime config
            config(['mpesa.exchange_rate' => $rate]);
            
            // Update service property
            $this->exchangeRate = $rate;
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating exchange rate: ' . $e->getMessage());
            return false;
        }
    }
}