<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class ApiMaster extends Model
{

    /**
     * | Get The Api to Call
     */
    public function getApiEndpoint($id)
    {
        return ApiMaster::select(
            'end_point'
        )
            ->where('id', $id)
            ->first();
    }
}
