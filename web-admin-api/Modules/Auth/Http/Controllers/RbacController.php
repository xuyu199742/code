<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Models\AdminPlatform\AdminUser;
use Models\AdminPlatform\SubPermissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Transformers\AdminUserTransformer;
use Validator;

class RbacController extends Controller
{
    /**
     * 禁用/启用系统用户
     *
     * @param        $id
     * @param string $status
     *
     * @throws \Exception
     */
    public function disableOrEnable($id)
    {
        $admin_user = AdminUser::withTrashed()->find($id);
        if ($admin_user) {
            if ($admin_user->trashed()) {
                $admin_user->restore();
            } else {
                if (!$admin_user->trashed()) {
                    $admin_user->delete();
                }
            }
            return ResponeSuccess('操作成功');
        } else {
            return $this->response()->errorNotFound('用户不存在');
        }
    }

    /**
     * 管理员列表
     *
     * @return \Dingo\Api\Http\Response
     */
    public function admins()
    {
        $admins = AdminUser::multiSearch(['id', 'username', 'mobile'])->withTrashed();
        return $this->response->paginator($admins->paginate(), new AdminUserTransformer());
    }

    //获取所有角色
    public function roles()
    {
        return ResponeSuccess('查询成功', Role::where('guard_name', $this->guard)->get());
    }

    //获取所有权限
    public function permissions()
    {
        $permissions = Permission::where('guard_name', $this->guard)->get();
        $permissions = collect($permissions)->groupBy('group');
        return ResponeSuccess('查询成功', $permissions);
    }

    //添加/修改角色
    public function addRole(Request $request)
    {
        $model        = new Role();
        $type_message = '添加';
        if ($request->input('id')) {
            $model = $model->find($request->input('id'));
            if (!$model) {
                return ResponeFails('角色不存在');
            }
            $type_message = '修改';
        }
        Validator::make($request->all(), [
            'name'  => ['required', Rule::unique($model->getTable(), 'name')->where('guard_name', $this->guard)->ignore(request('id'))],
            'title' => ['required'],
        ], [
            'name.required'  => '角色别名必传',
            'name.unique'    => '角色别名已经存在',
            'title.required' => '角色名称必传',
        ])->validate();
        $model->name       = $request->input('name');
        $model->title      = $request->input('title');
        $model->guard_name = $this->guard;
        if ($model->save()) {
            return ResponeSuccess($type_message . '角色成功');
        }
        return ResponeFails($type_message . '角色失败');
    }

    //添加权限
    public function addPermission(Request $request)
    {
        $model        = new Permission();
        $type_message = '添加';
        if ($request->input('id')) {
            $model = $model->find($request->input('id'));
            if (!$model) {
                return ResponeFails('该权限不存在');
            }
            $type_message = '修改';
        }
        Validator::make($request->all(), [
            'name'  => ['required', Rule::unique($model->getTable(), 'name')->where('guard_name', $this->guard)->ignore(request('id'))],
            'title' => ['required'],
            'group' => ['required'],
        ], [
            'name.required'  => '权限别名必传',
            'name.unique'    => '权限别名已经存在',
            'title.required' => '权限名称必传',
            'group.required' => '权限组名必传',
        ])->validate();
        $model->name       = $request->input('name');
        $model->title      = $request->input('title');
        $model->group      = $request->input('group');
        $model->guard_name = $this->guard;
        if ($model->save()) {
            return ResponeSuccess($type_message . '权限成功');
        }
        return ResponeFails($type_message . '权限失败');
    }

    //角色绑定权限，根据权限id绑定
    public function roleBindPermission(Request $request)
    {
        Validator::make($request->all(), [
            'role_id'        => ['required'],
            'permission_ids' => ['required', 'Array'],
        ], [
            'role_id.required'        => '角色ID不能为空',
            'permission_ids.required' => '权限ID不能为空',
            'permission_ids.Array'    => '权限ID必须是数组',
        ])->validate();
        //$permission_count = Permission::where('guard_name', $this->guard)->whereIn('id', $request->input('permission_ids'))->count();
        $permissions      = $request->input('permission_ids');
        $permission_count = Permission::where('guard_name', $this->guard)->whereIn('name', array_unique($permissions))->pluck('id');

        //$permission_count = ->count();
       /* if (count($permission_count) != count(array_unique($permissions))) {
            return ResponeFails('角色绑定权限失败:权限节点超出范围');
        }*/
        $role = Role::findById($request->input('role_id'));
        try {
            //$role->syncPermissions($request->input('permission_ids'));
            $role->syncPermissions($permission_count);
        } catch (\Exception $e) {
            return ResponeFails('角色绑定权限失败:' . $e->getMessage());
        }

        return ResponeSuccess('角色绑定权限成功');
    }


    //用户绑定角色
    public function userBindRole(Request $request)
    {
        $model = new AdminUser();
        Validator::make($request->all(), [
            'admin_id' => ['required', Rule::exists($model->getTable(), 'id')],
            'role_ids' => ['required', 'Array'],
        ], [
            'admin_id.required' => '用户ID不能为空',
            'role_ids.required' => '角色ID不能为空',
            'role_ids.Array'    => '角色ID必须是数组',
        ])->validate();
        $roles = Role::where('guard_name', $this->guard)->whereIn('id', $request->input('role_ids'))->pluck('name','id')->toArray();
        /*if (array_diff(array_keys($roles), $request->input('role_ids'))) {
            return ResponeFails('用户绑定角色失败:角色超出范围');
        }*/
        $model = $model->find($request->input('admin_id'));
        $model->syncRoles(array_keys($roles));
        return ResponeSuccess('用户绑定角色成功');
    }

    //获取用户拥有的权限
    /*public function powers()
    {
        return ResponeSuccess('查询成功',$this->user()->getAllPermissions()->pluck('title','name'));
    }*/
    //获取用户角色
    /*public function userRoles($id)
    {
        $admin = AdminUser::find($id);
        if ($admin) {
            return ResponeSuccess('查询成功', $admin->roles);
        }
        return ResponeFails('未找到该用户');

    }*/

    //获取角色拥有的权限
    public function roleHasPermissions($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return ResponeFails('未找到该角色');
        }
        return ResponeSuccess('查询成功', $role->permissions);

    }

    //删除角色
    public function deleteRole($id)
    {
        $role = Role::find($id);
        if ($role && $role->delete()) {
            return ResponeSuccess('删除成功');
        }
        return ResponeFails('该角色不存在');
    }

    //删除权限
    public function deletePermission($id)
    {
        $permission = Permission::find($id);
        if ($permission && $permission->delete()) {
            return ResponeSuccess('删除成功');
        }
        return ResponeFails('该权限不存在');
    }

    //刷新权限
    public function refreshPermission()
    {
        Artisan::call('permission:list');
        return ResponeSuccess('刷新成功');
    }

    //修改用户手机号权限
    public function editPhoneRule(Request $request)
    {
        $model = new AdminUser();
        if (\request()->isMethod('get')){
            Validator::make($request->all(), [
                'admin_id'  => ['required', Rule::exists($model->getTable(), 'id')],
            ], [
                'admin_id.required' => '用户ID不能为空',
            ])->validate();
            $data = SubPermissions::where('admin_id',\request('admin_id'))->pluck('sign')->toArray();
            $sub_permissions = config('sub_permissions');
            if (!empty($data)){
                foreach ($sub_permissions as $k => $v){
                    if (in_array($k,$data)){
                        $sub_permissions[$k]['status'] = 1;
                    }
                }
            }
            return ResponeSuccess('操作成功',$sub_permissions);
        }
        if (\request()->isMethod('post')){
            Validator::make($request->all(), [
                'admin_id'  => ['required', Rule::exists($model->getTable(), 'id')],
                'signs'     => 'array',
            ], [
                'admin_id.required'     => '用户ID不能为空',
                'signs.array'           => '权限格式为数组',
            ])->validate();
            SubPermissions::where('admin_id',\request('admin_id'))->delete();
            $signs = \request('signs');
            $info = [];
            foreach ($signs as $k => $v){
                $info[$k]['admin_id']   = request('admin_id');
                $info[$k]['sign']       = $v;
            }
            $res = SubPermissions::insert($info);
            return ResponeSuccess('操作成功');
        }
    }
}
