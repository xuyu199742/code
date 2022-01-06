<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/9
 * Time: 11:34
 */
namespace Models\AdminPlatform;


class RemitConfig extends Base
{
    protected $table      = 'remit_config';
    protected $primaryKey = 'id';

    const STATUS_ON    = 0;
    const STATUS_OFF   = 1;

    const CHANG_REMIT_ID    = 1;//畅代付id
    const XIN_REMIT_ID      = 2;//新代付id
    const LX_REMIT_ID       = 3;//龙鑫代付id
    const HY_REMIT_ID       = 4;//火蚁付付id

}
