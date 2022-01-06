<?php

namespace App\Mixin;



class PermissionMixin
{
    public $permission='';


    public function permission(){
        return function (...$parameters) {
            \Cache::put($this->uri, serialize($parameters),10);
        };
    }

    public function getPermission(){
        return function () {
            return \Cache::get($this->uri);
        };
    }




}