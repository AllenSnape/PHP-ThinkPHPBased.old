<?php
namespace app\admin\model;

//use think\Session;
use allensnape\model\BaseModel;

abstract class AdminBaseModel extends BaseModel{

    // 插入到数据库之前初始化数据
    public function beforeSave($autoGenId=true){
        $now = time();
        $this['create_time'] = $now;
        $this['update_time'] = $now;

        /* $loginedAdmin = Session::get(AdminController::USER_SESSION_CODE);
        if($loginedAdmin != null){
            $this['create_by'] = $loginedAdmin['id'];
            $this['update_by'] = $this['create_by'];
        } */

        if($autoGenId) $this->genID();

        return $this;
    }

    // 更新到数据库之前的操作
    public function beforeEdit(){
        $this['update_time'] = time();

        /* $loginedAdmin = Session::get(AdminController::USER_SESSION_CODE);
        if($loginedAdmin != null){
            $this['update_by'] = $loginedAdmin['id'];
        } */

        return $this;
    }

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
}