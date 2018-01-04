<?php
namespace app\admin\model;

use think\Db;
use think\Cache;

use allensnape\utils\StringUtil;

class Menu extends AdminBaseModel{

    // 菜单在缓存中的标识符
    const CACHE_KEY = 'menu_cache_key_as';

    // 设置当前模型对应的完整数据表名称
    const TABLE_NAME = 'as_menu';
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
        $model->limitLength(['name', 'remark', 'permission'], 200)->limitLength(['href', 'icon'], 3072)
        ->parseFieldsInArray(['disabled', 'hidden'], [0, 1]);
    }

    /**
     * 获取整理好之后的菜单列表
     * @param array:menus 要被整理的菜单数组, 为空时会自动从数据库中检索(WHERE disabled = 1 ORDER BY sort ASC, create_time DESC)
     */
    public static function getFormattedMenus($menus=null){
        $menus = is_null($menus) ? Db::table(self::TABLE_NAME)->where(['disabled'=>0])->order('sort ASC, create_time DESC')->select() : $menus;
        // 整理后的菜单对象
        $formattedMenus = [];

        // 使用递归判定所有菜单归属
        function hasRelation(&$fm, &$m){
            if($fm['id'] == $m['pid']){
                $m['pmenuName'] = $fm['name'];
                if(isset($fm['subMenus']) && is_array($fm['subMenus'])){
                    array_push($fm['subMenus'], $m);
                    // 根据sort字段, 升序排列
                    $subMenusCount = count($fm['subMenus']);
                    for($i = 0; $i < $subMenusCount-1; $i++){
                        for($j = $i+1; $j < $subMenusCount; $j++){
                            if($fm['subMenus'][$i]['sort'] > $fm['subMenus'][$j]['sort']){
                                $temp = $fm['subMenus'][$i];
                                $fm['subMenus'][$i] = $fm['subMenus'][$j];
                                $fm['subMenus'][$j] = $temp;
                            }
                        }
                    }
                }else{
                    $fm['subMenus'] = [$m];
                }
                return true;
            }else{
                if(isset($fm['subMenus']) && is_array($fm['subMenus'])){
                    foreach($fm['subMenus'] as $k=>&$v){
                        if(hasRelation($v, $m)){
                            return true;
                        }
                    }
                }
            }
            return false;
        }

        // 循环检索
        $lastRemained = count($menus);
        while(true){
            foreach($menus as $mk=>&$menu){
                if(!StringUtil::hasText($menu['pid'])){
                    array_push($formattedMenus, $menu);
                    unset($menus[$mk]);
                }else{
                    foreach($formattedMenus as $pmk=>&$pm){
                        if(hasRelation($pm, $menu)){
                            unset($menus[$mk]);
                        }
                    }
                }
            }
            $remained = count($menus);
            if($lastRemained == $remained){
                if($remained > 0) 
                    foreach($menus as $k=>$m)
                        array_push($formattedMenus, $m);
                break;
            }else{
                $lastRemained = $remained;
            }
        }

        return $formattedMenus;
    }

    /**
     * 将对应的菜单列表放入缓存
     * @param array:menus 放入缓存的菜单数组
     */
    public static function setCacheMenus($menus=null){
        Cache::set(self::CACHE_KEY, is_null($menus) ? self::getFormattedMenus() : $menus, 0);
        return self::getCacheMenus();
    }

    /**
     * 获取缓存中的菜单, 如果不存在, 则重新获取一遍
     */
    public static function getCacheMenus(){
        $menus = Cache::get(self::CACHE_KEY);
        return $menus === false ? self::setCacheMenus() : $menus;
    }

}