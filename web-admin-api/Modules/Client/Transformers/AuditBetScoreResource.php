<?php

namespace Modules\Client\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class AuditBetScoreResource extends  Resource
{

    public function toArray($request)
    {
        return [
            'CollectDate'       => date('Y-m-d H:i:s',strtotime($this->CollectDate)),
            'TypeText'          => $this->TypeText,
            'ChangeScore'       => realCoins($this->ChangeScore ?? 0),
            'CurAuditBetScore'  => realCoins($this->CurAuditBetScore ?? 0),
        ];
    }

}