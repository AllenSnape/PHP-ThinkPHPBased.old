<?php
namespace app\admin\model;

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

}