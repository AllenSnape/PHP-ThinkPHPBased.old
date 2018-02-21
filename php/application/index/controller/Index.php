<?php
namespace app\index\controller;

use allensnape\controller\BaseController;

class Index extends BaseController{
    
    public function index($field=null){
        //return $this->redirect(config('view_replace_str')['__ROOT__'].'/index.php/admin/index/loginPage');
        return 'welcome!';
    }

}
