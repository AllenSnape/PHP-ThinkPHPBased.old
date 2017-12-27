<?php
namespace app\admin\controller;

use app\admin\model\Menu as MenuModel;

class Menu extends AdminBaseController{

    public function demo(){
        return $this->json_success('哈哈', MenuModel::getFormattedMenus());
    }

    /**
     * 菜单列表
     */
    public function listPage(){
        $model = new MenuModel($_POST);
        $this->assign('title', '菜单列表');
        $this->assign('defaultParams', '?');
        $this->assign('data', ['rows'=>$model->where(['disabled'=>0])->order('`sort` ASC, create_time DESC')->select()]);
        return $this->fetch('menu/list');
    }

    /**
     * 添加菜单
     */
    public function add(){
        $model = new MenuModel($_POST);
        // 检查参数
        if(!$this->hasText($model['name'])){
            return $this->json_error('请输入菜单名称!');
        }

        return $model->beforeSave()->save() ? $this->json_success('添加成功!') : $this->json_error('添加失败!');
    }

    /**
     * 修改菜单
     */
    public function edit(){
        $model = new MenuModel($_POST);
        // 检查参数
        if(!$this->hasText($model['id'])){
            return $this->json_error('请选择要修改的菜单!');
        }else if(!$this->hasText($model['name'])){
            return $this->json_error('请输入菜单名称!');
        }

        // 检查修改的菜单是否存在
        $old = MenuModel::get($model['id']);
        if(is_null($old)) return $this->json_error('修改的菜单不存在!');

        return $old->allowField([
            'pid',
            'name',
            'href',
            'sort',
            'icon',
            'hidden',
            'remark',
            'permission',
            'update_by',
        ])->save($model->beforeEdit(), ['id'=>$model['id']]) ? $this->json_success('修改成功!') : $this->json_error('无可修改内容!');
    }

    /**
     * 操作菜单
     */
    public function dis(){
        $model = new MenuModel($_POST);
        // 检查参数
        if(!$this->hasText($model['id'])){
            return $this->json_error('请选择要修改的菜单!');
        }else if(!$this->is0Or1($model['disabled'])){
            return $this->json_error('操作参数错误!');
        }

        // 检查操作的菜单是否存在
        $old = MenuModel::get($model['id']);
        if(is_null($old)) return $this->json_error('修改的菜单不存在!');

        return $old->allowField(['disabled', 'update_by'])->save($model->beforeEdit(), ['id'=>$model['id']]) ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
    }

}