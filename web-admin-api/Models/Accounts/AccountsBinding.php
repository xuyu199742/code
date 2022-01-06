<?php
/*用户信息表*/
namespace Models\Accounts;
/**
 * ID
 * IPAddr
 * AgentID
 * ChannelID
 * BindStatus
 * CreateTime
 *
 */
class AccountsBinding extends Base
{
    protected $table            = 'AccountsBinding';
    public    $timestamps       = false;

    //新增
    public static function saveOne()
    {
        $data = request()->all();
        $model               =  new self();
        $model->IPAddr       =   request()->ip();
        $model->AgentID      =   $data['AgentID'] ?? 0;
        $model->ChannelID    =   $data['ChannelID'] ?? 0;
        $model->BindStatus   =   $data['BindStatus'] ?? 0;
        $model->CreateTime   =   date('Y-m-d H:i:s',time());
        $model->UpdateTime   =   date('Y-m-d H:i:s',time());
        return $model->save();
    }
}
