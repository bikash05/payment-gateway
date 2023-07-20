<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    /**
     * | Save the razorpay request data 
        | DepartmentId is the module Id
        | Working
     */
    public function saveRazorpayRequest($userId,$ulbId,$orderId,$request)
    {
        $mPaymentRequest = new PaymentRequest();
        $mPaymentRequest->user_id           = $userId;
        $mPaymentRequest->workflow_id       = $request->workflowId;
        $mPaymentRequest->ulb_id            = $ulbId;
        $mPaymentRequest->application_id    = $request->id;
        $mPaymentRequest->department_id     = $request->departmentId;                       // here(CHECK)
        $mPaymentRequest->razorpay_order_id = $orderId;
        $mPaymentRequest->amount            = $request->amount;
        $mPaymentRequest->currency          = 'INR';                                        // Static 
        $mPaymentRequest->save();
    }
}
