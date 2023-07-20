<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class WebhookPaymentData extends Model
{
    /**
     * | Get details according to given data to check the record in webhook table
     */
    public function getWebhookRecord($request, $captured, $webhookEntity, $status)
    {
        return WebhookPaymentData::where("account_id", $request->account_id)
            ->where("payment_order_id", $webhookEntity['order_id'])
            ->where("payment_id", $webhookEntity['id'])
            ->where("payment_status", $status)
            ->where("payment_captured", $captured);
    }

    /**
     * | Save Webhook Details 
     */
    public function saveWebhookData($request, $captured, $actulaAmount, $status, $notes, $firstKey, $contains, $actualTransactionNo, $webhookEntity)
    {
        $webhookData = new WebhookPaymentData();
        $webhookData->entity                       = $request->entity;
        $webhookData->account_id                   = $request->account_id;
        $webhookData->event                        = $request->event;
        $webhookData->webhook_created_at           = $request->created_at;
        $webhookData->payment_captured             = $captured;
        $webhookData->payment_amount               = $actulaAmount;
        $webhookData->payment_status               = $status;                                                      //<---------------- here (STATUS)
        $webhookData->payment_notes                = $notes;                                                       //<-----here (NOTES)
        $webhookData->payment_acquirer_data_type   = $firstKey;                                                    //<------------here (FIRSTKEY)
        $webhookData->contains                     = $contains;                                                    //<---------- this(CONTAINS)
        $webhookData->payment_id                   = $webhookEntity['id'];
        $webhookData->payment_entity               = $webhookEntity['entity'];
        $webhookData->payment_currency             = $webhookEntity['currency'];
        $webhookData->payment_order_id             = $webhookEntity['order_id'];
        $webhookData->payment_invoice_id           = $webhookEntity['invoice_id'];
        $webhookData->payment_international        = $webhookEntity['international'];
        $webhookData->payment_method               = $webhookEntity['method'];
        $webhookData->payment_amount_refunded      = $webhookEntity['amount_refunded'];
        $webhookData->payment_refund_status        = $webhookEntity['refund_status'];
        $webhookData->payment_description          = $webhookEntity['description'];
        $webhookData->payment_card_id              = $webhookEntity['card_id'];
        $webhookData->payment_bank                 = $webhookEntity['bank'];
        $webhookData->payment_wallet               = $webhookEntity['wallet'];
        $webhookData->payment_vpa                  = $webhookEntity['vpa'];
        $webhookData->payment_email                = $webhookEntity['email'];
        $webhookData->payment_contact              = $webhookEntity['contact'];
        $webhookData->payment_fee                  = $webhookEntity['fee'];
        $webhookData->payment_tax                  = $webhookEntity['tax'];
        $webhookData->payment_error_code           = $webhookEntity['error_code'];
        $webhookData->payment_error_description    = $webhookEntity['error_description'];
        $webhookData->payment_error_source         = $webhookEntity['error_source'] ?? null;
        $webhookData->payment_error_step           = $webhookEntity['error_step'] ?? null;
        $webhookData->payment_error_reason         = $webhookEntity['error_reason'] ?? null;
        $webhookData->payment_acquirer_data_value  = $webhookEntity['acquirer_data'][$firstKey];
        $webhookData->payment_created_at           = $webhookEntity['created_at'];

        # user details
        $webhookData->user_id                      = $webhookEntity['notes']['userId'];
        $webhookData->department_id                = $webhookEntity['notes']['departmentId'];   // moduleId
        $webhookData->workflow_id                  = $webhookEntity['notes']['workflowId'];
        $webhookData->ulb_id                       = $webhookEntity['notes']['ulbId'];

        # transaction id generation and saving
        $webhookData->payment_transaction_id = $actualTransactionNo;
        $webhookData->save();

        return $webhookData;
    }
}
