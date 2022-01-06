<?php

namespace Modules\User\Http\Controllers;

use App\Imports\EmailToPlayersImport;
use App\Jobs\AllUserInform;
use App\Http\Controllers\Controller;
use App\Jobs\SendMailChannel;
use App\Jobs\SendMailImport;
use App\Jobs\SendMailUser;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\EntityNotFoundException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Matrix\Exception;
use Models\Accounts\AccountsInfo;
use Models\Agent\ChannelInfo;
use Models\Treasure\GameMailInfo;
use Models\Treasure\GameMailInfoJob;
use Models\Treasure\GameMailInfoReceive;
use Transformers\GameMailInfoTransformer;
use Transformers\MailReceiveTransformer;
use Modules\System\Http\Requests\UserInformRequest;
use Validator;

class EmailController extends Controller
{
    //邮件发送列表
    public function sendEmailList(Request $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable','numeric'],
           // 'isRead'     => ['nullable','numeric'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
        ])->validate();
        $list=GameMailInfo::table()->whereHas('account', function ($query) use ($request){
            $query->from(AccountsInfo::tableName())->andFilterWhere('GameID',$request->input('game_id'));
        })->with(['account'=>function($query) use ($request){
            $query->select('UserID','GameID','NickName')->andFilterWhere('GameID',$request->input('game_id'));
        }])->andFilterBetweenWhere('CreateTime',request('start_date'),request('end_date'))
            ->orderBy('CreateTime', 'desc')
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new GameMailInfoTransformer());
    }
    //收件箱列表
    public function userEmailList(Request $request)
    {
        Validator::make(request()->all(), [
            'game_id'    => ['nullable','numeric'],
            'is_reply'    => ['nullable','in:0,1'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
        ], [
            'game_id.numeric' => 'game_id必须数字',
            'is_reply.in'     => '回复状态不在可选范围！',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
        ])->validate();
        $list=GameMailInfoReceive::table()->whereHas('account', function ($query) use ($request){
            $query->from(AccountsInfo::tableName())->andFilterWhere('GameID',$request->input('game_id'));
        })->with(['account'=>function($query) use ($request){
            $query->select('UserID','GameID','NickName')->andFilterWhere('GameID',$request->input('game_id'));
        }])->andFilterBetweenWhere('CreateTime',request('start_date'),request('end_date'))
            ->andFilterWhere('IsReply', $request->input('is_reply'))
            ->orderBy('CreateTime', 'desc')
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list,new MailReceiveTransformer())->addMeta('reply_status', GameMailInfoReceive::REPLAY_STATUS);
    }
    /*发送邮件接口*/
    public function sendEmail(UserInformRequest $request)
    {
        try{
            $request['admin_id']=$this->user()->id;
            if (request('SendType') == 0){//调动所有人发送任务
                //判断是否存在定时
                if (!empty(request('StartTime')) && request('TimeType') == 2){
                    $res = AllUserInform::dispatch($request->toArray())->delay(Carbon::parse(request('StartTime')));
                }else{
                    $res = AllUserInform::dispatch($request->toArray());
                }
            }elseif (request('SendType') == 1){//调动按玩家发送任务
                //判断是否存在定时
                if (!empty(request('StartTime')) && request('TimeType') == 2){
                    $res = SendMailUser::dispatch($request->toArray())->delay(Carbon::parse(request('StartTime')));
                }else{
                    $res = SendMailUser::dispatch($request->toArray());
                }
                //修改收件箱的邮件的回复状态
                if($request['ID'])
                {
                    $model = GameMailInfoReceive::find($request['ID']);
                    if(!$model)
                    {
                        return ResponeFails('该邮件不存在');
                    }
                    $model->IsReply  = GameMailInfoReceive::REPLAY_YES;
                    $model->admin_id =$request['admin_id'];
                    $model->save();
                }
            }elseif (request('SendType') == 2) {//调动按渠道发送任务
                $channel_id = $request->input('ChannelID');
                $channel_info = ChannelInfo::find($channel_id);
                if (!$channel_info) {
                    return ResponeFails('该渠道不存在');
                }
                //判断是否存在定时
                if (!empty(request('StartTime')) && request('TimeType') == 2) {
                    $res = SendMailChannel::dispatch($request->toArray())->delay(Carbon::parse(request('StartTime')));
                } else {
                    $res = SendMailChannel::dispatch($request->toArray());
                }
            }elseif (request('SendType') == 3){ //按导入excel发送
                //$excel_file_path=$request->file('import');//接受文件路径
                $data = $request->all();
                /* $datas=Excel::toArray(new EmailToPlayersImport(), $excel_file_path);
                   $list = $datas[0] ?? [];*/
                $list = $data['import'] ?? [];
                if(count($list) < 1) {
                    return ResponeFails('导入缺少数据');
                }
                $sum = count($list);
                $info = [];
                $num = 2;
                $times = ceil($sum/$num);
                for($i = 0; $i < $times; $i++){
                    foreach ($list as $k => $v){
                        if ($i * $num <= $k && $k  < ($i + 1) * $num){
                            $info[$i][] = $v;
                        }
                    }
                }
                $data_user  = [];
                //设置批量单次条数
                foreach ($info as $k => $v){
                    $user_ids   = AccountsInfo::whereIn('GameID',$v)->pluck('UserID');
                    foreach($user_ids as $key => $value) {
                        $data_user[$k][] = [
                            'UserID'    => $value,
                            'Title'     => $data['Title'],
                            'Context'   => $data['Context'],
                            'CreateTime'=> date('Y-m-d H:i:s',time()),
                            'admin_id'  => $data['admin_id'] ?? 0,
                            'receive_id'=> $data['ID'] ?? 0,
                        ];
                    }
                }
                //判断是否存在定时
                if (!empty(request('StartTime')) && request('TimeType') == 2) {
                    $res = SendMailImport::dispatch($data_user,$list)->delay(Carbon::parse(request('StartTime')));
                } else {
                    $res = SendMailImport::dispatch($data_user,$list);
                }
            }
            if ($res){
                return ResponeSuccess('发送成功');
            }else{
                return ResponeFails('发送失败');
            }
        }catch (Exception $exception){
            return ResponeFails('发送失败');
        }
    }
}
