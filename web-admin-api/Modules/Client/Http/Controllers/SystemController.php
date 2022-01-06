<?php

namespace Modules\Client\Http\Controllers;

use Carbon\Carbon;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use FinalConst\AccountStatusCode;
use FinalConst\SystemStatusCode;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Intervention\Image\Facades\Image;
use Models\Accounts\AccountsInfo;
use Models\Accounts\MembersHandsel;
use Models\Accounts\MembersHandselLogs;
use Models\Accounts\MembersInfo;
use Models\Accounts\SystemStatusInfo;
use Models\Activity\ActivieAi;
use Models\Activity\ActivitiesNormal;
use Models\Activity\Activity;
use Models\Activity\ActivityConfig;
use Models\Activity\ActivityReturnConfig;
use Models\Activity\FirstChargeSignInLog;
use Models\Activity\PhonePayGive;
use Models\Activity\RedPacketConfig;
use Models\Activity\ReturnRecord;
use Models\Activity\RotaryConfig;
use Models\Activity\TurntableConfig;
use Models\Activity\UserRotaryInfo;
use Models\Activity\UserRotaryRecords;
use Models\AdminPlatform\Ads;
use Models\AdminPlatform\CarouselAffiche;
use Models\AdminPlatform\CarouselWebsite;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\SystemSetting;
use Models\AdminPlatform\VipBusinessman;
use Models\AdminPlatform\SystemNotice;
use Models\AdminPlatform\RegisterGive;
use Models\Record\RecordTreasureSerial;
use Models\Treasure\RecordScoreDaily;
use Models\Treasure\UserAuditBetInfo;
use Modules\Client\Transformers\ReturnRebateTransformer;
use Modules\Client\Http\Requests\RotaryDrawRequest;
use Modules\Client\Http\Requests\WithdrawalOrdersRequest;
use PHPUnit\Exception;
use Validator;

class SystemController extends Controller
{
    //获取系统设置
    public function setting()
    {
        //从系统配置中获取
        $data['coin'] = system_configs('coin');
        return ResponeSuccess('查询成功', $data);
    }

    //技术支持
    public function support()
    {
        $data = SystemSetting::where('group', 'technical_support')->where('key', 'website')->pluck('value', 'key');
        return ResponeSuccess('查询成功', $data);
    }

    //公告，下载地址，客服等等
    public function notice()
    {
        $type = \request('type');
        if ($type == 'h5') {
            $arr = [SystemNotice::PLATFORM_ALL, SystemNotice::PLATFORM_H5];
        } else {
            $arr = [SystemNotice::PLATFORM_ALL, SystemNotice::PLATFORM_U3D];
        }
        $data['activityList'] = Ads::whereIn('platform_type', $arr)->orderBy('sort_id', 'asc')->orderBy('created_at', 'desc')->limit(10)->get();
        $data['customerService'] = SystemSetting::where('group', 'customer_service')->pluck('value', 'key');
        $data['customerService']['link_url'] = isset($data['customerService']['link_url']) ? cdn($data['customerService']['link_url']) : '';
        $data['customerService']['kfProblem'] = 'kfProblemDetail';
        $data['systemNotice'] = SystemNotice::whereIn('PlatformType', $arr)
            ->where('Nullity', 0)
            ->orderBy('IsTop', 'desc')
            ->orderBy('IsHot', 'desc')
            ->orderBy('SortID', 'asc')
            ->orderBy('PublisherTime', 'desc')->limit(10)->get();
        foreach ($data['activityList'] as $k => $v) {
            $data['activityList'][$k]['resource_url'] = cdn($v['resource_url']);
        }
        $data['landingPage'] = SystemSetting::where('group', 'prots')->whereIn('key', [
            'h5_download_url',
            'app_download_url'
        ])->pluck('value', 'key');
        $ysf_config = SystemSetting::where('group', 'ysf_config')->where('key', 'ysf_url')->pluck('value', 'key');
        if ($ysf_config) {
            $data['ysf_config'] = isset($ysf_config['ysf_url']) && $ysf_config['ysf_url'] ? $ysf_config : ["ysf_url" => ''];
        } else {
            $data['ysf_config'] = ["ysf_url" => ''];
        }
        return ResponeSuccess('查询成功', $data);
    }

    /*
     * vip商人
     */
    public function vip_trader()
    {
        $list = VipBusinessman::where('nullity', VipBusinessman::NULLITY_ON)->orderBy('sort_id', 'asc')->orderBy('gold_coins', 'desc')->get();
        $data = [];
        foreach ($list as $k => $v) {
            $data[$k]['contact_information'] = $list[$k]['contact_information'];
            $data[$k]['type'] = $list[$k]['type'];
            $data[$k]['gold_coins'] = realCoins($list[$k]['gold_coins']);
            $data[$k]['avatar_url'] = cdn($list[$k]['avatar']);
        }
        return ResponeSuccess('查询成功', $data);
    }

    /*
     * 转盘规则说明
     *
     * */
    public function turntable_config($flag = false)
    {
        $list = ActivityConfig::whereIn('TurntableType', ActivityConfig::TURNTABLE_TYPE)->get();
        $now_time = date('Y-m-d H:i:s', time());
        $data = [];
        foreach ($list as $k => $v) {
            $data[$k]['turntable_type'] = $list[$k]['TurntableType'];
            $data[$k]['describe'] = $list[$k]['Describe'];
            if ($v['status'] == ActivityConfig::STATUS_ON) {
                if (strtotime($now_time) >= strtotime($list[$k]['StartTime']) && strtotime($now_time) <= strtotime($list[$k]['EndTime'])) {
                    $configs = TurntableConfig::where('TurntableType', $v['TurntableType'])->count();
                    if ($configs > 0) {
                        $data[$k]['open_conditions'] = true;
                    } else {
                        $data[$k]['open_conditions'] = false;
                    }
                } else {
                    $data[$k]['open_conditions'] = false;
                }
            } else {
                $data[$k]['open_conditions'] = false;
            }
        }
        if ($flag == true) {
            return $data;
        }
        return ResponeSuccess('查询成功', $data);
    }

    /**
     * 新转盘配置
     * @param $user_id
     * @return array
     */
    public function rotary_config($user_id)
    {
        $now = Carbon::now()->toDateTimeString();
        $configs = ActivitiesNormal::query()
            ->whereIn('pid', [ActivitiesNormal::BETTING_TURNTABLE, ActivitiesNormal::RECHARGE_TURNTABLE])
            ->where('btime', '<=', $now)
            ->where('etime', '>=', $now)
            ->where('status', 1)
            ->orderBy('pid')
            ->get();
        if (count($configs) > 0) {
            if (count($configs) == 1) {
                if ($configs[0]['pid'] == ActivitiesNormal::BETTING_TURNTABLE) {
                    $status1 = true;
                    $status2 = false;
                } else {
                    $status1 = false;
                    $status2 = true;
                }
            } else {
                $status1 = true;
                $status2 = true;
            }
            $response = [
                'bet' => [
                    'status' => $status1,
                ],
                'recharge' => [
                    'status' => $status2,
                ]
            ];
            $user = AccountsInfo::query()->find($user_id);
            if (!$user) {
                return $response;
            }
            //跨天重置
            UserRotaryInfo::dayReset($user_id);
            $rotaryConfig = RotaryConfig::query()->get();
            foreach ($configs as $config) {
                $content = json_decode($config->content ?? '', true);
                if (!$content) {
                    return $response;
                }
                switch ($config->pid) {
                    case ActivitiesNormal::BETTING_TURNTABLE:
                        $key = 'bet';
                        $validValue = RecordScoreDaily::query()
                            ->where('UserID', $user_id)
                            ->where('UpdateDate', date('Y-m-d'))
                            ->sum('JettonScore');
                        break;
                    case ActivitiesNormal::RECHARGE_TURNTABLE:
                        $key = 'recharge';
                        $validValue = PaymentOrder::query()
                            ->where('user_id', $user_id)
                            ->where('payment_status', PaymentOrder::SUCCESS)
                            ->whereBetween('created_at', [Carbon::now()->startOfDay()->toDateTimeString(), Carbon::now()->endOfDay()->toDateTimeString()])
                            ->sum('coins');
                        break;
                    default:
                        $key = '';
                        $validValue = 0;
                }
                $usedValue = UserRotaryInfo::where('user_id', $user_id)->where('activity_normal_id', $config->id)->value('used_value') ?? 0;

                $response[$key]['status'] = true;
                $response[$key]['notice'] = $content['notice'];
                $response[$key]['score_lower_limit'] = realCoins($content['score_lower_limit']);
                $response[$key]['describe'] = $content['describe'];
                $response[$key]['rank_list'] = [];
                $response[$key]['used_value'] = $usedValue / realRatio();

                foreach ($content['rank_switch'] as $rk => $rv) {
                    $rank_condition = $content['rank_condition'][$rk] ?? '';
                    if (!$rv || !$rank_condition) {
                        continue;
                    }
                    $list = $rotaryConfig->where('pid', $config->pid)
                        ->where('rank_type', $rk)
                        ->sortBy('region')
                        ->map(function ($item) {
                            return [
                                'reward_id' => $item->id,
                                'region' => $item->region,
                                'reward_score' => realCoins($item->reward)
                            ];
                        })->values();

                    $rotary_num = $content['rank_condition'][$rk] ? floor(($validValue - $usedValue) / $content['rank_condition'][$rk]) : 0;
                    $response[$key]['rank_list'][] = [
                        'rank_name' => ActivitiesNormal::RANK_LIST[$rk],
                        'rank_condition' => realCoins($rank_condition),
                        'validValue' => realCoins($validValue),
                        'rotary_num' => $rotary_num > 0 ? $rotary_num : 0,
                        'config_list' => $list
                    ];
                }
            }
        } else {
            $response = [
                'bet' => [
                    'status' => false,
                ],
                'recharge' => [
                    'status' => false,
                ]
            ];
        }
        return $response;
    }

    /*
     * 转盘领取
     */
    public function rotary_draw(RotaryDrawRequest $request)
    {
        $user = AccountsInfo::query()->with('GameScoreInfo')->find($request->user_id);
        if (!$user) {
            return ResponeFails('用户不存在');
        }
        //活动是否开启
        $rotary = ActivitiesNormal::query()->where('pid', $request->rotary_type)->first();
        $status = $rotary->status ?? 0;
        $now = Carbon::now()->toDateTimeString();
        if (!$status || $rotary->etime < $now) {
            return ResponeFails('活动暂未开始');
        }
        $rotaryConfig = json_decode($rotary->content ?? '', true);
        $rankCondition = $rotaryConfig['rank_condition'][$request->rank_type] ?? '';
        if (!$rankCondition) {
            return ResponeFails('活动暂未开始');
        }
        //跨天重置
        UserRotaryInfo::dayReset($request->user_id);
        $unusedValue = 0;
        switch ($request->rotary_type) {
            case ActivitiesNormal::BETTING_TURNTABLE:
                $unusedValue = RecordScoreDaily::query()
                    ->where('UserID', $request->user_id)
                    ->where('UpdateDate', date('Y-m-d'))
                    ->sum('JettonScore');
                break;
            case ActivitiesNormal::RECHARGE_TURNTABLE:
                $unusedValue = PaymentOrder::query()
                    ->where('user_id', $request->user_id)
                    ->where('payment_status', PaymentOrder::SUCCESS)
                    ->whereBetween('created_at', [Carbon::now()->startOfDay()->toDateTimeString(), Carbon::now()->endOfDay()->toDateTimeString()])
                    ->sum('coins');
                break;
        }
        $connection = [
            ActivitiesNormal::connectionName(),
            RecordTreasureSerial::connectionName(),
            UserAuditBetInfo::connectionName()
        ];
        //开启事务
        try {
            ActivitiesNormal::beginTransaction($connection);
            $usedValue = UserRotaryInfo::where('user_id', $request->user_id)->where('activity_normal_id', $rotary->id)->lockForUpdate()->value('used_value') ?? 0;
            $rotaryNum = floor(($unusedValue - $usedValue) / $rankCondition);
            if ($rotaryNum < 1) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖次数不足');
            }
            //抽奖结果
            $reward = RotaryConfig::rotaryRand($request->rotary_type, $request->rank_type);
            if (!$reward['id']) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖失败');
            }
            if (!UserRotaryInfo::query()->updateOrCreate(
                [
                    'user_id' => $request->user_id,
                    'activity_normal_id' => $rotary->id
                ], [
                    'used_value' => $usedValue + $rankCondition
                ]
            )) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖失败');
            }
            if (!UserRotaryRecords::query()->create([
                'user_id' => $request->user_id,
                'activity_normal_id' => $rotary->id,
                'rank_type' => $request->rank_type,
                'reward_score' => $reward['reward']
            ])) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖失败');
            }
            //加流水
            if (!AccountsInfo::addRecords($user, $reward['reward'], RecordTreasureSerial::ACTIVITY_DISH)) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖失败');
            }
            //加稽核
            if (!AccountsInfo::addAuditBets($user, $reward['reward'], RecordTreasureSerial::ACTIVITY_DISH)) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖失败');
            }
            //加金币
            if (!AccountsInfo::addCoinsNoNotice($user, $reward['reward'], RecordTreasureSerial::ACTIVITY_DISH)) {
                ActivitiesNormal::rollBack($connection);
                return ResponeFails('抽奖失败');
            }
            //事务提交
            ActivitiesNormal::commit($connection);
            return ResponeSuccess('抽奖成功', [
                'reward_id' => $reward['id'],
                'reward_score' => $reward['reward'],
                'rank_type' => $request->rank_type,
                'rotary_type' => $request->rotary_type,
                'rotary_num' => $rotaryNum > 1 ? $rotaryNum - 1 : 0,
                'used_value' => ($usedValue + $rankCondition) / realRatio()
            ]);
        } catch (\Exception $e) {
            ActivitiesNormal::rollBack($connection);
            \Log::error($request->user_id . "转盘领取领取失败-{$e}");
            return ResponeFails('抽奖失败');
        }
    }

    /**
     * 全服记录
     */
    public function prize_list()
    {
        $rotary_type = request('rotary_type');
        if (!$rotary_type) {
            return ResponeFails('缺少转盘类型');
        }
        $rotary = ActivitiesNormal::query()->where('pid', $rotary_type)->where('status', 1)->first();
        $rotaryConfig = json_decode($rotary->content ?? '', true);
        $keys = array_keys(array_filter($rotaryConfig['rank_switch'] ?? []));
        $lower = $rotaryConfig['score_lower_limit'] ?? 10000;
        $configs = RotaryConfig::query()
            ->where('pid', $rotary_type)
            ->where('reward', '>', $lower)
            ->whereIn('rank_type', $keys)
            ->get()
            ->toArray();
        $rewards = array_column($configs, 'id');
        $data = [];
        if ($rewards) {
            $randKeys = array_rand($rewards, count($rewards) > 10 ? 10 : count($rewards));
            $randKeys = is_array($randKeys) ? $randKeys : [$randKeys];
            foreach ($randKeys as $key => $randKey) {
                $item = $configs[$randKey] ?? [];
                if ($item) {
                    $divide_hour = 24 / count($randKeys);
                    $hour = $divide_hour * ($key + 1);
                    $hour_time = rand($hour - $divide_hour, $hour);
                    $data[] = [
                        'id' => $key + 1,
                        'date' => date('m-d'),
                        'time' => ($hour_time < 10 ? '0' . $hour_time : $hour_time) . ':' . rand(0, 5) . rand(0, 9),
                        'game_id' => '***' . rand(0, 9) . rand(0, 9),
                        'reward' => realCoins($item['reward']),
                        'rank_text' => ActivitiesNormal::RANK_LIST[$item['rank_type']]
                    ];
                }
            }
        }
        return ResponeSuccess('查询成功', $data);
    }

    /*
     * 红包规则说明
     * */
    public function red_packet_config($flag = false)
    {
        $list = ActivityConfig::where('TurntableType', ActivityConfig::RED_PACKET_TYPE)->first();
        $now_time = date('Y-m-d H:i:s', time());
        $data = [];
        $data['describe'] = $list['Describe'];
        if ($list['status'] == ActivityConfig::STATUS_ON) {
            if (strtotime($now_time) >= strtotime($list['StartTime']) && strtotime($now_time) <= strtotime($list['EndTime'])) {
                $configs = RedPacketConfig::count();
                if ($configs > 0) {
                    $data['open_conditions'] = true;
                } else {
                    $data['open_conditions'] = false;
                }
            } else {
                $data['open_conditions'] = false;
            }
        } else {
            $data['open_conditions'] = false;
        }
        if ($flag == true) {
            return $data;
        }
        return ResponeSuccess('查询成功', $data);
    }

    //轮播网址
    public function carousel()
    {
        $list['advs'] = CarouselAffiche::orderBy('sort', 'asc')->get();
        $list['urls'] = CarouselWebsite::pluck('url');
        $SystemSetting = SystemSetting::where('group', 'carousel')->where('key', 'times')->first();
        $list['website_times'] = $SystemSetting->value ?? 1;//相隔时长
        $SystemSetting_ads = SystemSetting::where('group', 'carousel')->where('key', 'ads')->first();
        $list['ads_times'] = $SystemSetting_ads->value ?? 1;//相隔时长
        foreach ($list['advs'] as $k => $v) {
            $list['advs'][$k]['image'] = !empty($v['image']) ? cdn($v['image']) : '';
        }
        return ResponeSuccess('查询成功', $list);
    }

    /*
     * 用户分享
     * */
    public function user_share()
    {
        $data['h5'] = SystemSetting::where('group', 'user_share_h5')->pluck('value', 'key');
        if (count($data['h5']) == 0) {
            $data['h5'] = [];
        } else {
            $data['h5']['friends_image'] = isset($data['h5']['friends_pictures']) ? cdn($data['h5']['friends_pictures']) : '';
            $data['h5']['wechat_image'] = isset($data['h5']['wechat_pictures']) ? cdn($data['h5']['wechat_pictures']) : '';
            $data['h5']['type'] = isset($data['h5']['friends_text']) ? 1 : '';
            $data['h5']['composite_friends_image'] = true ? '' : $this->getImage($data['h5']['friends_pictures']);
            $data['h5']['composite_wechat_image'] =  true ? '' : $this->getImage($data['h5']['wechat_pictures']);
        }
        $data['u3d'] = SystemSetting::where('group', 'user_share_u3d')->pluck('value', 'key');
        if (count($data['u3d']) == 0) {
            $data['u3d'] = [];
        } else {
            $data['u3d']['friends_image'] = isset($data['u3d']['friends_pictures']) ? cdn($data['u3d']['friends_pictures']) : '';
            $data['u3d']['wechat_image'] = isset($data['u3d']['wechat_pictures']) ? cdn($data['u3d']['wechat_pictures']) : '';
            $data['u3d']['type'] = isset($data['u3d']['friends_text']) ? 2 : '';
            $data['u3d']['composite_friends_image'] =  true ? '' : $this->getImage($data['u3d']['friends_pictures']);
            $data['u3d']['composite_wechat_image'] =  true ? '' : $this->getImage($data['u3d']['wechat_pictures']);
        }
        return ResponeSuccess('获取成功', $data);
    }

    /*
     * 用户检测
     * */
    public function user_check()
    {
        Validator::make(request()->all(), [
            'user_id' => ['required', 'numeric'],
        ], [
            'user_id.required' => '用户user_id必传!',
            'user_id.numeric' => '用户user_id必须是数字，请重新输入！',
        ])->validate();
        $user = AccountsInfo::with('agent')->where('UserID', request('user_id'))->first();
        if (!$user) {
            return ResponeFails('用户不存在');
        }
        if (empty($user['RegisterMobile'])) {
            return ResponeFails('用户暂未绑定手机号');
        }
        $res = PhonePayGive::where('Phonenum', $user['RegisterMobile'])->first();
        if ($res) {
            return ResponeSuccess('查询成功', true);
        } else {
            return ResponeFails('未查找到手机号', false);
        }
    }

    /*
     * 生成二维码
     * */
    private function getImage($address)
    {
        try {
            $user_id = request('user_id', '');
            if (!$user_id) {
                return '';
            }
            $path = storage_path() . '/app/public/user_qrcode/' . $user_id . '/';
            $uri = 'user_qrcode/' . $user_id . '/';
            $fileName = basename($address);
            $type = request('type', 'app');
            $url = getQrcodeUrl($type) . '?agentid=' . $user_id;
            $fileName = md5($url) . $fileName;
            if (File::exists($path . $fileName)) {
                return cdn($uri . $fileName);
                // return asset('storage/'.$uri.$fileName); // 分享上传没有走oss
            }
            $options = new QROptions([
                'version' => 7,//版本号
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,//错误级别
                'scale' => 1,//像素大小
                'imageBase64' => false,//是否将图像数据作为base64或raw来返回
            ]);
            $qimage = (new QRCode($options))->render($url);
            $image_2 = imagecreatefromstring($qimage);
            $img = Image::make('storage/' . $address);
            $image_p = imagecreatetruecolor(170, 170);
            imagecopyresampled($image_p, $image_2, 0, 0, 0, 0, 170, 170, imagesx($image_2), imagesy($image_2));
            $img->insert($image_p, 'bottom-right', 235, 180);//455, 642|15,325|
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            imagedestroy($image_p);
            $img->save($path . $fileName);
            list($bool, $info) = ossUploadFile($path . $fileName, $uri . $fileName);
            if (!$bool) {
                //return ResponeFails('图片OSS上传失败'.$info);
                return '';
            }
            return cdn($uri . $fileName);
            // return asset('storage/'.$uri.$fileName); // 分享上传没有走oss
        } catch (\Exception $e) {
            return '';
        }
    }

    /*
     * 返利活动的图片
     * */
    public function rebate_activity()
    {
        $data = [];
        $list = ActivityReturnConfig::from('ActivityReturnConfig as a')
            ->with('logs')
            ->select('a.*', 'b.start_time', 'b.end_time')
            ->leftJoin(Activity::tableName() . ' as b', 'a.activity_id', '=', 'b.id')
            ->whereNull('a.deleted_at')
            ->withTrashed()
            ->where('a.nullity', ActivityReturnConfig::NULLITY_ON)
            ->andFilterWhere('b.start_time', '<=', date('Y-m-d H:i:s'))
            ->get();
        foreach ($list as $k => $v) {
            $data[$k]['name'] = trim($v['name']);
            $data[$k]['activity_id'] = $v['activity_id'];
            $data[$k]['category'] = $v['category'];
            $data[$k]['img_address'] = $v['img_address'];
            $data[$k]['all_img_address'] = cdn($v['img_address']);
            $data[$k]['start_time'] = date('Y-m-d H:i:s', strtotime($v['start_time'])) ?? '';
            $data[$k]['end_time'] = date('Y-m-d H:i:s', strtotime($v['end_time'])) ?? '';
        }
        return ResponeSuccess('查询成功', $data);
    }

    public function getRegisterInfo()
    {
        $info = SystemStatusInfo::query()->whereIn('StatusName', array_keys(SystemStatusInfo::REGISTER_INFO))->pluck('StatusValue', 'StatusName');

        return ResponeSuccess('获取成功', [$info]);
    }

    /**
     * 答题配置
     * @param $user_id
     * @return array
     */
    public function answer_give_config($user_id)
    {
        $config = ActivitiesNormal::AnswerGiveConfig();
        $response = [];
        $user = AccountsInfo::query()->find($user_id);
        if (!$user) {
            return $response;
        }
        if (!ActivitiesNormal::isGave($user, $config)) {
            return $response;
        }
        return [
            'show_min' => $config['show_min'],
            'show_max' => $config['show_max'],
            'answer_correct' => $config['answer_correct']
        ];
    }

    /**
     * 答题活动提交
     */
    public function answer_give_submit()
    {
        Validator::make(request()->all(), [
            'user_id' => ['required', 'numeric'],
            'answer_correct' => ['required']
        ], [
            'user_id.required' => '用户ID必传',
            'user_id.numeric' => '用户ID必须是数字',
            'answer_correct.required' => '答案必填',
        ])->validate();
        $user_id = request('user_id');
        $user = AccountsInfo::where('UserID', $user_id)->first();
        if (!$user) {
            return ResponeFails('用户不存在');
        }
        $config = ActivitiesNormal::AnswerGiveConfig();
        if (!$config['answer_correct']) {
            return ResponeFails('活动未开启');
        }
        if (strpos(request('answer_correct'), $config['answer_correct']) === false) {
            return ResponeFails('很遗憾，正确网址为：' . $config['answer_correct']);
        }
        if (!ActivitiesNormal::isGave($user, $config)) {
            return ResponeFails('领取失败');
        }
        $GameScoreInfo = $user->GameScoreInfo;
        try {
            $min = $config['answer_min'] * 10;
            $max = $config['answer_max'] * 10;
            $score = rand($min, $max) * 1000;
            RecordTreasureSerial::beginTransaction([RecordTreasureSerial::connectionName(), UserAuditBetInfo::connectionName()]);
            $rs = RecordTreasureSerial::addRecord(
                $user_id, $GameScoreInfo->Score, $GameScoreInfo->InsureScore, $score,
                RecordTreasureSerial::ANSWER_GIVE_TYPE, 0, '答题活动礼金领取', '', $score
            );
            if (!$rs) {
                RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName(), UserAuditBetInfo::connectionName()]);
                return ResponeFails('领取失败');
            }
            try {
                UserAuditBetInfo::addScore($GameScoreInfo, $GameScoreInfo->Score, $score);
            } catch (\Exception $ex) {
                RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName(), UserAuditBetInfo::connectionName()]);
                \Log::error($user_id . "答题活动领取失败-{$ex}");
                return ResponeFails('领取失败');
            }
            $GameScoreInfo->Score += $score;
            $gs = $GameScoreInfo->save();
            if (!$gs) {
                RecordTreasureSerial::rollBack([RecordTreasureSerial::connectionName(), UserAuditBetInfo::connectionName()]);
                return ResponeFails('领取失败');
            }
            giveInform($user_id, $user->GameScoreInfo->Score, $score);
            RecordTreasureSerial::commit([RecordTreasureSerial::connectionName(), UserAuditBetInfo::connectionName()]);
            \Log::channel('gold_change')->info($user_id . '答题活动金币领取' . $score);
        } catch (\Exception $e) {
            \Log::error($user_id . "答题活动领取失败-{$e}");
            return ResponeFails('领取失败');
        }
        return ResponeSuccess('恭喜回答正确，获得礼金：' . realCoins($score) . '元');
    }

    //获取首充签到配置
    public function firstChargeSignInConfig($user_id)
    {
        $ActivitiesNormal = ActivitiesNormal::where('id', 1)->select('content', 'status')->first();
        //未配置
        if (empty($ActivitiesNormal)) {
            return [];
        }
        $content = json_decode($ActivitiesNormal->content, true);
        //循环处理金币值
        foreach ($content['detail'] as $k => $v) {
            $content['detail'][$k]['money'] = realCoins($v['money']);
            $content['detail'][$k]['score'] = realCoins($v['score']);
        }
        $sort_data = array_column($content['detail'], 'money');
        array_multisort($sort_data, SORT_ASC, $content['detail']);
        $data['content'] = $content;
        //无资格
        $FirstChargeSignInLog = FirstChargeSignInLog::where('user_id', $user_id)->first();
        if (!empty($FirstChargeSignInLog)) {
            //超过时间
            $day = (new \Illuminate\Support\Carbon())->diffInDays(date('Y-m-d', strtotime($FirstChargeSignInLog->created_at)), true);
            if (!isset($content['days']) || $day >= $content['days']) {
                return [];
            }
            //活动最低充值金额是否满足
            $min_money = min(array_column($content['detail'], 'money'));
            if ($FirstChargeSignInLog->score < intval($min_money * 10000)) {
                return [];
            }
            //是否领取
            $RecordTreasureSerial = RecordTreasureSerial::where('UserID', $user_id)->whereDate('CollectDate', date('Y-m-d'))
                ->where('TypeID', RecordTreasureSerial::FIRST_CHARGE_SIGNIN)->first();
            if (!empty($RecordTreasureSerial)) {
                $data['is_receive'] = 1;//已领取
            } else {
                $data['is_receive'] = 0;//未领取
            }
            return $data;

        } else {
            //未开启
            if ($ActivitiesNormal->status != 1) {
                return [];
            }
            //同一设备仅可以参与一次
            $RegisterMachine = AccountsInfo::where('UserID', $user_id)->value('RegisterMachine');
            $r_count = FirstChargeSignInLog::where('machine', $RegisterMachine)->count();
            if ($r_count > 0) {
                return [];
            }
            //是否参与
            $p_count = PaymentOrder::where('user_id', $user_id)->where('payment_status', PaymentOrder::SUCCESS)->count();
            if ($p_count > 0) {
                return [];
            }
            $data['is_receive'] = 2;//未参与
            return $data;
        }
    }

    public function activityConfig()
    {
        Validator::make(request()->all(), [
            'user_id' => ['nullable', 'integer'],
        ], [
            'user_id.numeric' => '用户ID必须是数字',
        ])->validate();
        $id = request('user_id');
        $type = request('type');
        $data = [];
        if ($type) {
            foreach ($this->activityFunction($id) as $key => $item) {
                if (in_array($key, $type)) {
                    if ($key == 'red_packet') {
                        $data += [
                            $key => $this->{$item}(true),
                        ];
                    }
                    if ($key == 'register') {
                        $data += [
                            $key => $this->{$item}(),
                        ];
                    }
                    $data += [
                        $key => $this->{$item}($id),
                    ];
                }
            }
        } else {
            $data = [
                'red_packet' => $this->red_packet_config(true),
                'rotary' => $this->rotary_config($id),
                'register' => $this->registerActivity()
            ];
            if ($id) {
                $data['answer'] = $this->answer_give_config($id);
                $data['first_charge'] = $this->firstChargeSignInConfig($id);
                $data['vip'] = $this->vipConfig($id);
            }
        }

        return ResponeSuccess('获取成功', $data);
    }

    protected function registerActivity($client = 2)
    {
        try {
            $res = RegisterGive::where('platform_type', $client)->where('give_type', 2)->select('score_count AS min_score', 'score_max AS max_score')->first();
            if ($res) {
                return [
                    'min_score' => realCoins($res->min_score),
                    'max_score' => realCoins($res->max_score),
                ];
            }
            return [];
        } catch (Exception $e) {
            \Log::error('客户端接口 [activityConfig] ' . $e);
            return [];
        }
    }

    public function getRotaryRecordList()
    {
        $data = [];
        $type = request('rotary_type');
        $lists = UserRotaryRecords::query()->with('Accounts:UserID,GameID')
            ->where('user_id', request()->user_id)
            ->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])
            ->whereHas('ActivitiesNormal', function ($query) use ($type) {
                $query->where('pid', $type);
            })
            ->get();
        foreach ($lists as $item) {
            $data[] = [
                'game_id' => '***' . substr(optional($item->Accounts)->GameID ?? null, -2),
                'score' => realCoins($item->reward_score),
                'rank_name' => ActivitiesNormal::RANK_LIST[$item->rank_type],
                'time' => Carbon::parse($item->created_at)->format('H:i')
            ];
        }
        return ResponeSuccess('获取成功', $data);
    }

    /**
     * VIP配置
     * @param $user_id
     * @return array
     */
    private function vipConfig($user_id)
    {
        $account = AccountsInfo::query()->find($user_id);
        if (!$account) {
            return [
                'vip_info' => '',
                'vip_list' => ''
            ];
        }
        $vip_info = MembersHandsel::getVipDiff($account);
        $list = MembersHandsel::getVipConfig($account);
        return [
            'vip_info' => $vip_info,
            'vip_list' => $list
        ];
    }

    public function activityFunction($id)
    {
        $res = [
            'red_packet' => 'red_packet_config',
            'rotary' => "rotary_config",
            'register' => 'registerActivity',
        ];
        if ($id) {
            $res['answer'] = "answer_give_config";
            $res['first_charge'] = "firstChargeSignInConfig";
            $res['vip'] = "vipConfig";
        }
        return $res;
    }

    /**
     * 彩金领取
     */
    public function handselGive(WithdrawalOrdersRequest $request)
    {
        if (!$request->HandselID) {
            return ResponeFails('礼金领取失败');
        }
        $account = AccountsInfo::query()->with('GameScoreInfo')->find($request->user_id);
        $Handsel = MembersHandsel::query()->where('HandselID', $request->HandselID)->first();
        if (!$Handsel) {
            return ResponeFails('礼金领取失败');
        }
        $MembersInfo = MembersInfo::find($Handsel->MembersID);
        if ($MembersInfo->MemberOrder > $account->MemberOrder) {
            return ResponeFails('礼金领取失败');
        }
        try {
            $res = DB::connection(AccountsInfo::connectionName())
                ->select('exec GSP_GR_GetVIPPrize ?,?,?,?,?', [$account->UserID, getIp(), $Handsel->HandselType, $MembersInfo->MemberOrder, null]);
        } catch (\Exception $e) {
            \Log::channel('system_error')->error('彩金领取失败', ['HandselID' => $request->HandselID, 'Exception' => $e]);
            return ResponeFails('礼金领取失败');
        }
        $result = (array)$res[0];
        if (!$result['Status'] ?? 0) {
            \Log::channel('account_score')->warning('彩金领取失败', ['UserID' => $account->UserID]);
            return ResponeFails('礼金领取失败');
        }
        \Log::channel('gold_change')->info($account->UserID . 'vip彩金领取' . $account->GameScoreInfo->Score);
        $result['AllReward'] = realCoins($result['AllReward'] ?? 0);
        return ResponeSuccess('领取成功', $result);
    }

    /**
     * 回血返利列表
     */
    public function returnRebateList(WithdrawalOrdersRequest $request)
    {
        $list = ReturnRecord::query()
            ->selectRaw('id,user_id,score,abs(win_score) as loss_score')
            ->where('user_id', $request->user_id)
            ->orderBy('reward_time', 'desc')
            ->paginate(10);
        return ResponeSuccess('获取成功', ReturnRebateTransformer::collection($list));
    }

    /**
     * 近日返利排行榜
     */
    public function returnRebateRank()
    {
        $list = ActivieAi::query()
            ->select('NickName', 'JettonScore')
            ->orderBy('DateID', 'desc')
            ->limit(10)
            ->get()
            ->sortByDesc('JettonScore')
            ->map(function ($model) {
                $model->JettonScore = realCoins($model->JettonScore);
                return $model;
            })->toArray();
        return ResponeSuccess('获取成功', array_values($list));
    }
}
