<?php
namespace app\admin\controller;

use think\Db;

use app\admin\model\User as UserModel;
use app\admin\model\Role as RoleModel;
use app\admin\model\UserLog as UserLogModel;
use app\admin\model\UserRole as UserRoleModel;

class User extends AdminBaseController{

    // 管理员管理页面跳转
    public function listPage(){
        $this->assign('title', '管理员列表');
        $this->assign('defaultParams', '?porder=create_time&psort=desc');
        $this->assign('data', $this->userlist());
        $this->assign('user', UserModel::getCurrentUser());
        $this->assign('roles', RoleModel::where(['disabled'=>0])->select());
        return $this->fetch('user/list');
    }

    // 管理员操作记录页面跳转
    public function loglistPage(){
        $userlogs = new UserLogModel();
        $user = new UserModel();
        $this->assign('title', '管理员操作记录列表');
        $this->assign('defaultParams', '?porder=create_time&psort=desc');
        $this->assign('data', $userlogs->getStandardPagedArrayList([[['type', 'title', 'username']]], ['create_time'], [[$userlogs->getTable(), '*'], [$user->getTable(), 'username', $user->getTable().'.id = '.$userlogs->getTable().'.user_id']]));
        return $this->fetch('user/loglist');
    }

    // 获取管理员列表 - json格式
    public function jsonlist(){
        return $this->json_success('获取成功!', $this->userlist());
    }

    // 管理员列表
    private function userlist(){
        $user = new UserModel();
        return $user->getStandardPagedArrayList([[['mobile', 'name']], [['disabled'], '', '', '=']], ['create_time']);
    }
    
    // 添加管理员
    public function add(){
        $user = new UserModel($_POST);

        // 检查数据
        if(!preg_match('/^[\d\w\x80-\xff]{6,16}$/', $user['username'])){
            return $this->json_error('账号为6-16的中文或字母或数字!');
        }
        else if(!preg_match('/^.{6,}$/', $user['password'])){
            return $this->json_error('密码至少6位任意字符!');
        }

        // 检查账号是否存在
        $exists = UserModel::where('username', $user['username'])->find();
        if($exists != null){
            return $this->json_error('用户名已存在!');
        }

        // 加密密码
        $user['password'] = $user->getSaltedPassword();

        // 初始化数据->过滤字段->插入数据
        return $user->beforeSave()->save() == 1 ? $this->json_success('添加成功!') : $this->json_error('添加失败!');
    }

    // 修改管理员
    public function edit(){
        $user = new UserModel($_POST);
        
        // 检查数据
        if(is_null($user['id']) || $user['id'] == ''){
            return $this->json_error('请选择要修改的管理员!');
        }
        else if(!preg_match('/^[\d\w\x80-\xff]{6,16}$/', $user['username'])){
            return $this->json_error('账号为6-16的中文或字母或数字!');
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
        
        if($old->allowField(true)->save($user->beforeEdit(), ['id'=>$user['id']]) == 1){
            // 踢出修改的管理员
            UserModel::kickout($old['id']);
            return $this->json_success('修改成功!');
        }

        return $this->json_error('无可修改内容!');
    }

    // 操作管理员
    public function dis($id=null, $disabled=null){
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

        if($old->allowField(true)->save($user->beforeEdit(), ['id'=>$id]) == 1){
            // 踢出被禁用的管理员
            if($disabled == '1') UserModel::kickout($old['id']);
            return $this->json_success('修改成功!');
        }

        return $this->json_error('修改失败!');
    }

    /**
     * 查询对应管理员的角色授权列表
     */
    public function userRoleListJson($userid=null){
        // 检查参数
        if(!$this->hasText($userid)){
            return $this->json_error('请选择要查看的管理员!');
        }

        // 检查查看的管理员是否存在
        $user = UserModel::get($userid);
        if(is_null($user)){
            return $this->json_error('查看的管理员不存在!');
        }

        return $this->json_success('获取成功!', UserRoleModel::where(['user_id'=>$userid])->select());
    }

    /**
     * 设置管理员角色
     */
    public function setUserRoles($userid=null, $roleids=null){
        // 检查参数
        if(!$this->hasText($userid)){
            return $this->json_error('管理员id参数错误!');
        }

        // 检查角色id的json集合
        $roleids = json_decode($roleids, true);
        if(is_null($roleids)){
            return $this->json_error('角色id集合参数错误!');
        }

        // 检查操作的管理员是否存在
        $user = UserModel::get($userid);
        if(is_null($user)){
            return $this->json_error('操作的管理员不存在!');
        }

        // 添加之前先删除所有关联
        UserRoleModel::where(['user_id'=>$userid])->delete(true);

        // 过滤掉不存在的角色id
        $roles = RoleModel::where('id', 'in', $roleids)->select();

        if(count($roles) > 0){
            // 拼接批量添加sql
            $sql = 'INSERT INTO '.UserRoleModel::TABLE_NAME.'(`user_id`, `role_id`) VALUES ';
            $sqlValues = [];
            // 循环拼接
            foreach($roles as $rk=>$role){
                $sql .= '(?, ?),';
                array_push($sqlValues, $user['id']);
                array_push($sqlValues, $role['id']);
            }
            $sql = substr($sql, 0, strlen($sql)-1);
            return Db::execute($sql, $sqlValues) && UserModel::kickout($user['id']) ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
        }

        return $this->json_success('操作成功!');
    }

    /**
     * 清空管理员菜单权限缓存
     */
    public function cleanUserPermissionsCache($userid=null){
        return UserModel::removePermissionCacheStatically($userid) ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
    }

}
