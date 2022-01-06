<?php
/* 新闻公告 */

namespace Modules\News\Http\Controllers;

use App\Exceptions\NewException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Matrix\Exception;
use Models\AdminPlatform\Ads;
use Models\AdminPlatform\SystemNotice;
use Models\AdminPlatform\SystemSetting;
use Models\Platform\GameKindItem;
use Models\Platform\SystemMessage;
use Models\AdminPlatform\Dict;
use Modules\News\Http\Requests\SystemMessageRequest;
use Transformers\AdsTransformer;
use Transformers\SystemNoticeTransformer;
use Transformers\SystemMessageTransformer;
use Validator;

class NewsBulletinController extends Controller
{
    /**
     * 新闻公告列表
     *
     * @return Response
     */
    public function system_notice_list()
    {
        $list = SystemNotice::select('NoticeID', 'NoticeTitle', 'SortID', 'PublisherTime', 'PlatformType', 'remark')
            //->orderBy('IsTop','desc')
            //->orderBy('IsHot','desc')
            ->orderBy('SortID', 'asc')
            ->orderBy('PublisherTime', 'desc')
            ->paginate(config('page.list_rows'));
        foreach ($list as $key => &$value) {
            $value['PublisherTime'] = substr($value['PublisherTime'], 0, 10);
        }
        return $this->response->paginator($list, new SystemNoticeTransformer());
    }
    // 获取背景色
    public function getBgColor()
    {
        try {
            $bgColor = Dict::where('id', 10)->select('extend')->first();
            return ResponeSuccess('Success',$bgColor['extend']);
        } catch(Exception $e) {
            return ResponeFails('操作有误');
        }
    }

    /**
     * 获取单个公告详情
     *
     */
    public function getNoticeDetail($id)
    {
        try {
            $data = SystemNotice::GetDetail($id);
            return ResponeSuccess('Success',$data);
        } catch (Exception $e){
            return ResponeFails('操作有误');
        }
    }
	/**
	 * 新闻公告新增
	 *
	 * @return Response
	 */
	public function system_notice_add(Request $request)
	{
		Validator::make($request->all(), [
			'NoticeTitle'   => ['required'],
            'PlatformType'  => ['in:0,1,2'],
            'PublisherTime'     => ['required'],
			'SortID'        => ['required', 'numeric', 'min:1'],
            'is_img'        => 'required|in:0,1',
            'img'           => ['nullable'],
            'content'       => ['nullable'],
            'remark'        => ['nullable']
		], [
			'NoticeTitle.required'   => '公告名称必填',
			//'MoblieContent.required' => '手机内容必填',
			'SortID.required'        => '排序号必填',
			'SortID.numeric'         => '排序值必须是数字',
			'SortID.min'             => '排序值必须大于0',
			'PublisherTime.required'     => '发布时间必填',
            'PlatformType.in'        => '类型不在可选范围',
            'is_img.required'        => '参数不正确',
            'is_img.in'        => '内容不在可选范围',
		])->validate();
        $content = $request->input('content','?') ?? '';
        $data['NoticeTitle'] = $request->input('NoticeTitle');
        $data['SortID'] = $request->input('SortID');
        $data['PublisherTime'] = $request->input('PublisherTime');
        $data['PlatformType'] = $request->input('PlatformType');
        $data['is_img'] = $request->input('is_img');
        $data['img'] = $request->input('img') ?? '';
        $data['content'] = htmlspecialchars($content);
        $data['remark'] = $request->input('remark') ?? '';
        // $data->save()
        $m = new SystemNotice();
        $res = $m->insertGetId($data);
		if ($res) {
			return ResponeSuccess('操作成功',$res);
		} else {
			return $this->response->errorInternal('操作失败');
		}

	}

	/**
	 * 新闻公告编辑
	 *
	 * @return Response
	 */
	public function system_notice_edit(Request $request)
	{
        Validator::make($request->all(), [
            'NoticeTitle'   => ['required'],
            'PlatformType'  => ['in:0,1,2'],
            'PublisherTime'     => ['required'],
            'SortID'        => ['required', 'numeric', 'min:1'],
            'is_img'        => 'required|in:0,1',
            'img'           => ['nullable'],
            'content'       => ['nullable'],
            'remark'        => ['nullable']
        ], [
            'NoticeTitle.required'   => '公告名称必填',
            //'MoblieContent.required' => '手机内容必填',
            'SortID.required'        => '排序号必填',
            'SortID.numeric'         => '排序值必须是数字',
            'SortID.min'             => '排序值必须大于0',
            'PublisherTime.required'     => '发布时间必填',
            'PlatformType.in'        => '类型不在可选范围',
            'is_img.required'        => '参数不正确',
            'is_img.in'        => '内容不在可选范围',
        ])->validate();
		$notice_id = $request->input('NoticeID', '');
		if (!$notice_id) {
			return $this->response->errorBadRequest('没有id');
		}
		$model = SystemNotice::find($notice_id);//新闻公告表
		if (!$model) {
			return $this->response->errorNotFound('没有找到id');
		}
        // $model->loadFromRequest();
        $content = $request->input('content','?') ?? '';
        $data['NoticeTitle'] = $request->input('NoticeTitle');
        $data['SortID'] = $request->input('SortID');
        $data['PublisherTime'] = $request->input('PublisherTime');
        $data['PlatformType'] = $request->input('PlatformType');
        $data['is_img'] = $request->input('is_img');
        $data['img'] = $request->input('img') ?? '';
        $data['content'] = htmlspecialchars($content);
        $data['remark'] = $request->input('remark') ?? '';

        $res = SystemNotice::where('NoticeID', $notice_id)->update($data);
		if ($res) {
			return ResponeSuccess('操作成功');
		}
		return $this->response->errorInternal('操作失败');
	}

	/**
	 * 新闻公告删除
	 *
	 * @return Response
	 */
	public function system_notice_delete(Request $request)
	{
//		$ids = $request->input('ids', '');
//		$res = SystemNotice::whereIn('NoticeID', $ids)->delete();
//		if ($res) {
//			return ResponeSuccess('删除成功');
//		}
//		return $this->response->errorInternal('删除失败');

        $request->validate([
            'NoticeID' => 'integer',
        ]);
        try {
            $res = SystemNotice::where('NoticeID', $request->input('NoticeID'))->delete();
            if (!$res) {
                return ResponeFails('操作失败');
            }
            return ResponeSuccess('操作成功');
        } catch (Exception $e){
            return ResponeFails('操作异常');
        }
	}

	/**
	 * 广告管理列表
	 *
	 * @return Response
	 **/
	public function ads_list()
	{
		$list = Ads::paginate(config('page.list_rows'));
		return $this->response->paginator($list, new AdsTransformer())->addMeta('type', Ads::TYPE);
	}

	/**
	 * 广告管理新增和编辑
	 *
	 * @return Response
	 **/
	public function ads_save(Request $request)
	{
		Validator::make($request->all(), [
			'title'        => ['required'],
			'link_url'     => ['required', Rule::in(array_keys(Ads::TYPE))],
			'url'          => [Rule::requiredIf($request->input('link_url') == Ads::WEBSITE), 'url'],
			'image'        => ['image'],
			'resource_url' => ['required_without:id'],
			'sort_id'      => ['numeric', 'min:1'],
		], [
			'title.required'                => '广告标题必填',
			'link_url.required'             => '活动类型必选',
			'link_url.in'                   => '活动类型不在可选范围',
			'url.required_if'               => '网址不能为空',
			'image.image'                   => '图片类型不正确',
			'resource_url.required_without' => '图片必传',
			'sort_id.numeric'               => '排序值必须是数字',
			'sort_id.min'                   => '排序值必须大于0',
		])->validate();
		if ($request->input('id')) {
			$model = Ads::find($request->input('id'));
			if (!$model) {
				return ResponeFails('广告不存在');
			}
			//echo asset('storage/'.$model->resource_url);
		} else {
			$model = new Ads();
		}
		if (isset($_FILES['image']) && $request->file('image')->isValid()) {

			$path = $request->image->store('ads_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'ads_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
			$model->resource_url = $path;

		} else {
			$model->resource_url = $request->input('resource_url');
		}
		$model->title = $request->input('title');
		$model->type = $request->input('type');
		$model->sort_id = $request->input('sort_id', 0);
		$model->remark = $request->input('remark', '');
		$model->content = $request->input('content','');
		if ($request->input('link_url') == Ads::WEBSITE) {
			$model->link_url = $request->input('url');
		} else {
			$model->link_url = $request->input('link_url', 0);
		}

		$model->platform_type = $request->input('platform_type');
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
			'image' => ['required', 'file', 'image'],
		], [
			'image.required' => '图片必传',
			'image.image'    => '图片类型不正确',
		])->validate();
		if ($request->file('image')->isValid()) {
			$path = $request->image->store('ads_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'ads_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
			return ResponeSuccess('上传成功', ['path' => $path, 'all_path' => asset('storage/' . $path)]);
		}
		return ResponeFails('上传失败');
	}

	/**
	 * 广告管理删除
	 *
	 * @return Response
	 */
	public function ads_delete(Request $request)
	{
		$ids = $request->input('ids', '');
		$res = Ads::whereIn('id', $ids)->delete();
		if ($res) {
			return ResponeSuccess('删除成功');
		}
		return $this->response->errorInternal('删除失败');
	}

	/*
	 * 系统消息列表
	 * @return Response
	 * */
	public function system_message_list()
	{
		$list = SystemMessage::paginate(config('page.list_rows'));
		return $this->response->paginator($list, new SystemMessageTransformer());
	}

	/*
	 * 所有游戏和房间
	 * @return Response
	 * */
	public function kind_rooms()
	{
		$list = GameKindItem::with(['rooms:KindID,ServerID,ServerName'])->get();
		return ResponeSuccess('请求成功', $list);

	}

	/*
	 * 系统消息新增
	 * @return Response
	 * */
	public function system_message_add(SystemMessageRequest $request)
	{
		$res = SystemMessage::saveRecord($request->all());
		if (!$res) {
			return ResponeFails('添加失败');
		}
		messageInform();
		return ResponeSuccess('添加成功');
	}

	/*
	 * 系统消息编辑
	 * @return Response
	 * */
	public function system_message_edit(SystemMessageRequest $request, $message_id)
	{
		$res = SystemMessage::saveRecord($request->all(), $message_id);
		if (!$res) {
			return ResponeFails('修改失败');
		}
		messageInform();
		return ResponeSuccess('修改成功');
	}

	/*
	 * 系统消息禁用/启用
	 * @return Response
	 * */
	public function status(Request $request)
	{
		$id = request('id');
		$status = request('status') ? 0 : 1;
		$model = SystemMessage::find($id);
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

	/**
	 * 系统消息删除
	 *
	 * @return Response
	 */
	public function system_message_delete(Request $request)
	{
		$ids = $request->input('ids', '');
		$res = SystemMessage::whereIn('ID', $ids)->delete();
		if ($res) {
			return ResponeSuccess('删除成功');
		}
		return $this->response->errorInternal('删除失败');
	}

	/*
	 * 站点配置-系统客服展示
	 * @return data
	 * */
	public function customer_service_show()
	{
	    try {
            $data = SystemSetting::where('group', 'customer_service')->whereIn('key', [
                /*'telephone_number',
                'wechat_number',
                'qq_number',
                'link_url',*/
                'redirect_url'
            ])->pluck('value', 'key');
            $extend = Dict::where('id', 11)->value('extend');
            $data['content'] = stripslashes(htmlspecialchars_decode($extend));
            $bgcolor = Dict::where('id', 12)->value('extend');
            $data['content'] = stripslashes(htmlspecialchars_decode($extend));
            $data['bgcolor'] = $bgcolor;
            //$data['all_image'] = isset($data['link_url']) ? asset('storage/' . $data['link_url']) : '';
            return ResponeSuccess('获取成功', $data);
        } catch (\Exception $e){
	        \Log::error($e->getMessage());
	        return ResponeFails('获取失败');
        }
	}

	/*
	 * 站点配置-系统客服设置
	 * @return Response
	 *
	 * */
	public function system_customer_service(Request $request)
	{
		Validator::make($request->all(), [
			//'telephone_number' => ['required'],
			//'telephone_number' => ['required', 'regex:/^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\d{8}$|^0\d{2,3}-?\d{7,8}$/'],
			//'wechat_number'    => ['required'],
			//'qq_number'        => ['required'],
			//'qq_number'        => ['required', 'regex:/^[1-9][0-9]{4,}$/'],
			//'image'            => ['image'],
			//'link_url'         => ['required_without:id'],
            'redirect_url' => ['required', 'url'],
            'bgcolor' => ['required'],
            'content' => ['required'],
		], [
			//'telephone_number.required' => '客服电话必填',
			//'telephone_number.regex'    => '客服电话格式不正确',
			//'wechat_number.required'    => '微信号必填',
			//'qq_number.required'        => 'QQ号必填',
			//'qq_number.regex'           => 'QQ号格式不正确',
			//'image.image'               => '图片类型不正确',
			//'link_url.required_without' => '图片必传',
			'redirect_url.required' => '跳转地址必填',
            'redirect_url.url'      => '地址格式不正确',
            'bgcolor.required'      => '背景色未配置',
            'content.required'      => '常见问题不能为空',
		])->validate();
		try {
            $model = new SystemSetting();
            \DB::beginTransaction();
            $info = [
                /*[
                    'key'   => 'telephone_number',
                    'value' => $request->input('telephone_number')
                ],
                [
                    'key'   => 'wechat_number',
                    'value' => $request->input('wechat_number')
                ],
                [
                    'key'   => 'qq_number',
                    'value' => $request->input('qq_number')
                ],
                [
                    'key'   => 'link_url',
                    'value' => $request->input('link_url')
                ],*/
                [
                    'key' => 'redirect_url',
                    'value' => $request->input('redirect_url')
                ]
            ];
            // return $info;
            $res = $model->edit('customer_service', $info);
            $content = $request->input('content','?') ?? '';
            $bgcolor = $request->input('bgcolor','#060605') ?? '';
            Dict::where('id',11)->update(['extend' => htmlspecialchars($content)]);
            Dict::where('id',12)->update(['extend' => $bgcolor]);
            \DB::commit();
            return ResponeSuccess('操作成功');
        } catch(NewException $e){
            \DB::rollBack();
		    \Log::error('【客服配置】:'.$e->getMessage());
            ResponeFails('操作失败');
        }
	}

	/*
	 * 站点配置-技术支持
	 * @return Response
	 * */
	public function technical_support(Request $request)
	{
		Validator::make($request->all(), [
			'website' => ['required'],
		], [
			'website.required' => '技术支持网址必填',
		])->validate();
		$model = new SystemSetting();
		$info = [
			[
				'key'   => 'website',
				'value' => $request->input('website')
			],
		];
		$res = $model->edit('technical_support', $info);
		if ($res) {
			return ResponeSuccess('操作成功');
		} else {
			return ResponeFails('操作失败');
		}
	}

	/*
	* 站点配置-技术支持展示
	* @return data
	* */
	public function technical_support_show()
	{
		$data = SystemSetting::where('group', 'technical_support')->where('key', 'website')->pluck('value', 'key');
		return ResponeSuccess('获取成功', $data);
	}

    /*
     * 站点配置-商城云闪付配置
     * @return Response
     * */
    public function ysf_config(Request $request)
    {
//        Validator::make($request->all(), [
//            'ysf_url' => ['required'],
//        ], [
//            'ysf_url.required' => '跳转地址必填',
//        ])->validate();
        $model = new SystemSetting();
        $info = [
            [
                'key'   => 'ysf_url',
                'value' => $request->input('ysf_url')
            ],
        ];
        $res = $model->edit('ysf_config', $info);
        if ($res) {
            return ResponeSuccess('操作成功');
        } else {
            return ResponeFails('操作失败');
        }
    }

    /*
    * 站点配置-商城云闪付配置
    * @return data
    * */
    public function ysf_config_show()
    {
        $data = SystemSetting::where('group', 'ysf_config')->where('key', 'ysf_url')->pluck('value', 'key');
        return ResponeSuccess('获取成功', $data ? : ["ysf_url" => ""]);
    }

}
