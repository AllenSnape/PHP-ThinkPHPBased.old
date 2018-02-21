<?php
namespace app\member\controller;

use think\Session;

use app\member\model\Member;

use allensnape\controller\BaseController;
use allensnape\utils\StringUtil;

class Index extends BaseController{

    /**
     * 验证码在session中的key
     */
    const VERIFY_CODE_SESSION_CODE = 'memberLoginCodeSession_20180221';
    
    /**
     * 默认跳转登陆界面
     */
    public function index($field=null){
        return $this->redirect(config('view_replace_str')['__ROOT__'].'/index.php/member/index/loginPage');
    }

    /**
     * 跳转登陆页面
     */
    public function loginPage(){
        return $this->fetch('index/login');
    }

    /**
     * 获取验证码
     */
    public function getCode() {
        $this->getCodeImage(6, 80, 25, self::VERIFY_CODE_SESSION_CODE);
    }
    
    // 登录
    public function login($username=null, $password=null, $code=null)
    {
        // 如果当前session中有客户, 则先踢出该客户
        Session::delete(MemberBaseController::MEMBER_SESSION_CODE);
        // 检查输入数据
        if(!StringUtil::hasText($username)){
            return $this->json_error('请输入账号!');
        }
        else if(!preg_match('#[\w\d_]{6,16}#i', $username)){
            return $this->json_error('账号格式错误!');
        }
        else if(StringUtil::isEmpty($password)){
            return $this->json_error('请输入密码!');
        }
        else if(strlen($password) < 6){
            return $this->json_error('密码格式错误!');
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

        // 查询客户是否存在
        $member = Member::get(['username' => $username, 'password'=>Member::getSaltedPasswordStatically($password)]);
        if(is_null($member)){
            return $this->json_error('账号或密码错误!');
        }
        // 检查是否被禁用
        else if($member['status'] != 0){
            $statusMsg = Member::MEMBER_STATUS_MAP[$member['status']];
            return $this->json_error('您的账号'.(is_null($statusMsg) ? '异常' : $statusMsg).', 请联系客服处理!');
        }

        // 设置session中已登录的客户
        Session::set(MemberBaseController::MEMBER_SESSION_CODE, $member);

        return $this->json_success('Welcome '.$member['name'].'!');
    }

}
