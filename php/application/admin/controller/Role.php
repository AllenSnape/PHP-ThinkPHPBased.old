<?php
namespace app\admin\controller;

use think\Db;

use app\admin\model\Role as RoleModel;
use app\admin\model\Menu as MenuModel;
use app\admin\model\RoleMenu as RoleMenuModel;

class Role extends AdminBaseController{

    /**
     * 角色列表
     */
    public function listPage(){
        $model = new RoleModel($_POST);
        $this->assign('title', '角色列表');
        $this->assign('defaultParams', '?porder=create_time&psort=desc');
        $this->assign('menus', MenuModel::setCacheMenus());
        $this->assign('data', $model->getStandardPagedArrayList([[['name']], [['disabled'], '', '', '=']], ['create_time']));
        return $this->fetch('role/list');
    }

    /**
     * 角色菜单关联数据
     */
    public function roleMenuListJson($roleid=null){
        if(!$this->hasText($roleid)){
            return $this->json_error('请选择要查看的角色!');
        }
        return $this->json_success('获取成功!', RoleMenuModel::where(['role_id'=>$roleid])->select());
    }

    /**
     * 设置角色菜单
     */
    public function setRoleMenus($roleid=null, $menuids=null){
        // 检查参数
        if(!$this->hasText($roleid)){
            return $this->json_error('请选择要设置的角色!');
        }
        // 检查菜单json
        $menuids = json_decode($menuids, true);
        if(is_null($menuids)){
            return $this->json_error('菜单格式错误!');
        }

        // 检查角色是否存在
        $role = RoleModel::get($roleid);
        if(is_null($role)){
            return $this->json_error('查看的角色不存在!');
        }

        // 添加之前先删除之前的设置
        RoleMenuModel::where(['role_id'=>$role['id']])->delete(true);

        // 过滤菜单, 过滤掉不存在的菜单id
        $menus = MenuModel::where('id', 'in', $menuids)->select();

        if(count($menus) > 0){
            // 拼接批量添加sql
            $sql = 'INSERT INTO '.RoleMenuModel::TABLE_NAME.'(`role_id`, `menu_id`) VALUES ';
            $sqlValues = [];
            // 循环拼接
            foreach($menus as $mk=>$menu){
                $sql .= '(?, ?),';
                array_push($sqlValues, $role['id']);
                array_push($sqlValues, $menu['id']);
            }
            $sql = substr($sql, 0, strlen($sql)-1);
            return Db::execute($sql, $sqlValues) && RoleModel::kickoutBelongs($role['id']) ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
        }

        return $this->json_success('操作成功!');
    }

    /**
     * 添加角色
     */
    public function add(){
        $model = new RoleModel($_POST);
        // 检查参数
        if(!$this->hasText($model['name'])){
            return $this->json_error('请输入角色名称!');
        }

        // 检查角色名称是否存在
        $exists = RoleModel::get(['name'=>$model['name']]);
        if(!is_null($exists)) return $this->json_error('角色名称已存在!');

        return $model->beforeSave()->save() ? $this->json_success('添加成功!') : $this->json_error('添加失败!');
    }

    /**
     * 修改角色
     */
    public function edit(){
        $model = new RoleModel($_POST);
        // 检查参数
        if(!$this->hasText($model['id'])){
            return $this->json_error('请选择要修改的角色!');
        }else if(!$this->hasText($model['name'])){
            return $this->json_error('请输入角色名称!');
        }

        // 检查修改的角色是否存在
        $old = RoleModel::get($model['id']);
        if(is_null($old)) return $this->json_error('修改的角色不存在!');

        // 检查角色名称是否存在
        if($old['name'] != $model['name']){
            $exists = RoleModel::get(['name'=>$model['name']]);
            if(!is_null($exists)) return $this->json_error('角色名称已存在!');
        }

        return $old->allowField(['name', 'remark', 'update_by'])->save($model->beforeEdit(), ['id'=>$model['id']]) && RoleModel::kickoutBelongs($old['id']) ?
            $this->json_success('修改成功!') : $this->json_error('无可修改内容!');
    }

    /**
     * 操作角色
     */
    public function dis(){
        $model = new RoleModel($_POST);
        // 检查参数
        if(!$this->hasText($model['id'])){
            return $this->json_error('请选择要修改的角色!');
        }else if(!$this->is0Or1($model['disabled'])){
            return $this->json_error('操作参数错误!');
        }

        // 检查操作的角色是否存在
        $old = RoleModel::get($model['id']);
        if(is_null($old)) return $this->json_error('修改的角色不存在!');

        return $old->allowField(['disabled', 'update_by'])->save($model->beforeEdit(), ['id'=>$model['id']]) && RoleModel::kickoutBelongs($old['id'])
            ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
    }

}