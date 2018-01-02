<?php
namespace app\admin\controller;

use think\Session;
use think\Request;

use app\admin\model\User;
use app\admin\model\UserLog;

use allensnape\controller\BaseController;

class AdminBaseController extends BaseController{

    const USER_SESSION_CODE = 'userSessionCode_allensnape';
    
    public function _initialize() {
        
        $request = Request::instance();
        if($request->module() == 'admin')
        {
            $user = Session::get($this::USER_SESSION_CODE);
            if($request->controller() != 'Index'){
                if(is_null($user)){
                    if($request->isAjax() || isset($request->param()['ajax']))
                    {
                        $this->error($this->json_normal(401, '请先登录!')->getContent(), null, '', 3, ['content-type'=>'application/json; charset=utf-8'], true);
                    }
                    else
                    {
                        $this->error('请先登录!', 'index/loginPage', 3);
                    }
                }
                else
                {
                    $userlog = new UserLog([
                        'user_id'     => $user['id'],
                        'type'        => '访问',
                        'title'       => '请求'.$request->path(),
                        'content'     => json_encode($request->param()),
                        'remote_ip'   => $this->get_client_ip(),
                        'request_uri' => $request->url(),
                        'user_agent'  => $this->get_client_browser(' '),
                        'method'      => $request->method(),
                        'create_time' => time()
                    ]);
                    $userlog->save();
                }
            }
        }
        
    }
    
}