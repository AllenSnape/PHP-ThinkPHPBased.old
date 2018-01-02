<?php
namespace app\admin\controller;

use think\Session;

use app\admin\model\User;
use app\admin\model\Menu;

class Home extends AdminBaseController{

    /**
     * 检查是否登录并返回管理员信息
     */
    public function logined(){
        $user = Session::get(parent::USER_SESSION_CODE);
        return $this->json_success(
            $user['name'].' logined.', 
            $user->hidden(['password'])
        );
    }

    /**
     * 登出
     */
    public function logout(){
        $user = Session::pull(parent::USER_SESSION_CODE);
        return $this->json_success('Goodbye '.$user['name']);
    }
    
    /**
     * 管理员修改信息 - 暂且只能修改密码
     */
    public function editinfo(){
        $user = new User($_POST);
        // 当前登录的用户
        $old = User::getCurrentUser();

        // 如果填写了新密码的, 则检查相应的参数和数据
        if($this->hasText($user['password'])){
            if(!preg_match('/^.{6,16}$/', $user['oldPassword'])){
                return $this->json_error('请输入6-16位的旧密码!');
            }
            else if(!preg_match('/^.{6,16}$/', $user['password'])){
                return $this->json_error('请输入6-16位的新密码!');
            }
            else if($user['password'] == $user['oldPassword']){
                return $this->json_error('新旧密码不得相同!');
            }
            
            // 检查旧密码是否正确
            if($old['password'] != User::getSaltedPasswordStatically($user['oldPassword'])){
                return $this->json_error('旧密码错误!');
            }
            $user['password'] = $user->getSaltedPassword();
        }

        // 修改用户
        if($old->allowField(true)->save($user->beforeEdit(), ['id'=>$old['id']])){
            return $this->logout();
        }

        return $this->json_error('操作失败, 请联系超级管理员或运维人员处理!');
    }
    
    /**
     * 启动欢迎界面
     */
    public function welcomePage(){
        return $this->fetch('home/welcome');
    }

    /**
     * 管理员首页页面跳转
     */
    public function homePage(){
        $this->assign('title', '管理系统');
        $this->assign('menus', Menu::getCacheMenus());
        return $this->fetch('home/home');
    }

}