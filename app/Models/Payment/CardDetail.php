<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class CardDetail extends Model
{
    /**
     * | Save card details from webhook
     */
    public function saveCardDetails($webhookCardDetails)
    {
        $card = new CardDetail();
        $card->id               = $webhookCardDetails['id'];
        $card->entity           = $webhookCardDetails['entity'];
        $card->name             = $webhookCardDetails['name'];
        $card->last4            = $webhookCardDetails['last4'];
        $card->network          = $webhookCardDetails['network'];
        $card->type             = $webhookCardDetails['type'];
        $card->issuer           = $webhookCardDetails['issuer'];
        $card->international    = $webhookCardDetails['international'];
        $card->emi              = $webhookCardDetails['emi'];
        $card->sub_type         = $webhookCardDetails['sub_type'];
        $card->save();
    }
}
