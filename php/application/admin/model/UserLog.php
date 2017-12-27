<?php
namespace app\admin\model;

class UserLog extends AdminBaseModel
{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'user_log';

    // 默认主键
    protected $pk = 'id';

    protected static function init()
    {
        UserLog::beforeInsert(function ($userlog) {
            $userlog->limitLength(['request_uri'])->limitLength(['type'], 20)->limitLength(['title', 'remote_ip', 'method'], 64);
        });
    }
    
}