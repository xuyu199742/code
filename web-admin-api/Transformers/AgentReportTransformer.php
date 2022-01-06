<?php
/*代理返利结算记录表*/
namespace Transformers;

use League\Fractal\TransformerAbstract;

class AgentReportTransformer extends TransformerAbstract
{

    public function transform($item)
    {
        if (isset($item->sub_people)){
            return [
                "created_at"                => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',//代理绑定日期
                "game_id"                   => $item->game_id,//代理的game_id
                "parent_game_id"            => $item->parent_game_id,//上级game_id
                "first_pay_people"          => $item->first_pay_people,//直推首充人数
                "first_pay_money"           => realCoins($item->first_pay_money ?? 0),//直推首充
                "sub_pay_people"            => $item->sub_pay_people,//直推充值人数
                "sub_pay_money"             => $item->sub_pay_money ?? 0,//直推充值
                "sub_withdrawal_people"     => $item->sub_withdrawal_people,
                "sub_withdrawal_money"      => $item->sub_withdrawal_money ?? 0,
                "sub_bet_people"            => $item->sub_bet_people,//直推投注人数
                "sub_bet_money"             => realCoins($item->sub_bet_money ?? 0),//直推投注
                //"sub_water"                 => realCoins($item->sub_water ?? 0),//直推用户流水
                "sub_winlose"               => realCoins($item->sub_winlose ?? 0),//直推用户输赢
                //"sub_balance"               => realCoins($item->sub_balance ?? 0),//直推账户余额
                "sub_people_num"            => $item->sub_people,//直属下级总人数
                "sub_people_add_num"        => $item->sub_people_add_num,//直属下级新增人数
                "brokerage"                 => realCoins($item->brokerage ?? 0),//佣金
                "tx_balance"                => realCoins($item->tx_balance ?? 0),//余额
            ];
        }else{
            return [
                "created_at"                => date('Y-m-d H:i:s',strtotime($item->created_at)) ?? '',//代理绑定日期
                "game_id"                   => $item->game_id,//代理的game_id
                "team_add_people"           => $item->team_add_people,//团队新增人数
                "team_sum_people"           => $item->team_sum_people,//团队总人数
                "team_reg_people"           => $item->team_reg_people,//团队注册人数
                "team_pay_people"           => $item->team_pay_people,//团队充值人数
                "team_pay_money"            => $item->team_pay_money ?? 0,//团队充值
                "team_withdrawal_people"    => $item->team_withdrawal_people ?? 0,
                "team_withdrawal_money"     => $item->team_withdrawal_money ?? 0,
                "team_bet_people"           => $item->team_bet_people,//团队投注人数
                "team_bet_money"            => realCoins($item->team_bet_money ?? 0),//团队投注
                "team_winlose"              => realCoins($item->team_winlose ?? 0),//团队用户输赢

                //"profit"                    => realCoins($item->profit ?? 0),//利润
                //"reg_rate"                  => $item->reg_rate,//注册付费率
                //"pay_rate"                  => $item->pay_rate,//APRU人均付费率
                //"team_water"                => realCoins($item->team_water ?? 0),//团队用户流水
                //"team_balance"              => realCoins($item->team_balance ?? 0),//团队账户余额
                //"WinnerPaid"                => realCoins($item->WinnerPaid ?? 0),//团队中奖

                "active_score"              => realCoins($item->active_score ?? 0),//团队活动礼金
                "active_people"             => $item->active_people,//团队日活跃人数
                "CurJettonScore"            => realCoins($item->team_bet_money ?? 0),//团队打码量
            ];
        }

    }

}
