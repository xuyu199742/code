<?php
/*金币流水记录表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Record\RecordTreasureSerial;
class RecordTreasureSerialTransformer extends  TransformerAbstract
{
    protected $availableIncludes = ['account'];
    public function transform(RecordTreasureSerial $item)
    {
        return [
            'SerialNumber'      => $item->SerialNumber,
            'MasterID'          => $item->MasterID,
            'UserID'            => $item->UserID,
            'TypeID'            => $item->TypeID,
            'CurScore'          => realCoins($item->CurScore ?? 0),
            'CurInsureScore'    => realCoins($item->CurInsureScore ?? 0),
            'ChangeScore'       => realCoins($item->ChangeScore ?? 0),
            'ChangeAfterScore'  => $item->TypeID == RecordTreasureSerial::WITHDRAWAL_REFUSE ? realCoins($item->CurScore ?? 0) : realCoins($item->CurScore+$item->ChangeScore ?? 0),
            'ClientIP'          => $item->ClientIP,
            'CollectDate'       => date('Y-m-d H:i:s',strtotime($item->CollectDate)),
            'TypeText'          => $item->TypeText,
            'username'          => isset($item->admin->username) ? $item->admin->username : '',
            'Reason'            => $item->Reason ?: '',
        ];
    }

    /*关联用户信息表数据*/
    public function includeAccount(RecordTreasureSerial $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer());
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }
    }
}
