<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MpesaDarajaService
{
    /**
     * M-Pesa API endpoint base URL
     * 
     * @var string
     */
    protected $baseUrl;
    
    /**
     * M-Pesa consumer key
     * 
     * @var string
     */
    protected $consumerKey;
    
    /**
     * M-Pesa consumer secret
     * 
     * @var string
     */
    protected $consumerSecret;
    
    /**
     * M-Pesa business shortcode
     * 
     * @var string
     */
    protected $shortcode;
    
    /**
     * M-Pesa passkey for STK Push
     * 
     * @var string
     */
    protected $passkey;
    
    /**
     * M-Pesa initiator name for B2C
     * 
     * @var string
     */
    protected $initiatorName;
    
    /**
     * M-Pesa security credential
     * 
     * @var string
     */
    protected $securityCredential;
    
    /**
     * M-Pesa callback URL
     * 
     * @var string
     */
    protected $callbackUrl;
    
    /**
     * M-Pesa timeout URL
     * 
     * @var string
     */
    protected $timeoutUrl;
    
    /**
     * M-Pesa B2C result URL
     * 
     * @var string
     */
    protected $b2cResultUrl;
    
    /**
     * M-Pesa B2C queue timeout URL
     * 
     * @var string
     */
    protected $b2cQueueTimeoutUrl;
    
    /**
     * Create a new M-Pesa Daraja service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->baseUrl = config('services.mpesa.base_url');
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
        $this->initiatorName = config('services.mpesa.initiator_name');
        $this->securityCredential = config('services.mpesa.security_credential');
        $this->callbackUrl = config('services.mpesa.callback_url');
        $this->timeoutUrl = config('services.mpesa.timeout_url');
        $this->b2cResultUrl = config('services.mpesa.b2c_result_url');
        $this->b2cQueueTimeoutUrl = config('services.mpesa.b2c_queue_timeout_url');
    }
    
    /**
     * Get OAuth access token from M-Pesa API
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        // Check if token is cached and still valid
        if (Cache::has('mpesa_access_token')) {
            return Cache::get('mpesa_access_token');
        }
        
        try {
            $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Cache the token for slightly less than the expiry time (3600 seconds = 1 hour)
                $expiresIn = $data['expires_in'] ?? 3600;
                Cache::put('mpesa_access_token', $data['access_token'], Carbon::now()->addSeconds($expiresIn -
                60));
                
                return $data['access_token'];
            } else {
                Log::error('M-Pesa API access token error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return null;
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa API access token exception: ' . $e->getMessage());
            
            return null;
        }
    }
    
    /**
     * Initiate STK Push transaction
     *
     * @param string $phoneNumber Customer phone number (format: 254XXXXXXXXX)
     * @param float $amount Amount to charge
     * @param string $reference Transaction reference
     * @param string $description Transaction description
     * @return array
     */
    public function initiateSTKPush($phoneNumber, $amount, $reference, $description)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token'
                ];
            }
            
            $url = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
            
            $timestamp = Carbon::now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
            
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'BusinessShortCode' => $this->shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => round($amount),
                    'PartyA' => $phoneNumber,
                    'PartyB' => $this->shortcode,
                    'PhoneNumber' => $phoneNumber,
                    'CallBackURL' => $this->callbackUrl,
                    'AccountReference' => substr($reference, 0, 12),
                    'TransactionDesc' => substr($description, 0, 13)
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
                    return [
                        'success' => true,
                        'message' => 'STK push initiated successfully',
                        'checkoutRequestId' => $data['CheckoutRequestID'],
                        'data' => $data
                    ];
                } else {
                    Log::error('M-Pesa STK push error', [
                        'response' => $data
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['errorMessage'] ?? 'Failed to initiate STK push',
                        'data' => $data
                    ];
                }
            } else {
                Log::error('M-Pesa STK push API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to initiate STK push: API error',
                    'data' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa STK push exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'STK push failed due to an error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check STK Push transaction status
     *
     * @param string $checkoutRequestId
     * @return array
     */
    public function checkSTKStatus($checkoutRequestId)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token'
                ];
            }
            
            $url = $this->baseUrl . '/mpesa/stkpushquery/v1/query';
            
            $timestamp = Carbon::now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
            
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'BusinessShortCode' => $this->shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'CheckoutRequestID' => $checkoutRequestId
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
                    // Check the ResultCode for the actual status
                    if (isset($data['ResultCode']) && $data['ResultCode'] == '0') {
                        return [
                            'success' => true,
                            'message' => 'Transaction was successful',
                            'data' => $data
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => $data['ResultDesc'] ?? 'Transaction failed or is pending',
                            'data' => $data
                        ];
                    }
                } else {
                    Log::error('M-Pesa STK query error', [
                        'response' => $data
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['errorMessage'] ?? 'Failed to query STK status',
                        'data' => $data
                    ];
                }
            } else {
                Log::error('M-Pesa STK query API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to query STK status: API error',
                    'data' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa STK query exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'STK query failed due to an error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send money to a customer (B2C)
     *
     * @param string $phoneNumber Recipient phone number (format: 254XXXXXXXXX)
     * @param float $amount Amount to send
     * @param string $remarks Description/remarks
     * @return array
     */
    public function sendMoney($phoneNumber, $amount, $remarks)
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => 'Failed to get access token'
                ];
            }
            
            $url = $this->baseUrl . '/mpesa/b2c/v1/paymentrequest';
            
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'InitiatorName' => $this->initiatorName,
                    'SecurityCredential' => $this->securityCredential,
                    'CommandID' => 'BusinessPayment',
                    'Amount' => round($amount),
                    'PartyA' => $this->shortcode,
                    'PartyB' => $phoneNumber,
                    'Remarks' => substr($remarks, 0, 100),
                    'QueueTimeOutURL' => $this->b2cQueueTimeoutUrl,
                    'ResultURL' => $this->b2cResultUrl,
                    'Occasion' => 'WriterPayment'
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['ResponseCode']) && $data['ResponseCode'] == '0') {
                    return [
                        'success' => true,
                        'message' => 'Money sent successfully',
                        'transactionId' => $data['ConversationID'] ?? '',
                        'data' => $data
                    ];
                } else {
                    Log::error('M-Pesa B2C error', [
                        'response' => $data
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['errorMessage'] ?? 'Failed to send money',
                        'data' => $data
                    ];
                }
            } else {
                Log::error('M-Pesa B2C API error', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to send money: API error',
                    'data' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa B2C exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'B2C payment failed due to an error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format the phone number to the required format (254XXXXXXXXX)
     *
     * @param string $phoneNumber
     * @return string
     */
    public function formatPhoneNumber($phoneNumber)
    {
        // Remove any whitespace, hyphens, etc.
        $phone = preg_replace('/\s+/', '', $phoneNumber);
        
        // If the number starts with a plus, remove it
        if (substr($phone, 0, 1) == '+') {
            $phone = substr($phone, 1);
        }
        
        // If the number starts with a 0, replace it with 254
        if (substr($phone, 0, 1) == '0') {
            $phone = '254' . substr($phone, 1);
        }
        
        // If the number doesn't start with 254, add it
        if (substr($phone, 0, 3) != '254') {
            $phone = '254' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Process the callback data from M-Pesa STK Push
     *
     * @param array $callbackData
     * @return array
     */
    public function processSTKCallback($callbackData)
    {
        try {
            if (isset($callbackData['Body']['stkCallback'])) {
                $stkCallback = $callbackData['Body']['stkCallback'];
                $resultCode = $stkCallback['ResultCode'];
                $resultDesc = $stkCallback['ResultDesc'];
                $checkoutRequestId = $stkCallback['CheckoutRequestID'];
                
                if ($resultCode == 0) {
                    // Transaction successful
                    $callbackMetadata = $stkCallback['CallbackMetadata']['Item'];
                    
                    $amount = null;
                    $transactionId = null;
                    $transactionDate = null;
                    $phoneNumber = null;
                    
                    foreach ($callbackMetadata as $item) {
                        switch ($item['Name']) {
                            case 'Amount':
                                $amount = $item['Value'];
                                break;
                            case 'MpesaReceiptNumber':
                                $transactionId = $item['Value'];
                                break;
                            case 'TransactionDate':
                                $transactionDate = $item['Value'];
                                break;
                            case 'PhoneNumber':
                                $phoneNumber = $item['Value'];
                                break;
                        }
                    }
                    
                    return [
                        'success' => true,
                        'message' => $resultDesc,
                        'checkoutRequestId' => $checkoutRequestId,
                        'transactionId' => $transactionId,
                        'amount' => $amount,
                        'phoneNumber' => $phoneNumber,
                        'transactionDate' => $transactionDate
                    ];
                } else {
                    // Transaction failed
                    return [
                        'success' => false,
                        'message' => $resultDesc,
                        'checkoutRequestId' => $checkoutRequestId,
                        'resultCode' => $resultCode
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid callback data'
                ];
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing exception: ' . $e->getMessage(), [
                'callbackData' => $callbackData
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to process callback: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process the callback data from M-Pesa B2C
     *
     * @param array $callbackData
     * @return array
     */
    public function processB2CCallback($callbackData)
    {
        try {
            if (isset($callbackData['Result'])) {
                $result = $callbackData['Result'];
                $resultCode = $result['ResultCode'];
                $resultDesc = $result['ResultDesc'];
                $conversationId = $result['ConversationID'];
                
                if ($resultCode == 0) {
                    // Transaction successful
                    $transactionId = $result['TransactionID'];
                    $amount = $result['TransactionAmount'];
                    $recipientPhone = $result['ReceiverPartyPublicName'] ?? '';
                    
                    // Extract phone number from the recipient name
                    preg_match('/\d+/', $recipientPhone, $matches);
                    $phoneNumber = $matches[0] ?? '';
                    
                    return [
                        'success' => true,
                        'message' => $resultDesc,
                        'conversationId' => $conversationId,
                        'transactionId' => $transactionId,
                        'amount' => $amount,
                        'phoneNumber' => $phoneNumber
                    ];
                } else {
                    // Transaction failed
                    return [
                        'success' => false,
                        'message' => $resultDesc,
                        'conversationId' => $conversationId,
                        'resultCode' => $resultCode
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid callback data'
                ];
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa B2C callback processing exception: ' . $e->getMessage(), [
                'callbackData' => $callbackData
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to process callback: ' . $e->getMessage()
            ];
        }
    }
}