<?php
/*用户信息设置表*/
namespace Transformers;
use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\AccountLog;
class AccountsLogTransformer extends TransformerAbstract
{
    //用户表基本信息
    public function transform(AccountLog $item)
    {
        return [
            'id'                =>  $item->id,
            'user_id'           =>  $item->user_id,
            'game_id'           =>  $item->game_id,
            'type'              =>  $item->type,
            'type_text'         =>  $item->type_text,
            'ip'                =>  $item->ip,
            'remark'            =>  $item->remark,
            'phone'             =>  $item->phone,
            'client_type'       =>  $item->client_type,
            'client_type_text'  =>  $item->client_type_text,
            're_time'           =>  date('Y-m-d H:i:s',strtotime($item->re_time)),
            'create_time'       =>  date('Y-m-d H:i:s',strtotime($item->create_time)),
            'admin_id'          =>  $item->admin_id,
            'admin_name'        =>  $item->username,
            'channel_sign'      =>  $item->channel->channel_id ?? '官方',
        ];
    }

}
