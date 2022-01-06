<?php

namespace App\Traits;

use App\Exceptions\HttpErrorException;
use Models\Accounts\CheckCode;
use App\Rules\IsMobile;
use App\Rules\VerifyCodeExist;
use Illuminate\Support\Facades\Log;
use Models\AdminPlatform\SmsLog;
use PhpSms;
use PHPUnit\Exception;
use Validator;
use Illuminate\Http\Request;
use Carbon\Carbon;

trait Sms
{
    /*
    |--------------------------------------------------------------------------
    | Sms trait
    |--------------------------------------------------------------------------
    | 短信发送 代码片段
    |
    */
    public function code(Request $request)
    {
        Validator::make($request->all(), [
            'mobile' => ['required', 'numeric', new IsMobile(), new VerifyCodeExist()],
        ], [
            'mobile.required' => '手机号码必须填写'
        ])->validate();
        try {
            $tempData = [
                'code' => $this->generateCode(4),
                'minutes' => 5
            ];
            $model = CheckCode::where('PhoneNum', $request->input('mobile'))->orderBy('CollectTime', 'DESC')->first();
            if (!$model) {
                $model = new CheckCode();
                $model->PhoneNum = $request->input('mobile');
            }
            $model->CheckCode = $tempData['code'];
            $model->CollectTime = Carbon::now()->addMinutes(5);

            //SMS_163847427 SMS_163057706
            $content = sprintf('【金鸿网络】亲爱的用户，您的验证码是%s。有效期为%s分钟，请尽快验证', $tempData['code'], $tempData['minutes']);
//        $result = PhpSms::make('Aliyun', 'SMS_163057706')->to($request->input('mobile'))->data($tempData)->send();
            $result = PhpSms::make('YunPian')->content($content)->to($request->input('mobile'))->data($tempData)->send();

            if ($result['success']) {
                if ($model->save()) {
                    SmsLog::addLogs($request->input('mobile'), $result['success'], $result, SmsLog::TYPE_CODE);
                    return ResponeSuccess('发送成功', ['limit' => VerifyCodeExist::LIMIT]);
                }
            } else {
                SmsLog::addLogs($request->input('mobile'), $result['success'], $result, SmsLog::TYPE_CODE);
                Log::error('短信发送失败', $result);
                return ResponeFails('短信发送失败,请求过于频繁');
            }
        } catch (Exception $e) {
            Log::error('发送短信：'.$e);
            return ResponeFails('短信发送失败');
        }
    }

    private function generateCode($length = null, $characters = null)
    {
        $characters   = (string)($characters ?: '0123456789');
        $charLength   = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charLength - 1)];
        }
        return $randomString;
    }

}
