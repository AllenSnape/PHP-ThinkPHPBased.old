<?php
namespace app\admin\model;

use think\Session;

use app\admin\controller\AdminBaseController;

use allensnape\utils\StringUtil;

class User extends AdminBaseModel
{

    const PASSWORD_DEFAULT_ENCRYPT_ROUND = 2048;
    
    const PASSWORD_SALT_WORDS = 'userPasswordSalt';

    // 设置当前模型对应的完整数据表名称
    protected $table = 'user';

    // 默认主键
    protected $pk = 'id';
    
    // 只读字段
    protected $readonly = ['create_by', 'create_time'];

    protected static function init()
    {
        User::beforeInsert(function ($user) {
            User::filterFields($user);
        });
        User::beforeUpdate(function ($user) {
            User::filterFields($user);
        });
    }

    protected static function filterFields($user){
        $user->limitLength()->limitLength(['name'], 16);
    }
    
    /**
     * 获取当前session的管理员
     * @return app\admin\model\User
     */
    public static function getCurrentUser(){
        return Session::get(AdminBaseController::USER_SESSION_CODE);
    }
    
    /**
     * 获取当前用户明文密码加密后的字符串
     * @return string       加密后的字符串
     */
    public function getSaltedPassword(){
        return self::getSaltedPasswordStatically($this['password']);
    }

    /**
     * 获取加密后的密码
     * @param string $password      要加密的密码
     * @return string       加密后的字符串
     */
    public static function getSaltedPasswordStatically($password=null){
        return StringUtil::getSaltedPassword($password, self::PASSWORD_SALT_WORDS, self::PASSWORD_DEFAULT_ENCRYPT_ROUND);
    }

}