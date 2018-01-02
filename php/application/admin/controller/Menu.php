<?php
namespace app\admin\controller;

use app\admin\model\Menu as MenuModel;

class Menu extends AdminBaseController{

    /**
     * 菜单列表
     */
    public function listPage(){
        $model = new MenuModel($_POST);
        $this->assign('title', '菜单列表');
        $this->assign('defaultParams', '?');
        $this->assign('data', ['rows'=>MenuModel::setCacheMenus()]);
        return $this->fetch('menu/list');
    }

    /**
     * 菜单json列表, 用于自动补齐的搜索
     */
    public function listJson(){
        $model = new MenuModel($_POST);
        return $this->json_success('获取成功!', $model->getStandardPagedArrayList([[['name', 'href']], [['disabled'], '', '', '=']], ['create_time', 'sort']));
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
        
        // 如果父菜单id存在, 则检查是否存在
        if($this->hasText($model['pid'])){
            $pmenu = MenuModel::get($model['pid']);
            if(is_null($pmenu)){
                return $this->json_error('选择的父菜单不存在!');
            }
        }

        return $model->beforeSave()->save() && MenuModel::setCacheMenus() ? $this->json_success('添加成功!') : $this->json_error('添加失败!');
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
        
        // 如果父菜单id存在, 则检查是否存在
        if($this->hasText($model['pid'])){
            $pmenu = MenuModel::get($model['pid']);
            if(is_null($pmenu)){
                return $this->json_error('选择的父菜单不存在!');
            }
        }

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
        ])->save($model->beforeEdit(), ['id'=>$model['id']]) && MenuModel::setCacheMenus() ? $this->json_success('修改成功!') : $this->json_error('无可修改内容!');
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

        return $old->allowField(['disabled', 'update_by'])->save($model->beforeEdit(), ['id'=>$model['id']]) && MenuModel::setCacheMenus() ? $this->json_success('操作成功!') : $this->json_error('操作失败!');
    }

}