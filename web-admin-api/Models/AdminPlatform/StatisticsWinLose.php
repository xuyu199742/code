<?php
/*游戏记录从表*/
namespace Models\AdminPlatform;
use Illuminate\Support\Facades\DB;
use Models\Platform\GameKindItem;

/**
 *  ID                      序列标识
 *  ServerID                房间标识
 *  KindID                  游戏标识
 *  ChangeScore             用户成绩
 *  JettonScore             用户下注量
 *  SystemScore             系统输赢
 *  SystemServiceScore      服务费
 *  CreateTime              创建时间
 *
 */

class StatisticsWinLose extends Base
{
    protected $table = 'statistics_win_lose';

    public $timestamps=false;

    /*
     * 批量添加数据
     *
     * */
    public function addAll(Array $data)
    {
        $rs = DB::table($this->getTable())->insert($data);
        return $rs;
    }
    /**
     * 关联游戏名称
     *
     */
    public function kinditem()
    {
        return $this->belongsTo(GameKindItem::class, 'kind_id', 'KindID');
    }
}
