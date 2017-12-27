<?php
namespace app\admin\controller;

use think\View;
use think\Session;

use allensnape\utils\StringUtil;

use app\admin\controller\AdminBaseController;
use app\admin\model\User as UserModel;
use app\admin\model\UserLog as UserLogModel;

class User extends AdminBaseController{

    // 管理员管理页面跳转
    public function listPage(){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) $this->error('您无权查看该列表!');

        $this->assign('title', '管理员列表');
        $this->assign('defaultParams', '?porder=create_time&psort=desc');
        $this->assign('data', $this->userlist());
        $this->assign('user', UserModel::getCurrentUser());
        return $this->fetch('user/list');
    }

    // 管理员操作记录页面跳转
    public function loglistPage(){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) $this->error('您无权查看该列表!');

        $userlogs = new UserLogModel();
        $this->assign('title', '管理员操作记录列表');
        $this->assign('defaultParams', '?porder=create_time&psort=desc');
        $this->assign('data', $userlogs->getStandardPagedArrayList([[['type', 'title', 'username']]], ['create_time'], [['user_log', '*'], ['user', 'username', 'user.id = user_log.user_id']]));
        return $this->fetch('user/loglist');
    }

    // 获取管理员列表 - json格式
    public function jsonlist(){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) return $this->json_error('您无权操作!');

        return $this->json_success('获取成功!', $this->userlist());
    }

    // 管理员列表
    private function userlist(){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) return $this->json_error('您无权操作!');

        $user = new UserModel();
        return $user->getStandardPagedArrayList([[['mobile', 'name']]], ['create_time']);
    }
    
    // 添加管理员
    public function add(){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) return $this->json_error('您无权操作!');

        $user = new UserModel($_POST);

        // 检查数据
        if(!preg_match('/^[\d\w\x80-\xff]{6,16}$/', $user['username'])){
            return $this->json_error('账号为6-16的中文或字母或数字!');
        }
        else if(!preg_match('/^.{6,}$/', $user['password'])){
            return $this->json_error('密码至少6位任意字符!');
        }
        else if(!is_null($user['mobile']) && !StringUtil::isMobile($user['mobile'])){
            return $this->json_error('请输入正确的大陆手机号!');
        }
        else if(!$this->is0Or1($user['is_admin'])){
            return $this->json_error('是否为超级管理员参数错误!');
        }

        // 检查账号是否存在
        $exists = UserModel::where('username', $user['username'])->find();
        if($exists != null){
            return $this->json_error('用户名已存在!');
        }

        // 加密密码
        $user['password'] = $user->getSaltedPassword();

        // 初始化数据->过滤字段->插入数据
        return $user->beforeSave()->allowField(true)->save() == 1 ? $this->json_success('添加成功!') : $this->json_error('添加失败!');
    }

    // 修改管理员
    public function edit(){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) return $this->json_error('您无权操作!');

        $user = new UserModel($_POST);
        
        // 检查数据
        if(is_null($user['id']) || $user['id'] == ''){
            return $this->json_error('请选择要修改的管理员!');
        }
        else if(!preg_match('/^[\d\w\x80-\xff]{6,16}$/', $user['username'])){
            return $this->json_error('账号为6-16的中文或字母或数字!');
        }
        else if(!is_null($user['mobile']) && !StringUtil::isMobile($user['mobile'])){
            return $this->json_error('请输入正确的大陆手机号!');
        }
        else if(!$this->is0Or1($user['is_admin'])){
            return $this->json_error('是否为超级管理员参数错误!');
        }

        // 检查修改的管理员是否存在
        $old = UserModel::get($user['id']);
        if(is_null($old)){
            return $this->json_error('修改的用户不存在!');
        }

        // 检查用户名是否存在
        if($user['username'] !== $old['username']){
            $exists = UserModel::where('username', $user['username'])->find();
            if($exists != null){
                return $this->json_error('用户名已存在!');
            }
        }

        // 如果输入了密码则加密密码
        if($this->hasText($user['password'])){
            // 加密密码
            $user['password'] = $user->getSaltedPassword();
        }else{
            unset($user['password']);
        }
        
        if($old->allowField(true)->save($user->beforeEdit()->getData(), ['id'=>$user['id']]) == 1){
            // 踢出修改的管理员
            $this->kickout($old['id']);
            return $this->json_success('修改成功!');
        }

        return $this->json_error('修改失败!');
    }

    // 操作管理员
    public function ope($id=null, $disabled=null){
        // 检查当前管理员是否是超级管理员
        $cu = UserModel::getCurrentUser();
        if($cu['is_admin'] != 1) return $this->json_error('您无权操作!');
        
        if(!$this->hasText($id)){
            return $this->json_error('请选择要操作的管理员!');
        }
        else if(!$this->is0Or1($disabled)){
            return $this->json_error('操作参数错误!');
        }

        // 检查修改的管理员是否存在
        $old = UserModel::get($id);
        if(is_null($old)){
            return $this->json_error('修改的用户不存在!');
        }

        $user = new UserModel(['id'=>$id, 'disabled'=>$disabled]);

        if($old->allowField(true)->save($user->beforeEdit()->getData(), ['id'=>$id]) == 1){
            // 踢出被禁用的管理员
            if($disabled == '1') $this->kickout($old['id']);
            return $this->json_success('修改成功!');
        }

        return $this->json_error('修改失败!');
    }

    // 踢出修改的管理员 - 删除对应的session文件
    private function kickout($id=null){
        $id = is_null($id) && 
            isset($_SESSION['think'][AdminBaseController::USER_SESSION_CODE]) && 
            isset($_SESSION['think'][AdminBaseController::USER_SESSION_CODE]['id']) ? 
                $_SESSION['think'][AdminBaseController::USER_SESSION_CODE]['id'] : $id;
        if(is_null($id)) return false;
        foreach($this->getAllSessionIDs() as $index=>$sessionId){
            session_id($sessionId);
            if(!isset($_SESSION)){
                session_start();
            }
            if(isset($_SESSION['think']) && isset($_SESSION['think'][AdminBaseController::USER_SESSION_CODE]) 
                && isset($_SESSION['think'][AdminBaseController::USER_SESSION_CODE]['id'])){
                if($id === $_SESSION['think'][AdminBaseController::USER_SESSION_CODE]['id']){
                    @unlink (session_save_path().DS.'sess_'.$sessionId);
                    session_abort();
                    return true;
                }
            }
            session_abort();
        }
    }

}
