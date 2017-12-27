<?php
namespace app\admin\model;

class UserLog extends AdminBaseModel{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'as_user_log';

    // 默认主键
    protected $pk = 'id';
    // 关闭自动写入update_time字段
    protected $updateTime = false;
    // 只读字段
    protected $readonly = ['user_id', 'create_time'];

    protected static function init()
    {
        self::beforeInsert(function ($model) {
            $model->
            limitLength(['request_uri'])->
            limitLength(['type'], 20)->
            limitLength(['content', 'user_agent', 'exception'], 65535)->
            limitLength(['title', 'remote_ip', 'method'], 64);
        });
    }
    
}