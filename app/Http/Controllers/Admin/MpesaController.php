<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MpesaDarajaService;
use App\Models\MpesaTransaction;
use App\Models\Order;
use App\Models\Finance;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class MpesaController extends Controller
{
    protected $mpesaService;
    
    /**
     * Create a new controller instance.
     *
     * @param MpesaDarajaService $mpesaService
     * @return void
     */
    public function __construct(MpesaDarajaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }
    
    /**
     * Initiate payment via STK Push
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initiatePayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'order_id' => 'required|integer|exists:orders,id',
            ]);
            
            // Format the phone number
            $phoneNumber = $this->mpesaService->formatPhoneNumber($validated['phone']);
            
            // Get the order
            $order = Order::findOrFail($validated['order_id']);
            
            // Check if the order has already been paid
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has already been paid.'
                ], 400);
            }
            
            // Generate a reference (using order number)
            $reference = 'Order-' . $order->id;
            
            // Generate a description
            $description = 'Payment for Order #' . $order->id;
            
            // Initiate STK Push
            $result = $this->mpesaService->initiateSTKPush(
                $phoneNumber,
                $validated['amount'],
                $reference,
                $description
            );
            
            if ($result['success']) {
                // Store the transaction details
                MpesaTransaction::create([
                    'checkout_request_id' => $result['checkoutRequestId'],
                    'phone' => $phoneNumber,
                    'amount' => $validated['amount'],
                    'reference' => $reference,
                    'description' => $description,
                    'order_id' => $validated['order_id'],
                    'status' => 'pending',
                    'result_code' => null,
                    'result_desc' => null,
                    'transaction_id' => null,
                    'transaction_date' => null,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated. Please check your phone to complete the transaction.',
                    'checkoutRequestId' => $result['checkoutRequestId']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('STK Push initiation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * Check payment status
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'checkout_request_id' => 'required|string|exists:mpesa_transactions,checkout_request_id',
            ]);
            
            // First check the local database
            $transaction = MpesaTransaction::where('checkout_request_id', $validated['checkout_request_id'])
                ->first();
            
            if ($transaction) {
                // If transaction is already completed or failed, return the status
                if ($transaction->status !== 'pending') {
                    return response()->json([
                        'success' => $transaction->status === 'completed',
                        'message' => $transaction->result_desc,
                        'status' => $transaction->status,
                        'transactionId' => $transaction->transaction_id
                    ]);
                }
                
                // If still pending, check with M-Pesa
                $result = $this->mpesaService->checkSTKStatus($validated['checkout_request_id']);
                
                if ($result['success']) {
                    // Update the transaction status
                    $transaction->status = 'completed';
                    $transaction->result_code = 0;
                    $transaction->result_desc = 'Success';
                    $transaction->save();
                    
                    // Update the order payment status
                    $this->updateOrderPaymentStatus($transaction);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment completed successfully.',
                        'status' => 'completed',
                        'transactionId' => $transaction->transaction_id
                    ]);
                } else {
                    // Check if the transaction has explicitly failed
                    if (isset($result['data']['ResultCode']) && $result['data']['ResultCode'] != 0) {
                        $transaction->status = 'failed';
                        $transaction->result_code = $result['data']['ResultCode'];
                        $transaction->result_desc = $result['data']['ResultDesc'];
                        $transaction->save();
                        
                        return response()->json([
                            'success' => false,
                            'message' => $result['message'],
                            'status' => 'failed'
                        ]);
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment is still pending.',
                        'status' => 'pending'
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Check payment status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check payment status. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * M-Pesa STK Push callback
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function stkCallback(Request $request)
    {
        try {
            $callbackData = $request->all();
            Log::info('M-Pesa STK callback received', $callbackData);
            
            $result = $this->mpesaService->processSTKCallback($callbackData);
            
            if ($result['success']) {
                // Find the transaction
                $transaction = MpesaTransaction::where('checkout_request_id', $result['checkoutRequestId'])
                    ->first();
                
                if ($transaction) {
                    // Update the transaction
                    $transaction->status = 'completed';
                    $transaction->result_code = 0;
                    $transaction->result_desc = $result['message'];
                    $transaction->transaction_id = $result['transactionId'];
                    $transaction->transaction_date = $this->formatMpesaDate($result['transactionDate']);
                    $transaction->save();
                    
                    // Update the order payment status
                    $this->updateOrderPaymentStatus($transaction);
                }
            } else {
                // Find the transaction
                $transaction = MpesaTransaction::where('checkout_request_id', $result['checkoutRequestId'])
                    ->first();
                
                if ($transaction) {
                    // Update the transaction status
                    $transaction->status = 'failed';
                    $transaction->result_code = $result['resultCode'];
                    $transaction->result_desc = $result['message'];
                    $transaction->save();
                }
            }
            
            // M-Pesa expects a specific response format
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Confirmation received successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('M-Pesa STK callback error: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            
            // Always respond with success to M-Pesa
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Confirmation received successfully'
            ]);
        }
    }
    
    /**
     * M-Pesa B2C result callback
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function b2cResultCallback(Request $request)
    {
        try {
            $callbackData = $request->all();
            Log::info('M-Pesa B2C result callback received', $callbackData);
            
            $result = $this->mpesaService->processB2CCallback($callbackData);
            
            if ($result['success']) {
                // Find the withdrawal transaction by conversation ID
                $transaction = Finance::where('payment_reference', $result['conversationId'])
                    ->where('transaction_type', Finance::TYPE_WITHDRAWAL)
                    ->where('status', Finance::STATUS_PROCESSING)
                    ->first();
                
                if ($transaction) {
                    // Update the transaction
                    $transaction->status = Finance::STATUS_COMPLETED;
                    $transaction->payment_reference = $result['transactionId'];
                    $transaction->processed_at = now();
                    $transaction->save();
                    
                    // Get current balance
                    $currentBalance = Finance::getCurrentBalance($transaction->user_id);
                    
                    // Update the balance
                    $transaction->balance_after = $currentBalance - $transaction->amount;
                    $transaction->save();
                    
                    // Notify the user
                    $user = User::find($transaction->user_id);
                    if ($user) {
                        $user->notify(new \App\Notifications\WithdrawalProcessed($transaction));
                    }
                }
            } else {
                // Find the withdrawal transaction by conversation ID
                $transaction = Finance::where('payment_reference', $result['conversationId'])
                    ->where('transaction_type', Finance::TYPE_WITHDRAWAL)
                    ->where('status', Finance::STATUS_PROCESSING)
                    ->first();
                
                if ($transaction) {
                    // Update the transaction status
                    $transaction->status = Finance::STATUS_FAILED;
                    $transaction->description = $transaction->description . ' | Failed: ' . $result['message'];
                    $transaction->save();
                    
                    // Notify the user
                    $user = User::find($transaction->user_id);
                    if ($user) {
                        $user->notify(new \App\Notifications\WithdrawalFailed($transaction));
                    }
                }
            }
            
            // M-Pesa expects a specific response format
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Result received successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('M-Pesa B2C result callback error: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            
            // Always respond with success to M-Pesa
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Result received successfully'
            ]);
        }
    }
    
    /**
     * M-Pesa B2C timeout callback
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function b2cTimeoutCallback(Request $request)
    {
        try {
            $callbackData = $request->all();
            Log::info('M-Pesa B2C timeout callback received', $callbackData);
            
            // M-Pesa expects a specific response format
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Timeout received successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('M-Pesa B2C timeout callback error: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);
            
            // Always respond with success to M-Pesa
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Timeout received successfully'
            ]);
        }
    }
    
    /**
     * Update order payment status
     *
     * @param  \App\Models\MpesaTransaction  $transaction
     * @return void
     */
    private function updateOrderPaymentStatus($transaction)
    {
        try {
            DB::beginTransaction();
            
            $order = Order::find($transaction->order_id);
            
            if ($order) {
                // Update order payment status
                $order->payment_status = 'paid';
                $order->paid_at = now();
                $order->payment_method = 'mpesa';
                $order->payment_reference = $transaction->transaction_id;
                $order->save();
                
                // Create a customer payment record
                \App\Models\Payment::create([
                    'order_id' => $order->id,
                    'amount' => $transaction->amount,
                    'payment_method' => 'mpesa',
                    'transaction_id' => $transaction->transaction_id,
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
                
                // If the order is assigned to a writer, create a pending payment record
                if ($order->writer_id) {
                    // Calculate writer's payment amount (based on your business rules)
                    $writerPaymentAmount = $this->calculateWriterPayment($order, $transaction->amount);
                    
                    // Add the payment to the writer's balance as pending
                    // This will be released when the order is completed
                    \App\Models\WriterPendingPayment::create([
                        'writer_id' => $order->writer_id,
                        'order_id' => $order->id,
                        'amount' => $writerPaymentAmount,
                        'status' => 'pending',
                    ]);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Update order payment status error: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'order_id' => $transaction->order_id
            ]);
        }
    }
    
    /**
     * Calculate writer payment amount
     *
     * @param  \App\Models\Order  $order
     * @param  float  $totalAmount
     * @return float
     */
    private function calculateWriterPayment($order, $totalAmount)
    {
        // Get the writer's commission rate
        $writer = User::find($order->writer_id);
        $commissionRate = $writer->commission_rate ?? config('app.default_writer_commission_rate', 0.70);
        
        // Calculate the payment amount
        return round($totalAmount * $commissionRate, 2);
    }
    
    /**
     * Format M-Pesa transaction date
     *
     * @param  string|int  $dateString
     * @return \Carbon\Carbon
     */
    private function formatMpesaDate($dateString)
    {
        // Convert string format YYYYMMDDHHMMSS to Carbon
        if (is_string($dateString) && strlen($dateString) === 14) {
            return Carbon::createFromFormat('YmdHis', $dateString);
        }
        
        // If it's a timestamp
        if (is_numeric($dateString) && strlen((string)$dateString) === 14) {
            return Carbon::createFromFormat('YmdHis', (string)$dateString);
        }
        
        // Default to current time if format is not recognized
        return now();
    }
}