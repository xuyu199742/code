<?php
/* Vip商人*/

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Models\Accounts\AccountsInfo;
use Models\AdminPlatform\AdminUser;
use Models\AdminPlatform\VipBusinessman;
use Transformers\VipBusinessmanTransformer;
use Validator;

class VipBusinessmanController extends Controller
{
    /*
     * vip商人列表
     * */
    public function vip_trader_list()
    {
        $list = VipBusinessman::orderBy('sort_id', 'asc')->orderBy('gold_coins', 'desc')->paginate(config('page.list_rows'));
        return $this->response->paginator($list, new VipBusinessmanTransformer());

    }

    /*
     *  后台管理员列表
     * */
    public function admin_users_list()
    {
        $list = AdminUser::get();
        return ResponeSuccess('请求成功', $list);
    }

    /*
     * vip商人保存
     *
     * */
    public function vip_trader_save(Request $request)
    {
        Validator::make($request->all(), [
            'gold_coins'          => ['required', 'numeric','min:1'],
            'image'               => ['required'],
            'avatar'              => ['required_without:id'],
            'sort_id'             => ['numeric','min:1'],
            'contact_information' => ['required'],
            'type'                => ['required', 'in:1,2'],
            'admin_id'            => ['required','numeric'],
            'game_id'             => ['required','numeric'],
            'nickname'            => ['required'],
        ], [
            'gold_coins.required'          => '商人金币数必填',
            'gold_coins.numeric'           => '商人金币数必须是数字',
            'gold_coins.min'               => '商人金币数必须大于0',
            'image.required'               => '图片不能空',
            'avatar.required_without'      => '图片必传',
            'sort_id.numeric'              => '排序值必须是数字',
            'sort_id.min'                  => '排序值必须大于0',
            'contact_information.required' => '联系方式必填',
            'type.required'                => '联系方式类型必传',
            'type.in'                      => '联系方式类型不在可选范围',
            'admin_id.required'            => '后台管理员id必传',
            'nickname.required'            => 'vip商人昵称必填',
            'game_id.required'             => '游戏id必填',
        ])->validate();
        $account_info = AccountsInfo::where('GameID', $request->input('game_id'))->first();
        if (!$account_info) {
            return ResponeFails('该玩家不存在');
        }
        if ($request->input('id')) {
            $model = VipBusinessman::find($request->input('id'));
            if (!$model) {
                return ResponeFails('转盘奖励配置不存在');
            }
        } else {
            $model = new VipBusinessman();
        }
        $model->admin_id            = $request->input('admin_id');
        $model->gold_coins          = moneyToCoins($request->input('gold_coins'));
        $model->sort_id             = $request->input('sort_id', 0);
        $model->avatar              = $request->input('avatar');
        $model->contact_information = $request->input('contact_information');
        $model->type                = $request->input('type');
       // $model->nullity             = VipBusinessman::NULLITY_ON;
        $model->nickname            = $request->input('nickname');
        $model->user_id             = $account_info->UserID;
        if ($model->save()) {
            return ResponeSuccess('保存成功');
        }
        return ResponeFails('保存失败');
    }

    /*
    * 图片上传
    *
    * */

    public function upload(Request $request)
    {
        Validator::make($request->all(), [
            'image' => ['required', 'file', 'image','max:500'],
        ], [
            'image.required' => '图片必传',
            'image.image'    => '图片类型不正确',
            'image.max'      => '图片不超过500K',
        ])->validate();
        if ($request->file('image')->isValid()) {
            $path = $request->image->store('vip_trader_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'vip_trader_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
            return ResponeSuccess('上传成功', ['path' => $path, 'all_path' => asset('storage/' . $path)]);
        }
        return ResponeFails('上传失败');
    }

    /*
    * vip商人禁用
    *
    * */
    public function vip_trader_status()
    {
        $id             = request('id');
        $status         = request('status') ? 0 : 1;
        $model          = VipBusinessman::find($id);
        $model->Nullity = $status;
        \DB::beginTransaction();
        try {
            if ($model->save()) {
                \DB::commit();
                return ResponeSuccess('操作成功');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return $this->response->errorInternal('操作失败');
        }
    }

}
