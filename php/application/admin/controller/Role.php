<?php
namespace app\admin\controller;

use app\admin\model\Role as RoleModel;

class Role extends AdminBaseController{

    /**
     * 角色列表
     */
    public function listPage(){
        $model = new RoleModel($_POST);
        $this->assign('title', '角色列表');
        $this->assign('defaultParams', '?porder=create_time&psort=desc');
        $this->assign('data', $model->getStandardPagedArrayList([[['name']], [['disabled'], '', '', '=']], ['create_time']));
        return $this->fetch('role/list');
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

        return $old->allowField(['name', 'remark', 'update_by'])->save($model->beforeEdit(), ['id'=>$model['id']]) ? $this->json_success('修改成功!') : $this->json_error('无可修改内容!');
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

        return $old->allowField(['disabled', 'update_by'])->save($model->beforeEdit(), ['id'=>$model['id']]) ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
    }

}