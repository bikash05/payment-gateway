<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Model;

class UlbMaster extends Model
{
    /**
     * | Get Ulb Details
     */
    // public function getUlbDetails($ulbId): array
    // {
    //     $docBaseUrl = Config::get('app.url');
    //     $ulb = DB::table('ulb_masters as u')
    //         ->select('u.*', 'd.district_name', 's.name as state_name')
    //         ->join('district_masters as d', 'd.district_code', '=', 'u.district_code')
    //         ->join('m_states as s', 's.id', '=', 'u.state_id')
    //         ->where('u.id', $ulbId)
    //         ->first();
    //     if (collect($ulb)->isEmpty())
    //         throw new Exception("Ulb Not Found");
    //     return [
    //         "ulb_name" => $ulb->ulb_name,
    //         "district" => $ulb->district_name,
    //         "state" => $ulb->state_name,
    //         "address" => $ulb->address,
    //         "mobile_no" => $ulb->mobile_no,
    //         "mobile_no_2" => $ulb->toll_free_no,
    //         "website" => $ulb->current_website,
    //         "email" => $ulb->email,
    //         "state_logo" => $docBaseUrl . "/" . "custom/jhk-govt-logo.png",
    //         "ulb_logo" => $docBaseUrl . "/" . $ulb->logo,
    //         "ulb_parent_website" => $ulb->parent_website,
    //         "short_name" => $ulb->short_name,
    //         "toll_free_no" => $ulb->toll_free_no,
    //         "hindi_name" => $ulb->hindi_name,
    //         "current_website" => $ulb->current_website,
    //         "parent_website" => $ulb->parent_website,
    //     ];
    // }
}
