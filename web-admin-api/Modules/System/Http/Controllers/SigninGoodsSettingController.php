<?php
/*签到物品配置*/
namespace Modules\System\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\SystemSetting;
use Models\Platform\GamePackageGoods;
use Modules\System\Http\Requests\SigninPackageGoodsRequest;
use Transformers\GamePackageGoodsTransformer;
use Validator;

class SigninGoodsSettingController extends Controller
{
    /**
     * 签到物品配置列表
     *
     */
    public function getList()
    {
        $list = GamePackageGoods::get();
        return $this->response->collection($list,new GamePackageGoodsTransformer());
    }

    /**
     * 签到物品配置添加
     *
     */
    public function add(SigninPackageGoodsRequest $request)
    {
        $res = GamePackageGoods::saveOne();
        if (!$res){
            return ResponeFails('添加失败');
        }
        signinInform();
        return ResponeSuccess('添加成功');
    }

    /**
     * 签到物品配置修改
     *
     */
    public function edit(SigninPackageGoodsRequest $request, $goods_id)
    {
        $res = GamePackageGoods::saveOne($goods_id);
        if (!$res){
            return ResponeFails('修改失败');
        }
        signinInform();
        return ResponeSuccess('修改成功');
    }

    /**
     * 签到物品配置删除
     *
     */
    public function del($goods_id)
    {
        $res = GamePackageGoods::where('GoodsID',$goods_id)->delete();
        if (!$res){
            return ResponeFails('删除失败');
        }
        signinInform();
        return ResponeSuccess('删除成功');
    }
    /*
    * 活动管理-签到管理-用户分享配置列表
    * @return data
    * */
     public function user_share_show()
     {
         $data['h5'] = SystemSetting::where('group', 'user_share_h5')->pluck('value', 'key');
         if(count($data['h5'])==0){
            $data['h5']=[];
         }else{
             $data['h5']['friends_image'] = isset($data['h5']['friends_pictures']) ? asset('storage/' . $data['h5']['friends_pictures']) : '';
             $data['h5']['wechat_image'] = isset($data['h5']['wechat_pictures']) ? asset('storage/' . $data['h5']['wechat_pictures']) : '';
             $data['h5']['type']=isset($data['h5']['friends_text']) ? 1: '';
         }
         $data['u3d'] = SystemSetting::where('group', 'user_share_u3d')->pluck('value', 'key');
         if(count($data['u3d'])==0){
             $data['u3d']=[];
         }else {
             $data['u3d']['friends_image'] = isset($data['u3d']['friends_pictures']) ? asset('storage/' . $data['u3d']['friends_pictures']) : '';
             $data['u3d']['wechat_image'] = isset($data['u3d']['wechat_pictures']) ? asset('storage/' . $data['u3d']['wechat_pictures']) : '';
             $data['u3d']['type']=isset($data['u3d']['friends_text']) ? 2: '';
         }
         return ResponeSuccess('获取成功', $data);
     }

    /*
     * 活动管理-签到管理-用户分享设置
     * @return Response
     *
     * */
    public function user_share_config(Request $request)
    {
        Validator::make($request->all(), [
            'friends_text'     => ['required','max:30'],
            'wechat_text'      => ['required','max:30'],
            'image'            => ['image'],
            'friends_pictures' => ['required'],
            'wechat_pictures'  => ['required'],
            'platform_type'    => ['required','in:1,2'],
            'type'             => ['required','in:0,1']
        ], [
            'friends_text.required'      => '朋友圈软文必填',
            'wechat_text.required'       => '微信好友消息必填',
            'friends_text.max'           => '朋友圈软文不能大于30个字',
            'wechat_text.max'            => '微信好友消息不能大于30个字',
            'image.image'                => '图片类型不正确',
            'friends_pictures.required'  => '朋友圈图片必传',
            'wechat_pictures.required'   => '微信好友图片必传',
            'platform_type.in'           => '平台类型不在可选范围',
        ])->validate();
        $model = new SystemSetting();
        $platform_type=$request->input('platform_type');/*平台类型（1.H5,2.U3D）*/
        $type=$request->input('type'); // 0 新增,1 编辑
        $info = [['key' => 'friends_text','value' => $request->input('friends_text')],
                ['key'  => 'wechat_text','value' => $request->input('wechat_text')],
                ['key'  => 'friends_pictures','value' => $request->input('friends_pictures')],
                ['key'  => 'wechat_pictures','value' => $request->input('wechat_pictures')]];
        if($platform_type && $platform_type==1)
        {
            if($type==0){
                $exit_h5=SystemSetting::where('group','user_share_h5')->get();
                $count=count($exit_h5);
                if($count>0){ //有一种类型
                    return ResponeFails('已有H5平台的分享设置,操作失败');
                }
            }
            $res = $model->edit('user_share_h5', $info);
        }else{
            if($type==0) {
                $exit_u3d = SystemSetting::where('group', 'user_share_u3d')->get();
                $count = count($exit_u3d);
                if ($count > 0) { //有一种类型
                    return ResponeFails('已有U3D平台的分享设置,操作失败');
                }
            }
            $res = $model->edit('user_share_u3d', $info);
        }
        if ($res) {
            return ResponeSuccess('添加成功');
        } else {
            return ResponeFails('添加失败');
        }
    }
    /*
     * 活动管理-签到管理-用户分享图片上传
     * */
    public function user_share_upload(Request $request)
    {
        Validator::make($request->all(), [
            'image' => ['required', 'file', 'image'],// ,'dimensions:width=640,height=1136'
        ], [
            'image.required' => '图片必传',
            'image.image'    => '图片类型不正确',
        ])->validate();
        if ($request->file('image')->isValid()) {
            $path = $request->image->store('share_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'share_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
            return ResponeSuccess('上传成功', ['path' => $path, 'all_path' => asset('storage/' . $path)]);
        }
        return ResponeFails('上传失败');
    }
}
