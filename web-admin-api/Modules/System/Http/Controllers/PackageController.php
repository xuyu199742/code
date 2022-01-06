<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Models\AdminPlatform\Version;
use Modules\System\Http\Requests\VersionRequest;
use Transformers\VersionsTransformer;

class PackageController extends Controller
{
    public function index()
    {
        $list = Version::paginate(config('page.list_rows'));
        //return ResponeSuccess('查询成功',$list,new VersionsTransformer());
        return $this->response->paginator($list, new VersionsTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VersionRequest $request
     *
     * @return Response
     */
    public function store(VersionRequest $request)
    {
        $model = new Version();
        if ($request->input('id')) {
            $model = $model->find($request->input('id'));
        }
        $model->fill($request->all());
        $model->admin_id = \Auth::guard('admin')->id();
        try {
            if ($model->save()) {
                return ResponeSuccess('保存成功');
            } else {
                return $this->response->errorBadRequest('保存失败');
            }
        } catch (\Exception $e) {
            return $this->response->errorInternal('系统错误');
        }
    }

    /**
     * Show the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $version = Version::find($id);
        if ($version) {
            return ResponeSuccess('查询成功',$version);
        }
        return $this->response->errorNotFound('版本不存在');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (Version::destroy($id) > 0) {
            return ResponeSuccess('删除成功');
        }
        return $this->response->errorInternal('删除失败');
    }
}
