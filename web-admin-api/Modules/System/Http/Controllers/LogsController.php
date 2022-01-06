<?php
/* 日志*/
namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\ErrorLog;
use Models\AdminPlatform\GameControlLog;
use Models\AdminPlatform\LoginLog;
use Models\AdminPlatform\OrderLog;
use Models\AdminPlatform\SmsLog;
use Models\AdminPlatform\SystemLog;
use Models\AdminPlatform\UserLogonLog;
use Transformers\ErrorLogTransformer;
use Transformers\GameControlLogTransformer;
use Transformers\LoginLogTransformer;
use Transformers\OrderLogTransformer;
use Transformers\SmsLogTransformer;
use Transformers\SystemLogTransformer;
use Transformers\UserLogonLogTransformer;

class LogsController extends Controller
{
    /*
     * 错误日志
     * */
    public function error_logs(Request $request)
    {
        $list = ErrorLog::orderBy('created_at','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new ErrorLogTransformer());

    }
    /*
     * 登录日志
     * */
    public function login_logs(Request $request)
    {
        $list = LoginLog::orderBy('created_at','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new LoginLogTransformer());
    }
    /*
     * 订单日志
     * */
    public function order_logs(Request $request)
    {
        $list = OrderLog::orderBy('created_at','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new OrderLogTransformer());
    }
    /*
     * 短信日志
     * */
    public function sms_logs(Request $request)
    {
        $list = SmsLog::orderBy('created_at','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new SmsLogTransformer());
    }
    /*
     * 系统日志
     * */
    public function system_logs(Request $request)
    {
        $list = SystemLog::orderBy('created_at','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new SystemLogTransformer());
    }
    /**
     * 自动稽核查询日志
     *
     */
    public function user_logon_logs(Request $request){
        \Validator::make(request()->all(), [
            'game_id'       => ['nullable','integer'],
            //'ip'            => ['nullable','regex:/^(?=(\b|\D))(((\d{1,2})|(1\d{1,2})|(2[0-4]\d)|(25[0-5]))\.){3}((\d{1,2})|(1\d{1,2})|(2[0-4]\d)|(25[0-5]))(?=(\b|\D))$/'],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date'],
        ], [
            'game_id.integer'  => '玩家ID必须是整数',
            //'ip.regex'         => 'ip地址格式不正确',
            'start_date.date'  => '无效日期',
            'end_date.date'    => '无效日期',
        ])->validate();
        $search_type = request('search_type','ip'); // ip || DeviceNumber || RegDeviceName || SystemVersion
        $search_content = request('search_content','');
        $list = UserLogonLog::from(UserLogonLog::tableName().' as a')
            ->leftJoin(AccountsInfo::tableName().' as b','a.user_id','=','b.UserID')
            ->select('a.*','b.GameID','b.MemberOrder')
            ->andFilterWhere('b.GameID',$request->input('game_id'))
            ->where(function($query) use ($search_content, $search_type) {
                if($search_content){
                    if ($search_type == 'ip'){      //ip地址
                        $query->where('a.ip_addr', $search_content);
                    }else if ($search_type == 'DeviceNumber'){     //设备号
                        $query->where('a.info','like','%:'.$search_content);
                    }else if ($search_type == 'RegDeviceName'){    //手机型号
                        $query->where('a.device_name', $search_content);
                    } else if ($search_type == 'SystemVersion'){    //系统版本
                        $query->where('a.system_version', $search_content);
                    }
                }
            })
            ->andFilterBetweenWhere('a.create_date',request('start_date'),request('end_date'))
            ->orderBy('a.create_date','desc')
            ->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new UserLogonLogTransformer());
    }

    /**
     * 控制端操作日志
     *
     */
    public function gameControlLog(Request $request)
    {
        \Validator::make(request()->all(), [
            'username'     => ['nullable'],
            'action_types' => ['nullable', 'in:1,2,3,4,5,6,9'],
            'status'       => ['nullable', 'in:0,1'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date'],
        ], [
            'action_types.in' => '动作不在可选范围内',
            'status.date'     => '状态不在可选范围内',
            'start_date.date' => '无效日期',
            'end_date.date'   => '无效日期',
        ])->validate();
        $list = GameControlLog::whereHas('admin', function ($query) use ($request){
            if(request('username')){
                $query->where('username',$request->input('username'));
            }
        })->andFilterWhere('action',$request->input('action_types'))
            ->andFilterWhere('status',$request->input('status'))
            ->andFilterBetweenWhere('create_time',request('start_date'),request('end_date'))
            ->orderBy('create_time','desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new GameControlLogTransformer())
            ->addMeta('action_types',GameControlLog::ACTION_TYPES)
            ->addMeta('status_text',GameControlLog::STATUS);
    }

}
