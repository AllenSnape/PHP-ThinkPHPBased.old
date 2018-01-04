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
        if($request->module() == 'admin'){
            $user = Session::get($this::USER_SESSION_CODE);
            if($request->controller() != 'Index'){
                if(is_null($user)){
                    $this->errorJump('请先登录!', 401, 'index/loginPage');
                }
                else{
                    // 访问记录
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

                    // 检查当前管理员是否权限访问请求的action
                    // 拼接当前访问的标识符(module:controller:action)
                    $rqdPermission = $request->module().':'.$request->controller().':'.$request->action();
                    // 检查数据库数据
                    if($user->hasPermission($rqdPermission) === false){
                        // 访问记录设置exception用于回溯
                        $userlog['exception'] = '拒绝访问!';
                        $userlog->save();

                        $this->errorJump('您无权访问, 如有疑问请联系超级管理员处理!', 403, null);
                    }

                    // 保存访问记录
                    $userlog->save();
                }
            }
        }
        
    }
    
}