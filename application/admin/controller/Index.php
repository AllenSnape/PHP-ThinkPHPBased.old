<?php
namespace app\admin\controller;

use think\View;
use think\Request;
use think\Session;

use app\admin\model\User;
use app\admin\model\UserLog;

use allensnape\utils\StringUtil;
use allensnape\controller\BaseController;

class Index extends AdminBaseController{

    const VERIFY_CODE_SESSION_CODE = "adminLoginVerifyCodeSessionid_20170925_allensnape";
    
    // 登录
    public function login($username=null, $password=null, $code=null)
    {
        // 如果当前session中有用户, 则先踢出该用户
        Session::delete(AdminBaseController::USER_SESSION_CODE);
        // 检查输入数据
        if(is_null($username) || $username == ''){
            return $this->json_error('请输入账号!');
        }
        else if(is_null($password) || $password == '')
        {
            return $this->json_error('请输入密码!');
        }
        // 校验验证码 - DEBUG状态跳过
        if(config('app_debug') == false){
            if(is_null($code) || $code == ''){
                return $this->json_error('请输入验证码!');
            }
            else if($code !== Session::get(self::VERIFY_CODE_SESSION_CODE)){
                Session::delete(self::VERIFY_CODE_SESSION_CODE);
                return $this->json_error('验证码错误!');
            }
        }
        // 清除session中当前的验证码
        Session::delete(self::VERIFY_CODE_SESSION_CODE);

        // 查询用户是否存在
        $user = User::get(['username' => $username, 'password'=>StringUtil::getSaltedPassword($password, User::PASSWORD_SALT_WORDS, User::PASSWORD_DEFAULT_ENCRYPT_ROUND)]);
        if($user == null){
            return $this->json_error('账号或密码错误!');
        }
        // 检查是否被禁用
        else if($user['disabled'] == 1){
            return $this->json_error('您的账号已被禁用, 请联系超级管理员处理!');
        }
        // 设置session中已登录的用户
        Session::set(AdminBaseController::USER_SESSION_CODE, $user);

        // 添加登陆记录
        $request = Request::instance();
        $userlog = new UserLog([
            'user_id'     => $user['id'],
            'type'        => '登陆',
            'title'       => '请求'.$request->path(),
            'content'     => json_encode($request->param()),
            'remote_ip'   => $this->get_client_ip(),
            'request_uri' => $request->url(),
            'user_agent'  => $this->get_client_browser(),
            'method'      => $request->method(),
            'create_time' => time()
        ]);
        $userlog->save();

        return $this->json_success('Welcome '.$user['name'].'!');
    }
    
    // 获取管理员登录验证码
    public function getCode() {
        $this->getCodeImage(6, 80, 25, self::VERIFY_CODE_SESSION_CODE);
    }

    /**
     * 登录页面
     */
    public function loginPage(){
        return $this->fetch('home/login');
    }

}