<?php
/* 转盘配置*/
namespace Models\Activity;


class RotaryConfig extends Base
{
    //数据表
    protected $table = 'rotary_config';

    protected $guarded = [''];

    public $timestamps = false;

    //抽奖结果
    public static function rotaryRand($rotary_type, $rank_type)
    {
        $configs = RotaryConfig::query()
            ->where('pid', $rotary_type)
            ->where('rank_type', $rank_type)
            ->where('reward','>',0)
            ->where('weight','>',0)
            ->orderBy('weight')
            ->get()->toArray();
        $arr = array_column($configs, 'weight');
        $total = array_sum($arr);
        $rand = rand(1, $total);
        $weight = 0;
        $index = 0;
        foreach ($arr as $k => $v) {
            $weight += $v;
            if ($rand <= $weight) {
                $index = $k;
                break;
            }
        }
        //中奖结果
        return [
            'id' => $configs[$index]['id'] ?? 0,
            'reward' => $configs[$index]['reward'] ?? 0
        ];
    }
}
