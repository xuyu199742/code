<?php
/*用户*/
namespace Modules\User\Http\Controllers;
use App\Jobs\RepairLevel;
use Models\Accounts\AccountsInfo;
use Models\Accounts\AndroidVIPWeight;
use Models\Accounts\MembersHandsel;
use Models\Accounts\MembersHandselLogs;
use Models\Accounts\MembersInfo;
use Illuminate\Validation\Rule;
use Models\AdminPlatform\PaymentOrder;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Transformers\MembersHandselLogsTransformer;
use Transformers\MembersHandselTransformer;
use Transformers\MembersInfoTransformer;
use DB,Validator;

class MembersController extends BaseController
{

    /**
     * VIP列表
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getList()
    {
        $list = MembersInfo::from('MembersInfo as a')->select(
            'a.id','a.MemberOrder','a.UpperLimit','a.LowerLimit','a.Status',
            DB::raw('(SELECT count(UserID) FROM '.AccountsInfo::tableName().' WHERE IsAndroid = 0 and a.MemberOrder = MemberOrder) as AccountsNum'),
            DB::raw('(SELECT sum(HandselCoins) FROM '.MembersHandselLogs::tableName().' WHERE a.id = MembersID)  as HandselCoinsSum')
        )
        ->orderBy('a.MemberOrder','asc')
        ->paginate(20);
        foreach ($list as $k => $item){
            $list[$k]['LowerLimit'] = $list[$k]['LowerLimit']/10000;
            $list[$k]['UpperLimit'] = $list[$k+1]['LowerLimit']/10000 == 0 ? '上不封顶': $list[$k+1]['LowerLimit']/10000;
        }
        return $this->response->paginator($list, new MembersInfoTransformer())->addMeta('status',1);
    }

    //获取VIP关联活动配置
    public function getRelationConfig(){
        $activity = MembersInfo::first();
        $list = MembersInfo::pluck('ExtraIncomeRate','MemberOrder')->toArray();
        $data = [
            'IsProfit' =>  $activity['IsProfit'] ?? 0,
            'IsLoss'   =>  $activity['IsLoss'] ?? 0,
            'IsTask'   =>  $activity['IsTask'] ?? 0,
            'IsWater'  =>  $activity['IsWater'] ?? 0,
            'IsPour'   =>  $activity['IsPour'] ?? 0,
            'RelationStatus' => $activity['RelationStatus'] ?? 0,
            'ExtraIncomeRate'=> $list
        ];
        return ResponeSuccess('请求成功', $data);
    }

    //修改VIP关联活动配置
    public function setRelationConfig(){
        Validator::make(request()->all(), [
            'IsProfit'=> ['required','integer',Rule::in([0,1])],
            'IsLoss'  => ['required','integer',Rule::in([0,1])],
            'IsTask'  => ['required','integer',Rule::in([0,1])],
            'IsWater' => ['required','integer',Rule::in([0,1])],
            'IsPour'  => ['required','integer',Rule::in([0,1])],
            'RelationStatus'  => ['required','integer',Rule::in(array_keys(MembersInfo::RelationStatus))],
            'ExtraIncomeRate' => ['nullable','array']
        ])->validate();
        $input = request()->input();
        $ExtraIncomeRates = $input['ExtraIncomeRate'] ?? [];
        if(empty($ExtraIncomeRates)){
            return ResponeFails('暂无VIP,无法设置活动额外福利设置');
        }
        foreach ($ExtraIncomeRates as $MemberOrder => $ExtraIncomeRate){
            MembersInfo::where('MemberOrder',$MemberOrder)->update([
                'IsProfit' =>  $input['IsProfit'] ?? 0,
                'IsLoss'   =>  $input['IsLoss'] ?? 0,
                'IsTask'   =>  $input['IsTask'] ?? 0,
                'IsWater'  =>  $input['IsWater'] ?? 0,
                'IsPour'   =>  $input['IsPour'] ?? 0,
                'RelationStatus' => $input['RelationStatus'] ?? 0,
                'ExtraIncomeRate'=> $ExtraIncomeRate
            ]);
        }
        return ResponeSuccess('操作成功');
    }

    //获取VIP信息
    public function getVIPInfo(){
        Validator::make(request()->all(), [
            'vipId'       => ['required','integer'],
        ])->validate();
        $data = MembersInfo::select(DB::raw('id as vipId'),'MemberOrder','UpperLimit','LowerLimit','Status')->find(request('vipId'));
        if(!$data){
            return ResponeFails('该等级VIP不存在');
        }
        $data['UpperLimit'] = $data['UpperLimit']/10000;
        $data['LowerLimit'] = $data['LowerLimit']/10000;
        $Handsel = MembersHandsel::select('HandselID','HandselType','HandselDays','HandselCoins')->where('MembersID',request('vipId'))->get()->toArray() ?: [];
        array_walk($Handsel,function(&$v){
            $v['HandselCoins'] = realCoins($v['HandselCoins']);
        });
        $data['Handsel'] = $Handsel;
        return ResponeSuccess('请求成功',$data);
    }

    //新增VIP
    public function addVIP(){
        $type = config('set.vip_upgrade_type') ?? 2;
        $title= $type == 2 ? '稽核' :'充值';
        Validator::make(request()->all(), [
            'MemberOrder' => ['required','integer','min:1','max:'.MembersInfo::MaxLevel],
            'LowerLimit'  => ['required','integer','min:0'],
            'Status'      => ['required','integer',Rule::in(array_keys(MembersInfo::STATUS))],
            'Handsel'     => ['nullable','array']
        ], [
            'MemberOrder.required' => 'VIP等级必填',
            'MemberOrder.integer' => 'VIP等级必须是整数',
            'MemberOrder.min' => 'VIP等级最小为1级',
            'MemberOrder.max' => 'VIP等级最大为'.MembersInfo::MaxLevel.'级',
            'LowerLimit.required' => $title.'量必填',
            'LowerLimit.integer' => $title.'量必须是整数',
            'LowerLimit.min' => $title.'量最低数值为0'
        ])->validate();
        $input = request()->input();
        if(MembersInfo::where('MemberOrder',$input['MemberOrder'])->count()){
            return ResponeFails('该VIP等级已存在');
        }
        $Handsel = $input['Handsel'] ?? [];
        if (!empty($Handsel)) {
            if (count($Handsel) > count(array_unique(array_column($Handsel, 'HandselType')))) {
                return ResponeFails('日,周,月,晋级彩金每种类型只能有一个');
            }
        }
        //判断上一级VIP是否被禁用，禁用则该等级也被禁用
        $activity = MembersInfo::where('MemberOrder','<',$input['MemberOrder'])->orderBy('MemberOrder','desc')->first();
        if($activity){
            if($activity['Status'] == 1 && $input['Status'] == 1 ){
                $input['Status'] = 1;
            }else{
                $input['Status'] = 0;
            }
        }
        if(moneyToCoins($input['LowerLimit']) < $activity['LowerLimit']){
            return ResponeFails($title.'量不能小于或等于上一级'.$title.'量');
        }
        MembersInfo::beginTransaction([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
        $MembersInfo = MembersInfo::create([
            'MemberOrder' => $input['MemberOrder'],
            'UpperLimit' => moneyToCoins($input['LowerLimit']),
            'LowerLimit' => moneyToCoins($input['LowerLimit']),
            'Status' => $input['Status'],
            'IsProfit' =>  $activity['IsProfit'] ?? 0,
            'IsLoss'   =>  $activity['IsLoss'] ?? 0,
            'IsTask'   =>  $activity['IsTask'] ?? 0,
            'IsWater'  =>  $activity['IsWater'] ?? 0,
            'IsPour'   =>  $activity['IsPour'] ?? 0,
            'RelationStatus' => $activity['RelationStatus'] ?? 0,
        ]);
        if (!$MembersInfo) {
            DB::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
            return ResponeFails('操作失败');
        }
        if (!empty($Handsel)) {
            foreach ($Handsel as $item) {
                //必须是日彩金
                if($item['HandselType'] == 1 && !is_numeric($item['HandselDays'])){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金时间必须为整数');
                }
                if(!is_numeric($item['HandselCoins'])){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金数必须为整数');
                }
                if($item['HandselType'] == 1 && $item['HandselDays'] < 1){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金时间最小为1');
                }
                if($item['HandselCoins'] < 0){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金数最小为0');
                }
                try{
                    MembersHandsel::firstOrCreate([
                        'MembersID'   => $MembersInfo->id,
                        'HandselType' => $item['HandselType'],
                        'HandselDays' => $item['HandselDays'] ? : 1,
                        'HandselCoins'=> moneyToCoins($item['HandselCoins'])
                    ]);
                }catch (\Exception $e){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('福利配置失败:'.$e->getMessage());
                }
            }
        }
        MembersInfo::commit([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
        return ResponeSuccess('添加成功');
    }

    //修改VIP
    public function editVIP(){
        $type = config('set.vip_upgrade_type') ?? 2;
        $title = $type == 2 ? '稽核':'充值';
        Validator::make(request()->all(), [
            'vipId'       => ['required','integer'],
            'MemberOrder' => ['required','integer','min:1','max:'.MembersInfo::MaxLevel],
            'LowerLimit'  => ['required','integer','min:0'],
            'Status'      => ['required','integer',Rule::in(array_keys(MembersInfo::STATUS))],
            'Handsel'     => ['nullable','array']
        ], [
            'MemberOrder.required'  => 'VIP等级必填',
            'MemberOrder.integer'   => 'VIP等级必须是整数',
            'MemberOrder.min'       => 'VIP等级最小为1级',
            'MemberOrder.max'       => 'VIP等级最大为'.MembersInfo::MaxLevel.'级',
            'LowerLimit.required'   => $title.'量下限必填',
            'LowerLimit.integer'    => $title.'量下限必须是整数',
            'LowerLimit.min'        => $title.'量下限最低数值为0'
        ])->validate();
        $input = request()->input();
        if($input['MemberOrder'] > MembersInfo::MaxLevel){
            return ResponeFails('最大VIP等级不能超过'.MembersInfo::MaxLevel.'级');
        }
        if(MembersInfo::where('MemberOrder',$input['MemberOrder'])->where('id','<>',$input['vipId'])->count()){
            return ResponeFails('该VIP等级已存在');
        }
        $Handsel = $input['Handsel'] ?? [];
        if (!empty($Handsel)) {
            if (count($Handsel) > count(array_unique(array_column($Handsel, 'HandselType')))) {
                return ResponeFails('日,周,月，晋级彩金每种类型只能有一个');
            }
        }
        //判断上一级VIP是否被禁用，禁用则该等级也被禁用
        $desc = MembersInfo::where('MemberOrder','<',$input['MemberOrder'])->orderBy('MemberOrder','desc')->first();
        if($desc){
            if($desc['Status'] != 1){
                if($input['Status'] == 1){
                    return ResponeFails('上一级VIP已被禁用,该状态无法修改');
                }
            }
        }
        if($input['Status'] != 1){
            //低于该等级的状态都禁用
            MembersInfo::where('MemberOrder','>',$input['MemberOrder'])->update(['Status' => 0]);
        }
        if(!empty($desc) && moneyToCoins($input['LowerLimit']) <= $desc['LowerLimit']){
            return ResponeFails($title.'量不能小于或等于上一级'.$title.'量');
        }
        $asc = MembersInfo::where('MemberOrder','>',$input['MemberOrder'])->orderBy('MemberOrder','asc')->first();
        if($asc){
            if(moneyToCoins($input['LowerLimit']) >= $asc['LowerLimit']){
                return ResponeFails($title.'量不能大于或等于下一级'.$title.'量');
            }
        }
        MembersInfo::beginTransaction([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
        $MembersInfo = MembersInfo::where('id',$input['vipId'])->update([
            'MemberOrder'=> $input['MemberOrder'],
            'UpperLimit' => moneyToCoins($input['LowerLimit']),
            'LowerLimit' => moneyToCoins($input['LowerLimit']),
            'Status'     => $input['Status'],
        ]);

        if (!$MembersInfo) {
            MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
            return ResponeFails('修改失败');
        }
        $HandselIDs = MembersHandsel::where('MembersID',$input['vipId'])->pluck('HandselID')->toArray();
        if (!empty($Handsel)) {
            $needDelIDs = array_diff($HandselIDs,array_filter(array_column($Handsel,'HandselID')));
            /*if(MembersHandselLogs::whereIn('HandselID',$needDelIDs)->count()){
                return ResponeFails('该福利彩金已有人领取,无法删除');
            }*/
            MembersHandsel::whereIn('HandselID',$needDelIDs)->delete();
            foreach ($Handsel as $item) {
                if($item['HandselType'] == 1 && !is_numeric($item['HandselDays'])){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金时间必须为整数');
                }
                if(!is_numeric($item['HandselCoins'])){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金数必须为整数');
                }
                if($item['HandselType'] == 1 && $item['HandselDays'] < 1){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金时间最小为1');
                }
                if($item['HandselCoins'] < 0){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('彩金数最小为0');
                }
                try{
                    if($item['HandselID'] ?? 0){
                        MembersHandsel::where('HandselID',$item['HandselID'])->update([
                            'MembersID'   => $input['vipId'],
                            'HandselType' => $item['HandselType'],
                            'HandselDays' => $item['HandselDays'] ? : 1,
                            'HandselCoins'=> moneyToCoins($item['HandselCoins'])
                        ]);
                    }else{
                        MembersHandsel::create([
                            'MembersID'   => $input['vipId'],
                            'HandselType' => $item['HandselType'],
                            'HandselDays' => $item['HandselDays'] ? : 1,
                            'HandselCoins'=> moneyToCoins($item['HandselCoins'])
                        ]);
                    }
                }catch (\Exception $e){
                    MembersInfo::rollback([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
                    return ResponeFails('福利配置失败:'.$e->getMessage());
                }
            }
        }else{
            /*if(MembersHandselLogs::whereIn('HandselID',$HandselIDs)->count()){
                return ResponeFails('该福利彩金已有人领取,无法删除');
            }*/
            MembersHandsel::whereIn('HandselID',$HandselIDs)->delete();
        }
        MembersInfo::commit([MembersInfo::connectionName(),MembersHandsel::connectionName()]);
        return ResponeSuccess('修改成功');
    }

    //获取该等级VIP奖励详情
    public function getMemberHandsel(){
        Validator::make(request()->all(), [
            'vipId'       => ['required','integer'],
        ])->validate();
        if(!MembersInfo::find(request('vipId'))){
            return ResponeFails('该等级VIP不存在');
        }
        $data = MembersHandsel::from('MembersHandsel as a')->select(
            'a.HandselID','a.HandselType','a.HandselDays',
            DB::raw('count(b.UserID) as CollectNum'),
            DB::raw('sum(b.HandselCoins) as CollectCoins')
        )
            ->leftJoin(MembersHandselLogs::tableName().' AS b','a.HandselID','=','b.HandselID')
            ->where('a.MembersID',request('vipId'))
            ->groupBy('a.HandselID','a.HandselType','a.HandselDays')
            ->get();
        return $this->response->collection($data, new MembersHandselTransformer());
    }

    //获取福利类型选项
    public function getHandselType(){
        Validator::make(request()->all(), [
            'vipId'       => ['required','integer'],
        ])->validate();
        if(!MembersInfo::find(request('vipId'))){
            return ResponeFails('该等级VIP不存在');
        }
        $data = MembersHandsel::where('MembersID',request('vipId'))->get();
        return $this->response->collection($data, new MembersHandselTransformer());
    }

    //获取VIP奖励日志列表
    public function getHandselLogs(){
        Validator::make(request()->all(), [
            'vipId'         => ['required','integer'],
            'created_start' => ['nullable', 'date'],
            'created_end'   => ['nullable', 'date'],
            'upgrade_start' => ['nullable', 'date'],
            'upgrade_end'   => ['nullable', 'date'],
            'game_id'       => ['nullable', 'integer'],
            'HandselType'     => ['nullable', 'integer'],
        ])->validate();
        if(!MembersInfo::find(request('vipId'))){
            return ResponeFails('该等级VIP不存在');
        }
        $list = MembersHandselLogs::from('MembersHandselLogs as a')->select(
            'a.id','a.HandselID','a.UserID','b.GameID','b.NickName','a.HandselType','a.HandselDays','a.HandselCoins','a.VipUpgradeTime','a.CreatedTime',
            DB::raw('(SELECT sum(amount) FROM '.PaymentOrder::tableName().' WHERE a.UserID = user_id and payment_status = '."'".PaymentOrder::SUCCESS."'".') as PayAmount'),
            //DB::raw('(SELECT sum(WinScore) FROM '.GameScoreInfo::tableName().' WHERE a.UserID = UserID)  as WinScore'),
            DB::raw('(SELECT sum(RewardScore) FROM '.RecordGameScore::tableName().' WHERE a.UserID = UserID)  as RewardScore'),
            DB::raw('(SELECT sum(JettonScore) FROM '.RecordGameScore::tableName().' WHERE a.UserID = UserID)  as JettonScore'),
            DB::raw('(SELECT sum(RewardScore - JettonScore) FROM '.RecordGameScore::tableName().' WHERE a.UserID = UserID) as UserWinLose'),
            DB::raw('(SELECT sum(JettonScore - RewardScore) FROM '.RecordGameScore::tableName().' WHERE a.UserID = UserID) as PayOutScore')
        )
            ->leftJoin(AccountsInfo::tableName().' AS b','a.UserID','=','b.UserID')
            ->where('a.MembersID',request('vipId'))
            ->andFilterBetweenWhere('a.CreatedTime',request('created_start'),request('created_end'))
            ->andFilterBetweenWhere('a.VipUpgradeTime',request('upgrade_start'),request('upgrade_end'))
            ->andFilterWhere('b.GameID',request('game_id'))
            ->andFilterWhere('a.HandselType',request('HandselType'))
            ->groupBy('a.id','a.UserID','b.GameID','b.NickName','a.HandselID','a.HandselType','a.HandselDays','a.HandselCoins','a.VipUpgradeTime','a.CreatedTime')
            ->paginate(20);
        return $this->response->paginator($list, new MembersHandselLogsTransformer());
    }

    //修改所有VIP状态
    public function setVipStatus(){
        Validator::make(request()->all(), [
            'status'         => ['required','integer',Rule::in(array_keys(MembersInfo::STATUS))]
        ])->validate();
        MembersInfo::where('id','>',0)->update(['Status' => request('status')]);
        return ResponeSuccess('修改成功',['status' => request('status')]);
    }

    //修复所有用户VIP等级
    public function repairLevel(){
        $vip_upgrade_type = config('set.vip_upgrade_type') ?? 2;
        if($vip_upgrade_type == 2){//1：充值 2：有效投注
            $sql = '{ call GSP_GR_UserVipUpdate() }';
            $res = DB::connection('treasure')->update($sql);
            if ($res == 0) {
                return ResponeSuccess('修复成功');
            } else {
                return ResponeFails('修复失败');
            }
        }else {
            $res = RepairLevel::dispatch();
            if ($res) {
                return ResponeSuccess('成功执行修复队列');
            } else {
                return ResponeFails('修复失败');
            }
        }
    }

    /**
     * 获取机器人VIP等级配置列表
     */
    public function androidVipList(){
        $levelList = MembersInfo::where('Status',1)->pluck('MemberOrder')->toArray();
        $list = AndroidVIPWeight::select('ServerLevel','nWeight','MemberOrder')->whereIn('MemberOrder',$levelList)->orWhere('MemberOrder',0)->orderBy('MemberOrder')->get()->groupBy('MemberOrder')->toArray();
        $data = [];
        foreach($list as $key => $item){
            if(!empty($item)){
                $data[$key] = [
                    'MemberOrder' => $item[0]['MemberOrder'] ?? 0,
                    'server1'     => 0,
                    'server2'     => 0,
                    'server3'     => 0,
                    'server4'     => 0,
                ];
                foreach($item as $v){
                    $data[$key]['server'.$v['ServerLevel']] = $v['nWeight'];
                }
            }
        }
        $total = [
            'serverTotal1'     => 0,
            'serverTotal2'     => 0,
            'serverTotal3'     => 0,
            'serverTotal4'     => 0,
        ];
        $query = AndroidVIPWeight::select('ServerLevel',
            \DB::raw('SUM(nWeight) as nWeightTotal')
            )->whereIn('MemberOrder',$levelList)->orWhere('MemberOrder',0)->groupBy('ServerLevel')->pluck('nWeightTotal','ServerLevel')->toArray();
        foreach ($query as $kt => $vt){
            $total['serverTotal'.$kt] = $vt;
        }
        $levelList = MembersInfo::where('Status',1)->whereNotIn('MemberOrder',array_column($data,'MemberOrder'))->pluck('MemberOrder')->toArray();
        //去掉vip0，改为VIP1-20
//        if(!in_array('0',array_column($data,'MemberOrder'))){
//            array_unshift($levelList,'0');
//        }
        return ResponeSuccess('请求成功',['list' => $data,'total' => $total,'levelList' => $levelList]);
    }

    /**
     * 新增或编辑机器人VIP等级配置
     */
    public function androidVipEdit(){
        Validator::make(request()->all(), [
            'MemberOrder'   => ['required','integer'],
            'server1'       => ['required', 'integer'],
            'server2'       => ['required', 'integer'],
            'server3'       => ['required', 'integer'],
            'server4'       => ['required', 'integer'],
        ], [
            'MemberOrder.required' => 'VIP等级必填',
            'MemberOrder.integer' => 'VIP等级为整数',
            'server1.required' => '体验场权重值为必填',
            'server2.required' => '初级场权重值为必填',
            'server3.required' => '中级场权重值为必填',
            'server4.required' => '高级场权重值为必填',
            'server1.integer' => '体验场权重值为整数',
            'server2.integer' => '初级场权重值为整数',
            'server3.integer' => '中级场权重值为整数',
            'server4.integer' => '高级场权重值为整数',
        ])->validate();
        $MemberOrder = request('MemberOrder');
        $isE = MembersInfo::where('MemberOrder',$MemberOrder)->count();
        if(!$isE && $MemberOrder != 0){
            return ResponeFails('该会员等级不存在');
        }
        AndroidVIPWeight::beginTransaction([AndroidVIPWeight::connectionName()]);
        try{
            for($i = 1;$i <= 4;$i++){
                $serverI = request('server'.$i);
                if($serverI == ''){
                    continue;
                }
                $isSuccess = AndroidVIPWeight::where('MemberOrder',$MemberOrder)->updateOrCreate([
                    'MemberOrder' => $MemberOrder,
                    'ServerLevel' => $i
                ],[
                    'MemberOrder' => $MemberOrder,
                    'ServerLevel' => $i,
                    'nWeight'     => $serverI
                ]);
                if(!$isSuccess){
                    AndroidVIPWeight::rollback([AndroidVIPWeight::connectionName()]);
                    return ResponeFails('操作失败');
                }
            }
        }catch (\Exception $e){
            AndroidVIPWeight::rollback([AndroidVIPWeight::connectionName()]);
            return ResponeFails('操作失败：'.$e->getMessage());
        }
        AndroidVIPWeight::commit([AndroidVIPWeight::connectionName()]);
        return ResponeSuccess('操作成功');
    }

    /**
     * 删除机器人VIP等级配置
     */
    public function androidVipDel(){
        Validator::make(request()->all(), [
            'MemberOrder'   => ['required','integer','min:1'],
        ], [
            'MemberOrder.required' => 'VIP等级必填',
            'MemberOrder.integer' => 'VIP等级为整数',
            'MemberOrder.min' => 'VIP等级0不能删除',
        ])->validate();
        $isSuccess = AndroidVIPWeight::where('MemberOrder',request('MemberOrder'))->delete();
        if(!$isSuccess){
            return ResponeFails('删除失败');
        }
        return ResponeSuccess('删除成功');
    }

}
