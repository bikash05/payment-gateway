<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class IdGenerationParam extends Model
{
    /**
     * | Find IdGeneration by id
     */
    public function getParams($id)
    {
        return IdGenerationParam::find($id);
    }
}
