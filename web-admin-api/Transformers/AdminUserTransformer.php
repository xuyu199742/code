<?php
/*
 |--------------------------------------------------------------------------
 |
 |--------------------------------------------------------------------------
 | Notes:
 | Class AdminUserTransformer
 | User: Administrator
 | Date: 2019/6/20
 | Time: 20:55
 |
 |  * @return
 |  |
 |
 */

namespace Transformers;


use League\Fractal\TransformerAbstract;
use Models\AdminPlatform\AdminUser;

class AdminUserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['roles'];

    public function transform(AdminUser $adminUser)
    {
        return [
            'id'              => $adminUser->id,
            'username'        => $adminUser->username,
            'parent_username' => $adminUser->parent->username ?? '',
            'email'           => $adminUser->email,
            'mobile'          => $adminUser->mobile,
            'sex'             => $adminUser->sex,
            'sex_text'        => $adminUser->sex_text,
            'status'          => $adminUser->status,
            'has_bind'        => empty($adminUser->google2fa_secret) ? false : true,
            'status_text'     => $adminUser->status_text,
            'created_at'      => $adminUser->created_at,
        ];
    }

    public function includeRoles(AdminUser $adminUser)
    {
        return $this->primitive($adminUser->roles, function ($item) {
            return $item;
        });
    }

}
