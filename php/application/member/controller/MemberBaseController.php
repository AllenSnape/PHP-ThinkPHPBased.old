<?php
namespace app\member\controller;

use think\Session;
use think\Request;

use allensnape\controller\BaseController;

class MemberBaseController extends BaseController{

    const MEMBER_SESSION_CODE = 'memberSessionCode_allensnape';
    
    public function _initialize() {
        
        $request = Request::instance();
        if($request->module() == 'member'){
            $member = Session::get($this::MEMBER_SESSION_CODE);
            if($request->controller() != 'Index'){
                if(is_null($member)){
                    $this->errorJump('请先登录!', 401, 'index/loginPage');
                }else{
                    
                }
            }
        }
        
    }
    
}