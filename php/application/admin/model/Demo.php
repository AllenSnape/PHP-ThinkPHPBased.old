<?php
namespace app\admin\model;

class Demo extends AdminBaseModel
{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'demo';

    // 默认主键
    protected $pk = 'id';
    
    // 只读字段
    protected $readonly = ['create_time', 'create_by'];

    protected static function init()
    {
        Demo::beforeInsert(function ($demo) {
            Demo::filterFields($demo);
        });
        Demo::beforeUpdate(function ($demo) {
            Demo::filterFields($demo);
        });
    }

    protected static function filterFields($demo){
        $demo->limitLength(['remark'], 255);
    }

}