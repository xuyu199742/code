<?php
/*签到记录*/
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\Accounts\AccountsInfo;
use Models\Record\RecordGameSignIn;

class RecordGameSignInTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['account'];

    public function transform(RecordGameSignIn $item)
    {
        return [
            'RecordID'      => $item->RecordID,
            'UserID'        => $item->UserID,
            'SignType'      => $item->SignType,
            'PackageName'   => $item->PackageName,
            'PackageGoods'  => $item->PackageGoods,
            'Probability'   => $item->Probability,
            'NeedDay'       => $item->NeedDay,
            'TotalDay'      => $item->TotalDay,
            'ClinetIP'      => $item->ClinetIP,
            'CollectDate'   => $item->CollectDate,
            'SignTypeText'  => $item->SignTypeText,
        ];
    }

    /*关联用户信息表数据*/
    public function includeAccount(RecordGameSignIn $item)
    {
        if(isset($item->account)){
            return $this->primitive($item->account,new AccountsInfoTransformer());
        }else{
            return $this->primitive(new AccountsInfo(),new AccountsInfoTransformer);
        }
    }

}