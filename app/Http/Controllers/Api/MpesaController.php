<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Finance;
use App\Models\MpesaTransaction;
use App\Models\Order;
use App\Models\User;
use App\Models\WriterPendingPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    /**
     * Handle M-Pesa callback for STK Push
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleCallback(Request $request)
    {
        // Log the request for debugging
        Log::info('M-Pesa callback received', ['data' => $request->all()]);

        try {
            $callbackData = $request->all();
            
            // Check for Body.stkCallback structure (STK Push callback)
            if (isset($callbackData['Body']) && isset($callbackData['Body']['stkCallback'])) {
                return $this->processSTKCallback($callbackData['Body']['stkCallback']);
            }
            
            // If it's a direct callback with CheckoutRequestID
            if (isset($callbackData['CheckoutRequestID'])) {
                // Find the transaction
                $transaction = MpesaTransaction::where('checkout_request_id', $callbackData['CheckoutRequestID'])->first();
                
                if (!$transaction) {
                    Log::warning('M-Pesa transaction not found', ['checkout_request_id' => $callbackData['CheckoutRequestID']]);
                    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Transaction not found but accepted']);
                }
                
                // Update transaction details
                $transaction->update([
                    'status' => ($callbackData['ResultCode'] == 0) ? 'completed' : 'failed',
                    'result_code' => $callbackData['ResultCode'] ?? null,
                    'result_desc' => $callbackData['ResultDesc'] ?? null,
                    'mpesa_receipt_number' => $callbackData['MpesaReceiptNumber'] ?? null,
                    'transaction_date' => $callbackData['TransactionDate'] ?? null,
                    'phone_number' => $callbackData['PhoneNumber'] ?? null,
                    'amount' => $callbackData['Amount'] ?? $transaction->amount,
                ]);
                
                // If payment was successful, update the order
                if (($callbackData['ResultCode'] ?? null) == 0) {
                    $this->updateOrderPaymentStatus($transaction);
                }
            }
            
            // Always return success to M-Pesa
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            // Always return success to M-Pesa even if we had an error
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
        }
    }

    /**
     * Process STK Push callback
     *
     * @param array $stkCallback
     * @return \Illuminate\Http\JsonResponse
     */
    private function processSTKCallback($stkCallback)
    {
        // Extract the necessary data
        $merchantRequestID = $stkCallback['MerchantRequestID'] ?? null;
        $checkoutRequestID = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode = $stkCallback['ResultCode'] ?? null;
        $resultDesc = $stkCallback['ResultDesc'] ?? null;
        
        // Find the transaction
        $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestID)
            ->orWhere('merchant_request_id', $merchantRequestID)
            ->first();
        
        if (!$transaction) {
            Log::warning('M-Pesa STK transaction not found', [
                'checkout_request_id' => $checkoutRequestID,
                'merchant_request_id' => $merchantRequestID
            ]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Transaction not found but accepted']);
        }
        
        // If successful, get the transaction details
        if ($resultCode == 0) {
            $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? [];
            $metadataItems = [];
            
            // Extract metadata items
            foreach ($callbackMetadata as $item) {
                $metadataItems[$item['Name']] = $item['Value'] ?? null;
            }
            
            // Update transaction
            $transaction->update([
                'status' => 'completed',
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
                'mpesa_receipt_number' => $metadataItems['MpesaReceiptNumber'] ?? null,
                'transaction_date' => $this->formatMpesaDate($metadataItems['TransactionDate'] ?? null),
                'phone_number' => $metadataItems['PhoneNumber'] ?? null,
                'amount' => $metadataItems['Amount'] ?? $transaction->amount,
            ]);
            
            // Update order payment status
            $this->updateOrderPaymentStatus($transaction);
        } else {
            // Update transaction as failed
            $transaction->update([
                'status' => 'failed',
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
            ]);
        }
        
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }

    /**
     * Handle M-Pesa timeout for STK Push
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleTimeout(Request $request)
    {
        // Log the request for debugging
        Log::info('M-Pesa timeout received', ['data' => $request->all()]);

        try {
            $requestData = $request->all();
            
            // Get checkout request ID
            $checkoutRequestID = $requestData['CheckoutRequestID'] ?? null;
            $merchantRequestID = $requestData['MerchantRequestID'] ?? null;
            
            if ($checkoutRequestID || $merchantRequestID) {
                // Find the transaction
                $transaction = MpesaTransaction::where(function ($query) use ($checkoutRequestID, $merchantRequestID) {
                    if ($checkoutRequestID) {
                        $query->where('checkout_request_id', $checkoutRequestID);
                    }
                    if ($merchantRequestID) {
                        $query->orWhere('merchant_request_id', $merchantRequestID);
                    }
                })->first();
                
                if ($transaction) {
                    // Update transaction as timeout
                    $transaction->update([
                        'status' => 'timeout',
                        'result_desc' => 'Transaction timed out',
                    ]);
                }
            }
            
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Timeout received successfully']);
        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa timeout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Timeout received successfully']);
        }
    }

    /**
     * Handle M-Pesa B2C result callback
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleResult(Request $request)
    {
        // Log the request for debugging
        Log::info('M-Pesa result received', ['data' => $request->all()]);

        try {
            $resultData = $request->all();
            
            // Process result based on structure
            if (isset($resultData['Result'])) {
                // B2C result structure
                $result = $resultData['Result'];
                $originatorConversationID = $result['OriginatorConversationID'] ?? null;
                $resultCode = $result['ResultCode'] ?? null;
                $resultDesc = $result['ResultDesc'] ?? null;
                $transactionID = $result['TransactionID'] ?? null;
                
                // Find the transaction by conversation ID
                $transaction = MpesaTransaction::where('originator_conversation_id', $originatorConversationID)
                    ->orWhere('conversation_id', $result['ConversationID'] ?? null)
                    ->first();
                
                if ($transaction) {
                    // Update transaction details
                    $transaction->update([
                        'status' => ($resultCode == 0) ? 'completed' : 'failed',
                        'result_code' => $resultCode,
                        'result_desc' => $resultDesc,
                        'transaction_id' => $transactionID,
                    ]);
                    
                    // If it's a successful B2C payment (writer withdrawal)
                    if ($resultCode == 0 && $transaction->transaction_type == 'payout') {
                        $this->processWriterWithdrawal($transaction, $result);
                    }
                } else {
                    Log::warning('B2C transaction not found', ['originatorConversationID' => $originatorConversationID]);
                }
            }
            
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Result received successfully']);
        } catch (\Exception $e) {
            Log::error('Error processing M-Pesa result', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Result received successfully']);
        }
    }

    /**
     * Update order payment status
     *
     * @param MpesaTransaction $transaction
     * @return void
     */
    private function updateOrderPaymentStatus($transaction)
    {
        try {
            DB::beginTransaction();
            
            // Get the order
            $orderId = $transaction->order_id;
            if (!$orderId) {
                // Try to extract order ID from reference
                if (preg_match('/Order-(\d+)/', $transaction->account_reference, $matches)) {
                    $orderId = $matches[1];
                }
            }
            
            if ($orderId) {
                $order = Order::find($orderId);
                
                if ($order) {
                    // Update order payment status
                    $order->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'payment_method' => 'mpesa',
                        'payment_reference' => $transaction->mpesa_receipt_number,
                    ]);
                    
                    // Create payment record
                    Finance::create([
                        'order_id' => $order->id,
                        'user_id' => $order->client_id,
                        'amount' => $transaction->amount,
                        'payment_method' => 'mpesa',
                        'transaction_reference' => $transaction->mpesa_receipt_number,
                        'status' => 'completed',
                        'payment_date' => now(),
                        'description' => 'M-Pesa payment for Order #' . $order->id,
                    ]);
                    
                    // Update financial records
                    if ($order->client_id) {
                        Finance::create([
                            'user_id' => $order->client_id,
                            'amount' => $transaction->amount,
                            'transaction_type' => 'payment',
                            'payment_method' => 'mpesa',
                            'reference' => $transaction->mpesa_receipt_number,
                            'description' => 'Payment for Order #' . $order->id,
                            'status' => 'completed',
                        ]);
                    }
                    
                    // If the order has a writer, create pending payment
                    if ($order->writer_id) {
                        // Calculate writer commission
                        $writer = User::find($order->writer_id);
                        $commissionRate = $writer->commission_rate ?? config('app.default_writer_commission_rate', 0.70);
                        $writerAmount = round($transaction->amount * $commissionRate, 2);
                        
                        // Add to writer's pending payments
                        WriterPendingPayment::create([
                            'writer_id' => $order->writer_id,
                            'order_id' => $order->id,
                            'amount' => $writerAmount,
                            'status' => 'pending',
                            'release_date' => now()->addDays(config('app.payment_hold_days', 7)),
                        ]);
                    }
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order payment status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction' => $transaction
            ]);
        }
    }

    /**
     * Process writer withdrawal
     *
     * @param MpesaTransaction $transaction
     * @param array $resultData
     * @return void
     */
    private function processWriterWithdrawal($transaction, $resultData)
    {
        try {
            DB::beginTransaction();
            
            // Find the withdrawal record
            $withdrawal = Finance::where('payment_reference', $transaction->originator_conversation_id)
                ->where('transaction_type', Finance::TYPE_WITHDRAWAL)
                ->where('status', Finance::STATUS_PROCESSING)
                ->first();
            
            if ($withdrawal) {
                // Update withdrawal status
                $withdrawal->update([
                    'status' => Finance::STATUS_COMPLETED,
                    'processed_at' => now(),
                    'reference' => $resultData['TransactionID'] ?? $transaction->transaction_id,
                    'description' => $withdrawal->description . ' | Completed via M-Pesa',
                ]);
                
                // Notify the user
                $user = User::find($withdrawal->user_id);
                if ($user) {
                    $user->notify(new \App\Notifications\WithdrawalProcessed($withdrawal));
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing writer withdrawal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction' => $transaction
            ]);
        }
    }

    /**
     * Format M-Pesa date
     *
     * @param string|int|null $date
     * @return string|null
     */
    private function formatMpesaDate($date)
    {
        if (!$date) {
            return now()->format('Y-m-d H:i:s');
        }
        
        // Convert string format YYYYMMDDHHMMSS to Carbon
        if (is_string($date) && strlen($date) === 14) {
            return Carbon::createFromFormat('YmdHis', $date)->format('Y-m-d H:i:s');
        }
        
        // If it's a timestamp
        if (is_numeric($date)) {
            // Convert to string if it's a numeric timestamp
            $dateStr = (string) $date;
            
            if (strlen($dateStr) === 14) {
                return Carbon::createFromFormat('YmdHis', $dateStr)->format('Y-m-d H:i:s');
            }
            
            // If it's a unix timestamp
            return Carbon::createFromTimestamp($date)->format('Y-m-d H:i:s');
        }
        
        // Return as is if it's already formatted
        return $date;
    }
    
    /**
     * Legacy callback handler for backward compatibility
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        return $this->handleCallback($request);
    }

    /**
     * Legacy timeout handler for backward compatibility
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeout(Request $request)
    {
        return $this->handleTimeout($request);
    }

    /**
     * Legacy result handler for backward compatibility
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function result(Request $request)
    {
        return $this->handleResult($request);
    }

    /**
     * Handle STK Push callback
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stkCallback(Request $request)
    {
        return $this->handleCallback($request);
    }

    /**
     * Handle B2C result callback
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function b2cResultCallback(Request $request)
    {
        return $this->handleResult($request);
    }

    /**
     * Handle B2C timeout callback
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function b2cTimeoutCallback(Request $request)
    {
        return $this->handleTimeout($request);
    }
}