<?php
namespace app\admin\model;

class UserRole extends AdminBaseModel{

    const TABLE_NAME = 'as_user_role';

    // 设置当前模型对应的完整数据表名称
    protected $table = self::TABLE_NAME;

    // 默认主键
    protected $pk = ['user_id', 'role_id'];
    // 关闭自动写入update_time字段
    protected $updateTime = false;
    // 关闭自动写入create_time字段
    protected $createTime = false;

}