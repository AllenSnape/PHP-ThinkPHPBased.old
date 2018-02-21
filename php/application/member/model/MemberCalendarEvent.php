<?php
namespace app\member\model;

class MemberCalendarEvent extends MemberBaseModel{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'as_member_calendar_event';

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
        $model
        ->limitLength(['name', ], 64)
        ->limitLength(['content', ], 65535)
        ->limitLength(['remark', ], 3072)
        ->parseFieldsInArray(['content_type'], [0, 1, 2, 3])
        ->parseFieldsInArray(['disabled'], [0, 1]);
    }

}