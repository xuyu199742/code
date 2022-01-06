<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Models\AdminPlatform\AdminUser;
use Models\AdminPlatform\SystemLog;
use ParagonIE\ConstantTime\Base32;
use Google2FA;
use Validator;

class Google2FAController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:' . $this->guard);
    }

    public function enableTwoFactor()
    {
        $user = $this->user();
        if (env('GOOLGE_LOGIN',false) === false){
            return ResponeSuccess('操作成功', ['has_bind' => true]);
        }
        if ($user->google2fa_secret) {
            return ResponeSuccess('操作成功', ['has_bind' => true]);
        }
        /* else {
             $secret                 = $this->generateSecret();
             $user->google2fa_secret = Crypt::encrypt($secret);
             $user->save();
         }*/
        $secret                 = $this->generateSecret();
        //$user->google2fa_secret = Crypt::encrypt($secret);
        $imageDataUri = Google2FA::getQRCodeInline(
            env('APP_NAME').':'.date('Y-m-d H:i:s'),
            $user->mobile,
            $secret,
            200
        );
        return ResponeSuccess('操作成功', ['has_bind' => false, 'image' => $imageDataUri, 'secret' => $secret]);
        //`return view('auth::2fa/enableTwoFactor', ['image' => $imageDataUri, 'secret' => $secret]);
    }

    public function disableTwoFactor($id)
    {
        $user = AdminUser::find($id);
        if ($user) {
            $user->google2fa_secret = null;
            if ($user->save()) {
                @SystemLog::addLogs('重置了' . $user->username . '验证器');
                return ResponeSuccess('重置成功');
            }
        }
        return ResponeFails('重置失败');
    }


    private function generateSecret()
    {
        $randomBytes = random_bytes(10);
        return Base32::encodeUpper($randomBytes);
    }

    public function postValidateToken()
    {
        $user = $this->user();
        if ($user->google2fa_secret) {
            return ResponeFails('已绑定验证器');
        }
        Validator::make(request()->all(), [
            'token' => ['required'],
            'secret'=> ['required'],
        ], [
            'token.required' => '验证码不能为空',
            'secret.required' => '私钥不能为空',
        ])->validate();
        $secret=request('secret');
        if (Google2FA::verifyKey($secret, request('token'))) {
            $user->google2fa_secret = Crypt::encrypt($secret);
            if($user->save()){
                return ResponeSuccess('绑定成功');
            }
            return ResponeFails('绑定失败');
        }
        return ResponeFails('验证失败');
    }

}
