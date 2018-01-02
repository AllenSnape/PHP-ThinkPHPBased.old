<?php
namespace app\admin\model;

use allensnape\utils\StringUtil;

class Role extends AdminBaseModel{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'as_role';

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
        $model->limitLength()->limitLength(['name'], 16)
        ->parseFieldsInArray();
    }

    /**
     * 踢出属于该角色的所有用户
     */
    public static function kickoutBelongs($roleid=null){
        // 检查参数
        if(!StringUtil::hasText($roleid)){
            throw new \Exception('操作的角色参数错误!');
        }
        
        // 检查操作的角色是否存在 - NOTE: 不需要检查, 反正数据库不存在的也不会存在对应的用户

        // 获取所有用户id
        $users = UserRole::where(['role_id'=>$roleid])->select();
        foreach($users as $uk=>$user){
            User::kickout($user['user_id']);
        }

        return true;
    }

}