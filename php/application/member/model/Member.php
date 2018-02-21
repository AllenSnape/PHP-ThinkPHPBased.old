<?php
namespace app\member\model;

use think\Session;

use app\member\controller\MemberBaseController;

use allensnape\utils\StringUtil;

class Member extends MemberBaseModel{

    // 密码加密添加的盐
    const PASSWORD_SALT_WORDS = 'asmember';

    // 密码加密次数
    const PASSWORD_DEFAULT_ENCRYPT_ROUND = 1024;

    /**
     * 客户账户状态字典
     */
    const MEMBER_STATUS_MAP = [
        -3      =>      '冻结',
        -2      =>      '禁封',
        -1      =>      /* 删除 */'异常',
        0       =>      '正常'
    ];

    // 设置当前模型对应的完整数据表名称
    protected $table = 'as_member';

    // 默认主键
    protected $pk = 'id';
    
    // 只读字段
    protected $readonly = ['create_by', 'create_time'];

    protected static function init()
    {
        self::beforeInsert(function ($model) {
            self::filterFields($model);
        });
        self::beforeUpdate(function ($model) {
            self::filterFields($model);
        });
    }

    protected static function filterFields($model){
        $model->limitLength()->limitLength(['name', 'mobile', 'qq'], 20)
        ->limitLength(['nickname', 'wechat', 'wx_openid', 'wx_unionid', 'wx_access_token'], 32)
        ->limitLength(['idcode', ], 64)
        ->limitLength(['face', 'token', 'sign', 'remark'], 3072)
        ->parseFieldsInArray(['sex'], [0, 1, 2])
        ->parseFieldsInArray(['status'], [-3, -2, -1, 0]);
    }

    /**
     * 加密给予的密码
     * @param string:password 要加密的密码, 如果不存在则从当前实例中获取password字段, 如果password字段不存在则返回null
     */
    public function getSaltedPassword($password){
        return self::getSaltedPasswordStatically(is_null($password) ? $this['password'] : $password);
    }

    /**
     * 获取当前登陆的客户
     * @return 如果当前session有登陆的客户就返回客户实例; 没有就返回null
     */
    public static function getCurrentMember(){
        return Session::get(MemberBaseController::MEMBER_SESSION_CODE);
    }

    /**
     * 加密给予的密码
     * @param string:password 要加密的密码, 为null时返回null
     */
    public static function getSaltedPasswordStatically($password){
        if(is_null($password)) return null;
        return StringUtil::getSaltedPassword($password, self::PASSWORD_SALT_WORDS, self::PASSWORD_DEFAULT_ENCRYPT_ROUND);
    }

}