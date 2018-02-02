<?php

use app\admin\model\User;

if (!function_exists('userHasPermissions')) {
    /**
     * 检查管理员是否有权限
     * @param string:permissions 权限标识符数组
     */
    function userHasPermissions($permissions=[]){
        // 获取当前登录的管理员
        $cu = User::getCurrentUser();
        // 如果当前管理员不存在则返回false
        if(is_null($cu)) return false;
        // 循环判断是否包含标识符
        foreach($permissions as $permission){
            // 如果有该权限, 则直接返回false
            if($cu->hasPermission($permission) === true){
                return true;
            }
        }
        return false;
    }
}