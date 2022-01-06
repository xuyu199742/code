<?php

namespace Modules\System\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Validator;

class UploadsController extends Controller
{
    /*图片上传*/
    public function uploadImage(Request $request)
    {
        Validator::make($request->all(), [
            'image' => ['required', 'file', 'image'],
        ], [
            'image.required' => '图片必传',
            'image.image'    => '图片类型不正确',
        ])->validate();
        if ($request->file('image')->isValid()) {
            $path = $request->image->store('carousel_images', 'public');
            $real_path = $request->file('image')->getRealPath();
            list($bool,$info) = ossUploadFile($real_path,'carousel_images/'.pathinfo($path)['basename']);
            if (!$bool) {
                return ResponeFails('图片OSS上传失败'.$info);
            }
            return ResponeSuccess('上传成功', ['path' => $path, 'all_path' => asset('storage/' . $path)]);
        }
        return ResponeFails('上传失败');
    }
}
