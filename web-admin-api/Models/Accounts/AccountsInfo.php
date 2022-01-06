<?php
/*用户信息表*/
namespace Models\Accounts;
/**
 * UserID			    int			    用户的唯一标识号码，注册的时候自动生成，用于其他表的关联字段，不能修改
 * GameID			    int			    游戏ID，注册的时候自动生成，用于其他表的关联字段，请通过正确的方式修改
 * SpreaderID		    int			    推广员标识，若推广员标识有效则表明当前玩家用户是这个推广员用户的下线。该字段与UserID关联。推广员的使用由具体运营商确定。不填则为0
 * Accounts		        nvarchar(31)	账号名字，具有唯一性，不能重复
 * NickName		        nvarchar(31)	用户昵称。平台上显示的名字。具有唯一性，不能重复
 * RegAccounts		    nvarchar(31)	用户注册的原始名字，默认与账号名字相同，方便运营商跟踪管理账号名字改变过的用户
 * UnderWrite		    nvarchar(250)	用户个性签名
 * PassPortID		    nvarchar(18)	用户注册的身份证号码
 * Compellation		    nvarchar(16)	用户注册的真实姓名
 * LogonPass		    nchar(32)		用户账号的登录密码，采用通用加密算法 MD5 加密记录（32位密文）
 * InsurePass		    nchar(32)		用户保险箱登录密码，采用通用加密算法 MD5 加密记录（32位密文）
 * DynamicPass		    nchar(32)		动态密码。用于确保玩家在进入房间，使用保险箱，修改密码等操作的时候，确定是同一个用户所为
 * DynamicPassTime		datetime		动态密码更新时间
 * FaceID			    smallint		用户图像ID，该ID对于系统ID。并非自定义图像ID
 * CustomID		        int			    自定义图像ID。默认为0时用户读取系统图像。否则读取用户自定义头像表的头像
 * UserRight		    int			    用户权限标志，参数意义需要参考所使用系统的权限参数对照表，请参考文档最后的“权限参数对照表”
 * MasterRight		    int			    管理员权限标志，参数意义请参考所使用系统的权限参数对照表，请参考文档最后的“权限参数对照表”
 * ServiceRight		    int			    服务权限标志，一般网站上使用权限由具体运营商确定
 * MasterOrder		    tinyint			管理等级标识。普通用户默认为0
 * MemberOrder		    tinyint			会员等级标识。默认为0。0普通玩家，1：蓝钻玩家，2：黄钻玩家，3：白钻玩家，4：红钻玩家，5：VIP钻玩家
 * MemberOverDate		datetime		会员到期时间
 * MemberSwitchDate	    datetime		切会员切换时间（如果用户同时拥有多个会员的话，该时间为最高等级的会员到期的时间。如果低等级会员时间足够长，过了这时间后会自动切换低等级的会员）
 * CustomFaceVer		tinyint			用户自定义图像版本号。默认为0。如果非0则代表用户使用的自定义图像
 * Gender			    tinyint			用户性别。保密为0，男为1，女为2
 * Nullity			    tinyint			账号禁用标识                状态：0正常、1禁止
 * NullityOverDate		datetime		账号解禁时间
 * StunDown		        tinyint			账号安全关闭标识，用户安全保护相关（保留扩展用字段）由具体运营商确定如何使用
 * MoorMachine		    tinyint			固定机器，用户安全保护相关，运营商可以在网站上提供该项服务，该项服务启用后，根据“MachineSerial（机器码序列）”限制用户登录行为
 * IsAndroid		    tinyint			机器人标识。0为用户，1为机器人
 * WebLogonTimes		int			    登录次数（网站）
 * GameLogonTimes		int			    登录次数（游戏）
 * PlayTimeCount		int			    用户游戏时间（由各个游戏累计）
 * OnLineTimeCount		int			    用户在线时间（由各个游戏累计）
 * LastLogonIP		    nvarchar(15)	用户最后登录的IP地址
 * LastLogonDate		datetime		用户最后登录的时间
 * LastLogonMobile		nvarchar(11)	手机客户端用户，用户登陆时读取到的用户手机号码
 * LastLogonMachine	    nvarchar(32)	用户最后登录电脑的机器码。用户绑定机器的机器码也是这个
 * RegisterIP		    nvarchar(15)	用户账号的注册所在的 IP 地址
 * RegisterDate		    datetime		用户注册时间
 * RegisterMobile		nvarchar(11)	手机客户端用户，用户注册时所读取到的用户手机号码
 * RegisterMachine		nvarchar(32)	用户注册使用电脑的机器码
 * RegisterOrigin		tinyint
 * ClientType		    tinyint			注册来源：1、android，2、ios，3、pc
 * PlatformID		    smallint        平台类型：1、H5，2、U3D，3、LUA
 * LogonMode		    smallint        来源：1、游客登录，2、微信登录，3、手机登录
 * UserUin			    nvarchar(32)    第三方登录记录的第三方标识
 * RankID			    int             扩展字段，暂时未使用
 * AgentID			    int             代理标识
 * PlaceName		    nvarchar(33)	地名
 *
 */
use Models\AdminPlatform\AccountsSet;
use Models\AdminPlatform\PaymentOrder;
use Models\AdminPlatform\WithdrawalOrder;
use Models\Agent\AgentRelation;
use Models\Agent\ChannelUserRelation;
use Models\Record\RecordTreasureSerial;
use Models\Record\RecordUserLogon;
use Models\Treasure\GameScoreInfo;
use Models\Treasure\RecordGameScore;
use Models\Treasure\RecordScoreDaily;
use Models\Treasure\UserAuditBetInfo;

class AccountsInfo extends Base
{
    protected $table            = 'AccountsInfo';
    protected $primaryKey       = 'UserID';
    protected $hidden           = ['LogonPass','DynamicPass'];
    const CLIENT_TYPE_ANDROID = 1;
    const CLIENT_TYPE_IOS     = 2;
    const CLIENT_TYPE_PC      = 3;
    const CLIENT_TYPE         = [
        self::CLIENT_TYPE_ANDROID => 'android',
        self::CLIENT_TYPE_IOS => 'ios',
        self::CLIENT_TYPE_PC => 'h5',
    ];
    const LOGON_MODE_VISITORS = 1;
    const LOGON_MODE_WECHAT   = 2;
    const LOGON_MODE_MOBILE   = 3;
    const LOGON_MODE          = [
        self::LOGON_MODE_VISITORS => '游客登录',
        self::LOGON_MODE_WECHAT => '微信登录',
        self::LOGON_MODE_MOBILE => '手机登录',
    ];
    const PLATFORM_ID_H5 = 1;
    const PLATFORM_ID_U3D   = 2;
    const PLATFORM_WEP_APP   = 4;
    //const PLATFORM_ID_LUA   = 3;
    const PLATFORM_ID          = [
        self::PLATFORM_ID_H5 => 'CCC',
        self::PLATFORM_ID_U3D => 'U3D',
        self::PLATFORM_WEP_APP => 'web-app',
        //self::PLATFORM_ID_LUA => 'LUA',
    ];

    /*注册来源*/
    public function getClientTypeTextAttribute()
    {
        return self::CLIENT_TYPE[$this->ClientType] ?? '';
    }

    /*来源*/
    public function getLogonModeTextAttribute()
    {
        return self::LOGON_MODE[$this->LogonMode] ?? '';
    }

    /*所用引擎*/
    public function getPlatformIDTextAttribute()
    {
        return self::PLATFORM_ID[$this->PlatformID] ?? '';
    }

    /*用户--设置关联*/
    public function accountset()
    {
        return $this->belongsTo(AccountsSet::class,'UserID','user_id');
    }

    /*用户--金币信息关联*/
    public function GameScoreInfo()
    {
        return $this->belongsTo(GameScoreInfo::class,'UserID','UserID');
    }

    /*用户--稽核打码量关联*/
    public function AuditBetInfo()
    {
        return $this->belongsTo(UserAuditBetInfo::class,'UserID','UserID');
    }

    /*用户--代理关联(中间表)*/
    public function agent()
    {
        return $this->belongsTo(AgentRelation::class,'UserID','user_id');
    }

    /*用户--渠道关联(中间表)*/
    public function channel()
    {
        return $this->belongsTo(ChannelUserRelation::class,'UserID','user_id');
    }

    //用户-登录时间关联
    public function  userLogin()
    {
        return $this->hasMany(RecordUserLogon::class,'UserID','UserID');
    }
    /**
     * 通过用户GameID查询UserID
     * @param   int     $game_id   玩家注册id，排除机器人
     */
    public function getUserId($game_id)
    {
        $model = $this->where('GameID',$game_id)->where('IsAndroid',0)->first();
        return $model->UserID ?? 0;
    }

    /*关联充值*/
    public function payment()
    {
        return $this->hasMany(PaymentOrder::class, 'user_id','UserID');
    }

    public function withdraw()
    {
        return $this->hasMany(WithdrawalOrder::class, 'user_id','UserID');
    }

    /*关联游戏记录日流水表*/
    public function dayWater()
    {
        return $this->hasMany(RecordScoreDaily::class,'UserID','UserID');
    }

    /**
     * 关联游戏记录
     */
    public function recordgamesocre()
    {
        return $this->hasMany(RecordGameScore::class,'UserID','UserID');
    }

    public static function addExp($user_id,$amount,$isNotify = true){
        $user = self::find($user_id);
        if($user){
	        $max_level = MembersInfo::where('Status',1)->orderBy('MemberOrder','desc')->first(); //查询最大等级
	        if($user->MemberOrder>=$max_level->MemberOrder && $user->vip_exp >= $max_level->LowerLimit){//已经达到最高等级
		        //只加经验，不改变等级
                $user->MemberOrder = $max_level->MemberOrder;
	        	$user->vip_exp += $amount;
		        $user->save();
		        //等级通知
                $isNotify ? notify_user_vip($user_id, $user->vip_exp, $user->MemberOrder) : '';
		        return;
	        }
	        //没有达到最高等级
	        $user->vip_exp += $amount;
	        if($user->vip_exp > $max_level->LowerLimit){
	        	//处理充值超出最大等级
		        $user->MemberOrder=$max_level->MemberOrder;
		        MembersUpgrade::updateOrCreate([
			        'UserID'      => $user_id,
			        'MemberOrder' => $max_level->MemberOrder,
		        ],[
                    'CreatedTime' => date('Y-m-d H:i:s')
                ]);
                MembersUpgrade::where( 'UserID',$user_id)->where('MemberOrder','>',$max_level->MemberOrder)->delete();
	        }else{
	        	//处理正常等级升级
		        $member = MembersInfo::where('Status',1)->where('LowerLimit','<=',$user->vip_exp)->orderBy('MemberOrder','desc')->first();
		        if($member){
			        if($user->MemberOrder != $member->MemberOrder){
				        $user->MemberOrder = $member->MemberOrder;
                        MembersUpgrade::updateOrCreate([
                            'UserID'      => $user_id,
                            'MemberOrder' => $member->MemberOrder,
                        ],[
                            'CreatedTime' => date('Y-m-d H:i:s')
                        ]);
                        MembersUpgrade::where( 'UserID',$user_id)->where('MemberOrder','>',$member->MemberOrder)->delete();
			        }
		        }
	        }
            $user->save();
	        //等级变化通知
            $isNotify ? notify_user_vip($user_id, $user->vip_exp, $user->MemberOrder) : '';
        }
    }

    //加金币
    public static function addCoins($user,$score,$type){
        $GameScoreInfo = $user->GameScoreInfo;
        $text = RecordTreasureSerial::TYPEID[$type];
        try {
            $GameScoreInfo->Score += $score;
            $gs = $GameScoreInfo->save();
            if (!$gs) {
                return false;
            }
            giveInform($user->UserID, $user->GameScoreInfo->Score, $score);
            \Log::channel('gold_change')->info($user->UserID . $text.'金币领取' . $score);
        } catch (\Exception $e) {
            \Log::error($user->UserID . $text."领取失败-{$e}");
            return false;
        }
        return true;
    }
    //加金币不通知
    public static function addCoinsNoNotice($user,$score,$type){
        $GameScoreInfo = $user->GameScoreInfo;
        $text = RecordTreasureSerial::TYPEID[$type];
        try {
            $GameScoreInfo->Score += $score;
            $gs = $GameScoreInfo->save();
            if (!$gs) {
                return false;
            }
            \Log::channel('gold_change')->info($user->UserID . $text.'金币领取' . $score);
        } catch (\Exception $e) {
            \Log::error($user->UserID . $text."领取失败-{$e}");
            return false;
        }
        return true;
    }

    //加流水
    public static function addRecords($user,$score,$type){
        $GameScoreInfo = $user->GameScoreInfo;
        $text = RecordTreasureSerial::TYPEID[$type];
        $rs = RecordTreasureSerial::addRecord(
            $user->UserID, $GameScoreInfo->Score, $GameScoreInfo->InsureScore, $score,
            $type, 0, $text.'领取', '', $score
        );
        if (!$rs) {
            return false;
        }
        return true;
    }

    //加稽核
    public static function addAuditBets($user,$score,$type){
        $GameScoreInfo = $user->GameScoreInfo;
        $text = RecordTreasureSerial::TYPEID[$type];
        try {
            UserAuditBetInfo::addScore($GameScoreInfo, $GameScoreInfo->Score, $score);
        } catch (\Exception $ex) {
            \Log::error($user->UserID . $text."稽核插入失败-{$ex}");
            return false;
        }
        return true;
    }

}
