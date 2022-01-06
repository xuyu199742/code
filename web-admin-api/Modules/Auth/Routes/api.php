<?php

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    //登陆路由
    $api->group(['prefix' => 'auth', 'namespace' => '\Modules\Auth\Http\Controllers'], function ($api) {
        $api->post('login', 'AuthController@login');
        $api->post('logout', 'AuthController@logout');
        $api->post('refresh', 'AuthController@refresh');

        $api->get('/2fa/enable', 'Google2FAController@enableTwoFactor');
        $api->delete('/2fa/disable/{id}', ['uses' => 'Google2FAController@disableTwoFactor', 'permission' => ['admin', '重置谷歌验证器', '权限管理', 'v1'], 'middleware' => ['auth:admin', 'admin']])->name('auth.google.disable');
        $api->post('/2fa/validate', 'Google2FAController@postValidateToken');

        $api->post('me', ['uses' => 'AuthController@me', 'middleware' => ['auth:admin']])->name('auth.me');
        $api->post('reset', ['uses' => 'AuthController@reset', 'middleware' => ['auth:admin']])->name('auth.reset');
        $api->post('addUser', ['uses' => 'AuthController@addUser', 'permission' => ['admin', '添加/修改管理员', '权限管理', 'v1'], 'middleware' => ['auth:admin', 'admin']])->name('auth.add.user');
    });
    //rbac管理
    $api->group(['prefix' => 'rbac', 'middleware' => ['auth:admin', 'admin'], 'namespace' => '\Modules\Auth\Http\Controllers'], function ($api) {
        //登陆路由
        $api->delete('switch/{id}', ['uses' => 'RbacController@disableOrEnable', 'permission' => ['admin', '禁用/启用管理员', '权限管理', 'v1']])
            ->name('rbac.admin.status');
        $api->get('users', ['uses' => 'RbacController@admins', 'permission' => ['admin', '管理员列表', '权限管理', 'v1']])->name('rbac.admin.list');

        $api->get('roles', ['uses' => 'RbacController@roles', 'permission' => ['admin', '角色列表', '权限管理', 'v1']])->name('rbac.roles');
        $api->get('permissions', ['uses' => 'RbacController@permissions', 'permission' => ['admin', '权限列表', '权限管理', 'v1']])->name('rbac.permissions');
        $api->post('addRole', ['uses' => 'RbacController@addRole', 'permission' => ['admin', '添加角色', '权限管理', 'v1']])->name('rbac.add.role');
        $api->post('addPermission', ['uses' => 'RbacController@addPermission', 'permission' => ['admin', '添加权限', '权限管理', 'v1']])->name('rbac.add.permission');
        $api->post('roleBindPermission', ['uses' => 'RbacController@roleBindPermission', 'permission' => ['admin', '角色绑定权限', '权限管理', 'v1']])->name('rbac.role.bind.permission');
        $api->post('userBindRole', ['uses' => 'RbacController@userBindRole', 'permission' => ['admin', '用户绑定角色', '权限管理', 'v1']])->name('rbac.user.bind.role');
        //$api->get('userRoles/{id}', ['uses' => 'RbacController@userRoles', 'permission' => ['admin', '获取用户拥有的角色', '权限管理','v1']])->name('rbac.user.roles');
        $api->get('roleHasPermissions/{id}', ['uses' => 'RbacController@roleHasPermissions', 'permission' => ['admin', '获取角色拥有的权限', '权限管理', 'v1']])->name('rbac.role.has.permissions');
        $api->delete('deleteRole/{id}', ['uses' => 'RbacController@deleteRole', 'permission' => ['admin', '删除角色', '权限管理', 'v1']])->name('rbac.delete.role');
        $api->delete('deletePermission/{id}', ['uses' => 'RbacController@deletePermission', 'permission' => ['admin', '删除权限', '权限管理', 'v1']])->name('rbac.delete.permission');
        //$api->get('powers', 'RbacController@powers')->name('rbac.user.powers');
        $api->get('refresh/permission', ['uses' => 'RbacController@refreshPermission', 'permission' => ['admin', '刷新权限', '权限管理', 'v1']])->name('rbac.permission.refresh');
        //修改手机号权限
        $api->match(['get','post'],'editPhoneRule', ['uses' => 'RbacController@editPhoneRule', 'permission' => ['admin', '修改手机号权限', '权限管理', 'v1']])->name('rbac.edit_phone_rule');
        $api->get('menuList', ['uses' => 'MenuController@menuList']);//获取菜单权限
        $api->get('getMenuRule', ['uses' => 'MenuController@getMenuRule', 'permission' => ['admin', '获取权限菜单', '权限管理', 'v1']])->name('rabc.get_menu_rule');//获取菜单权限
        $api->post('saveMenuRule', ['uses' => 'MenuController@saveMenuRule', 'permission' => ['admin', '绑定权限菜单', '权限管理', 'v1']])->name('rabc.save_menu_rule');//绑定菜单权限
    });


});
