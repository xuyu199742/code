<?php
//用户代理信息表
namespace Models\Agent;



class AgentInfo extends Base
{
    protected $table = 'agent_info';

    //定义禁止礼金勾选字段
    const FORBID_GIFTS = [
        'reg_give'  => 1,//注册赠送
    ];

}
