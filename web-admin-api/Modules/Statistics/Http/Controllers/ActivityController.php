<?php

namespace Modules\Statistics\Http\Controllers;

use App\Http\Controllers\Controller;
use Models\Accounts\AccountsInfo;
use Models\Record\RecordTreasureSerial;
use Transformers\ActivityReportDetailsTransformer;
use Transformers\ActivityReportTransformer;

class ActivityController extends Controller
{
   /**
    * 活动报表
    *
    */
   public function activityReport()
   {
       \Validator::make(request()->all(), [
           'start_date' => ['nullable', 'date'],
           'end_date'   => ['nullable', 'date'],
       ], [
           'start_date.date'        => '无效日期',
           'end_date.date'          => '无效日期',
       ])->validate();
       try{
           //红包、转盘、首充、签到、绑定、注册
           $list = RecordTreasureSerial::select('TypeID',
               \DB::raw("sum(ChangeScore) as ChangeScore"),
               \DB::raw("count(*) as num"),
               \DB::raw("count(distinct(UserID)) as people_num")
           )
               ->whereIn('TypeID',array_keys(RecordTreasureSerial::getTypes(2)))
               ->andFilterBetweenWhere('CollectDate', request('start_date'), request('end_date'))
               ->groupBy('TypeID')
               ->get();
           return $this->response->collection($list,new ActivityReportTransformer());
       }catch (\Exception $exception){
           return ResponeFails('异常错误');
       }
   }

   /**
    * 活动报表详情
    *
    */
   public function activityReportDetails()
   {
       \Validator::make(request()->all(), [
           'type'       => 'required|integer',
           'game_id'    => 'nullable|integer',
           'start_date' => 'nullable|date',
           'end_date'   => 'nullable|date',
       ], [
           'type.required'      => '类型不能为空',
           'type.integer'       => '类型必须是数字',
           'game_id.integer'    => '游戏id必须是数字',
           'start_date.date'    => '无效日期',
           'end_date.date'      => '无效日期',
       ])->validate();
       try{
           //红包、转盘、首充、签到、绑定、注册
           $list = AccountsInfo::from('AccountsInfo as a')
               ->with(['payment','withdraw','dayWater'])
               ->select('a.GameID','b.UserID',
                   \DB::raw("max(a.PlayTimeCount) as PlayTimeCount"),
                   \DB::raw("max(b.CollectDate) as CollectDate"),
                   \DB::raw("sum(b.ChangeScore) as ChangeScore"),
                   \DB::raw("count(*) as num"),
                   \DB::raw("count(distinct(b.UserID)) as people_num")
               )
               ->rightJoin(RecordTreasureSerial::tableName().' as b','a.UserID','=','b.UserID')
               ->where('a.IsAndroid',0)
               ->where('b.TypeID',request('type'))
               ->andFilterWhere('a.GameID',request('game_id'))
               ->groupBy('a.GameID','b.UserID')
               ->orderBy(\DB::raw("max(b.CollectDate)"),'desc')
               ->paginate(config('page.list_rows'));
           foreach ($list as $k => $v){
               $list[$k]['payment_score']  = $v->payment->sum('amount');//充值
               $list[$k]['withdraw_score'] = $v->withdraw->sum('money');
               $list[$k]['bet_score']      = $v->dayWater->sum('JettonScore');//投注
               $list[$k]['winlose_score']  = $v->dayWater->sum('ChangeScore');//输赢
               unset($list[$k]['payment']);
               unset($list[$k]['withdraw']);
               unset($list[$k]['dayWater']);
           }
           return $this->response->paginator($list,new ActivityReportDetailsTransformer());
       }catch (\Exception $exception){
           return ResponeFails('异常错误');
       }
   }

}
