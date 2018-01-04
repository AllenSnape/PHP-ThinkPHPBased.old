<?php
namespace app\admin\model;

use think\Db;
use think\Cache;
use think\Session;

use app\admin\controller\AdminBaseController;

use allensnape\utils\StringUtil;
use allensnape\controller\BaseController;

class User extends AdminBaseModel{

    /**
     * 管理员密码加密循环次数
     */
    const PASSWORD_DEFAULT_ENCRYPT_ROUND = 2048;
    
    /**
     * 密码加密时添加的调料
     */
    const PASSWORD_SALT_WORDS = 'userPasswordSalt';

    /**
     * 管理员权限缓存标签
     */
    const USER_PERMISSIONS_TAG = 'userPermissionsTag';

    // 设置当前模型对应的完整数据表名称
    const TABLE_NAME = 'as_user';
    protected $table = self::TABLE_NAME;

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

    /**
     * 踢出修改的管理员 - 删除对应的session文件
     * @param string:id 管理员id; 不得为空
     */
    public static function kickout($id=null){
        $thinkSessionFlag = config('session.prefix');
        $id = is_null($id) && 
            isset($_SESSION[$thinkSessionFlag]) &&
            isset($_SESSION[$thinkSessionFlag][AdminBaseController::USER_SESSION_CODE]) && 
            isset($_SESSION[$thinkSessionFlag][AdminBaseController::USER_SESSION_CODE]['id']) ? 
                $_SESSION[$thinkSessionFlag][AdminBaseController::USER_SESSION_CODE]['id'] : $id;
        if(is_null($id)) return false;
        foreach(BaseController::getAllSessionIDs() as $index=>$sessionId){
            session_id($sessionId);
            if(!isset($_SESSION)){
                session_start();
            }
            if(isset($_SESSION[$thinkSessionFlag]) && isset($_SESSION[$thinkSessionFlag][AdminBaseController::USER_SESSION_CODE]) 
                && isset($_SESSION[$thinkSessionFlag][AdminBaseController::USER_SESSION_CODE]['id'])){
                if($id === $_SESSION[$thinkSessionFlag][AdminBaseController::USER_SESSION_CODE]['id']){
                    session_abort();
                    @unlink(session_save_path().DS.'sess_'.$sessionId);
                    return true;
                }
            }
            session_abort();
        }
        return true;
    }

    /**
     * 将权限标识符(菜单访问权)载入缓存中
     * 缓存标签为self::USER_PERMISSIONS_TAG
     * 缓存key为管理员id
     * @param string:id 管理员id; 不得为空
     */
    public static function setPermissionsStatically($id=null){
        if(!StringUtil::hasText($id)){
            throw new \Exception('管理员id不得为空!');
        }
        $permissions = Db::query(
            'SELECT 
                m.* 
            FROM '.Menu::TABLE_NAME.' m
            WHERE 
             m.id IN (SELECT rm.menu_id FROM '.RoleMenu::TABLE_NAME.' rm LEFT JOIN '.UserRole::TABLE_NAME.' ur ON ur.role_id = rm.role_id WHERE ur.user_id = ? GROUP BY rm.menu_id)
             AND m.disabled = 0',// AND m.permission IS NOT NULL AND m.permission <> \'\'
            [$id]
        );

        return Cache::tag(self::USER_PERMISSIONS_TAG)->set($id, $permissions, 0);
    }

    /**
     * 将权限列表(菜单访问权限列表)载入缓存中
     * @param string:id 管理员id; 为空时自动使用当前实例的id作为参数
     */
    public function setPermissions($id=null){
        return self::setPermissionsStatically(StringUtil::hasText($id) ? $id : $this['id']);
    }

    /**
     * 加载缓存中的权限列表
     * @param string:id         管理员id; 不得为空
     */
    public static function loadPermissionsStatically($id=null){
        if(!StringUtil::hasText($id)){
            throw new \Exception('管理员id不得为空!');
        }

        // 获取缓存中的数据
        $permissions = Cache::tag(self::USER_PERMISSIONS_TAG)->get($id);
        // 如果缓存中不存在, 则重新加载
        if(is_null($permissions) || $permissions === false) self::setPermissions($id);

        // 再次获取后返回
        return Cache::tag(self::USER_PERMISSIONS_TAG)->get($id);
    }

    /**
     * 加载缓存中的权限列表
     * @param string:id 管理员id; 为空时自动使用当前实例的id作为参数
     */
    public function loadPermissions($id=null){
        return self::loadPermissionsStatically(StringUtil::hasText($id) ? $id : $this['id']);
    }

    /**
     * 检查id对应管理员是否拥有此权限
     * @param string:id         管理员id; 不得为空
     * @param string:permission 权限标识符; 不得为空
     * @return bool true: 允许访问; false: 无权访问(403)
     */
    public static function hasPermissionStatically($id=null, $permission=null){
        // 检查参数
        if(!StringUtil::hasText($id)){
            return false;
        }else if(!StringUtil::hasText($permission)){
            return true;
        }
        // 加载权限
        $permissions = self::loadPermissionsStatically($id);

        // 检查权限标识符是否包含于其中
        $permission = strtolower($permission);
        foreach($permissions as $pk=>$permis){
            if(strtolower($permis['permission']) == $permission){
                return true;
            }
        }

        return false;
    }

    /**
     * 检查id对应管理员是否拥有此权限
     * @param string:permission 权限标识符; 为空时直接返回true
     * @param string:id 管理员id; 为空时自动使用当前实例的id作为参数; 该参数排后是为了更方便的添加permission参数
     * @return bool true: 允许访问; false: 无权访问(403)
     */
    public function hasPermission($permission=null, $id=null){
        return self::hasPermissionStatically(StringUtil::hasText($id) ? $id : $this['id'], $permission);
    }

    /**
     * 清空权限缓存
     * @param string:id 管理员id; 为空时清除所有
     */
    public static function removePermissionCacheStatically($id=null){
        if(StringUtil::hasText($id))
            return Cache::tag(self::USER_PERMISSIONS_TAG)->rm($id);
        else
            return Cache::clear('tag');
    }

    /**
     * 清空当前实例的权限缓存
     */
    public function removePermissionCache(){
        return self::removePermissionCacheStatically($this['id']);
    }

}