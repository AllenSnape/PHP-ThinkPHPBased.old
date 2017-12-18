<?php
namespace app\admin\controller;

use app\admin\model\Demo;

class Home extends AdminBaseController{

    public function home(){
        $this->assign('title', '管理系统');
        return $this->fetch('home/home');
    }

    public function demoListPage(){
        $demo = new Demo();
        //$this->assign('data', $demo->getStandardPagedArrayList([[['name']], [['disabled'], '', '', '=']], ['create_time', 'update_time']));
        $this->assign('data', ['rows'=>[], 'pageNum'=>1, 'pageSize'=>15, 'total'=>0]);
        return $this->fetch('demo/list');
    }

}