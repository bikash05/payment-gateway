<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Model;

class WfWorkflow extends Model
{
    /**
     * | List workflow by id 
     */
    public function listbyId($refReq)
    {
        return WfWorkflow::where('id', $refReq->id)
            ->where('is_suspended', false)
            ->first();
    }
}
