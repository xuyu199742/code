<?php

namespace Models\AdminPlatform;

/**
 * id                   int         序列id
 * score_count			int			赠送金币数
 * give_type			int			活动类型（1.注册,2.绑定）
 * platform_type	    int         平台类型（1.H5,2.U3D）
 * created_at		    int			创建时间
 * updated_at           int         修改时间
*/
class RegisterGive extends Base
{
    protected $table = 'register_give';  // 注册赠送

    const H5 = 1;
    const U3D = 2;
    const WEB_API = 4;

    const TYPE = [
        self::H5,
        self::U3D,
        self::WEB_API
    ];
}
