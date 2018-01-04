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
        $user->removePermissionCache();
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
    
    /**
     * 二进制文件上传
     */
    public function upload(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                return $this->json_success('上传成功!', DS . 'uploads'. DS . $info->getSaveName());
            }else{
                return $this->json_error($file->getError());
            }
        }else{
            return $this->json_error('请选择要上传的文件!');
        }
    }

    /**
     * base64文件上传
     */
    public function uploadInBase64($file=null){
        // 检查参数
        if(!$this->hasText($file)) return $this->json_error('请选择要上传的文件!');
        // 整理数据
        $base64File = trim($file);
        // 保存的路径
        $savePath = ROOT_PATH . 'public' . DS . 'uploads' . DS;
        // 检查路径是否存在
        if(!file_exists($savePath)){
            mkdir($savePath, 0777);
        }
        // 检查格式
        if(preg_match('/^(data:\s*\w+\/(\w+);base64,)/', $base64File, $result)){
            // 文件格式
            $type = $result[2];
            $dataPath = date('Ymd').DS;
            $newFile = $dataPath.md5($base64File).'.'.$type;
            // 检查路径是否存在
            if(!file_exists($savePath.$dataPath)){
                mkdir($savePath.$dataPath, 0777);
            }
            $savePath = $savePath.$newFile;
            if(file_put_contents($savePath, base64_decode(str_replace($result[1], '', $base64File)))){
                return $this->json_success('上传成功!', DS . 'uploads'. DS . $newFile);
            }
        }
        return $this->json_error('上传的base64格式文件有误!');

        /* try{
            document.getElementById("sendMsgInput").addEventListener('paste', function(e){
                if (e.clipboardData && e.clipboardData.items[0].type.indexOf('image') > -1) {
                    var that = this, reader =  new FileReader();
                    file = e.clipboardData.items[0].getAsFile();
                    var layerLoadingFlag = layer.load(2);
                    reader.onload = function (e) {
                        var xhr = new XMLHttpRequest(), fd = new FormData();
                        xhr.open('POST', '__ROOT__/index.php/index/index/uploadInBase64.html', true);
                        xhr.onload = function(){
                            layer.close(layerLoadingFlag);
                            var data = JSON.parse(this.responseText);
                            if(data.result == 1){
                                回调('__ROOT__'+data.data);
                            }else{
                                layer.alert(data.message, {icon: 5});
                            }
                        }
                        fd.append('file', this.result);
                        xhr.send(fd);
                    }
                    reader.readAsDataURL(file);
                }
            }, false);
        }catch(e){
            layer.alert("初始化粘贴图片上传功能失败!", {icon: 5});
            console.error(e);
        } */
    }

}