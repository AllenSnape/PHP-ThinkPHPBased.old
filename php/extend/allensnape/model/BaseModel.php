<?php
namespace allensnape\model;

use think\Model;
use think\Request;
use allensnape\utils\StringUtil;

abstract class BaseModel extends Model
{

    // 默认的分页数据
    const pageNum = 1;
    const pageSize = 15;

    var $pageNum = self::pageNum;
    var $pageSize = self::pageSize;
    var $total = 0;

    /**
     * 数据库查询分页
     * 自动读取请求数据中的参数
     * @param integer:pageNum 页码
     * @param integer:pageSize 每页数量
     * @return $this
     */
    public function startPage($pageNum=null, $pageSize=null){
        // 请求内容
        $request = Request::instance();
        // 分页数据
        $pageNum = is_null($pageNum) || !is_numeric($pageNum) ? $request->param('pageNum') : $pageNum;
        $this->pageNum = is_null($pageNum) || !is_numeric($pageNum) ? self::pageNum : $pageNum;
        $pageSize = is_null($pageSize) || !is_numeric($pageSize) ? $request->param('pageSize') : $pageSize;
        $this->pageSize = is_null($pageSize) || !is_numeric($pageSize) ? self::pageSize : $pageSize;
        $this->page($this->pageNum, $this->pageSize);
        
        return $this;
    }

    /**
     * 数据库查询排序
     * @param array:allows 允许排序的字段数组
     * 自动读取请求数据中的参数
     * @param porder 排序字段
     * @param psort 排序方式
     * @param porderby 多字段排序时的sql排序代码
     * @return $this
     */
    public function orderBy($allows=[]){
        // 请求内容
        $request = Request::instance();
        // 单字段排序数据 - 优先处理
        $porder = $request->param('porder');
        $porder = StringUtil::hasText($porder) ? $porder : null;
        $psort = $request->param('psort');
        $psort = StringUtil::hasText($psort) && preg_match('/^\s*(asc|desc)\s*$/', strtolower($psort)) ? $psort : '';
        if(!is_null($porder) && !is_null($psort)){
            if(in_array($porder, $allows)) $this->order($porder.' '.$psort);
        }
        // 多字段排序
        else{
            $porderby = $request->param('porderby');
            $porderby = StringUtil::hasText($porderby) ? $porderby : null;
            if(!is_null($porderby) && in_array($porderby, $allows)) $this->order($porderby);
        }
        return $this;
    }

    /**
     * 获取格式化的列表数据
     * @param array:searchableFields       可搜索的字段 @see $this->initSearchableFieldsArray(...)
     * @param array:orderableFields        可排序字段
     * @param array:views                  视图查询数据
     * @param string:groupby               分组数据
     */
    public function getStandardPagedArrayList($searchableFields=[], $orderableFields=[], $views=[], $groupby=null){
        // 先获取总共查询的数据
        $this->initViews($views)->initSearchableFieldsArray($searchableFields);
        if(StringUtil::hasText($groupby)) $this->group($groupby);
        $this->total = $this->count('1');

        $this->initViews($views)->initSearchableFieldsArray($searchableFields);
        if(StringUtil::hasText($groupby)) $this->group($groupby);
        $list = $this->orderBy($orderableFields)->startPage()->select();
        return self::getStandardArrayListStatically($this->pageNum, $this->pageSize, $this->total, $list);
    }

    /**
     * 初始化搜索字段
     * @param array:searchableFields       可搜索的字段的集合, 例如[[[可搜索字段0, 可搜索字段1, ...], '值前缀', '值后缀', '检索方式: LIKE, =, ...', '直接进行匹配的值'], ...]
     */
    public function initSearchableFieldsArray($searchableFields){
        foreach($searchableFields as $fieldsArray){
            if(isset($fieldsArray[0]) && is_array($fieldsArray[0])) $this->initSearchableFields(
                $fieldsArray[0], 
                isset($fieldsArray[1]) && !is_null($fieldsArray[1]) ? $fieldsArray[1] : '%', 
                isset($fieldsArray[2]) && !is_null($fieldsArray[2]) ? $fieldsArray[2] : '%', 
                isset($fieldsArray[3]) && !is_null($fieldsArray[3]) ? $fieldsArray[3] : 'LIKE', 
                isset($fieldsArray[4]) && !is_null($fieldsArray[4]) ? $fieldsArray[4] : null
            );
        }
        return $this;
    }

    /**
     * 初始化连表数据
     * @param array:views       视图查询数据, 至少两个参数. 例如: [['主表的别名', '主表检索的字段'], ['连接的表模型','检索的字段','条件sql', '连表方式, INNER、LEFT、RIGHT等']], 
     *                    还有其余方式请参照https://www.kancloud.cn/manual/thinkphp5/156576 → 数据库 → 查询构造器 → 视图查询
     */
    public function initViews($views=[]){
        foreach($views as $view){
            if(isset($view[3])){
                $this->view($view[0], $view[1], $view[2], $view[3]);
            }else if(isset($view[2])){
                $this->view($view[0], $view[1], $view[2]);
            }else if(isset($view[1])){
                $this->view($view[0], $view[1]);
            }
        }
        return $this;
    }

    /**
     * 输入格式化的列表
     * @param integer:pageNum       页码
     * @param integer:pageSize      每页数量
     * @param integer:total         总共条数
     * @param array:list          列表数据
     * @return 格式化的列表
     */
    public static function getStandardArrayListStatically($pageNum=self::pageNum, $pageSize=self::pageSize, $total=0, $list=[]){
        return [
            'pageNum'       =>          $pageNum,
            'pageSize'      =>          $pageSize,
            'total'         =>          $total,
            'rows'          =>          $list
        ];
    }
    
    /**
     * 给id生成没有"-"的uuid
     * @return $this
     */
    public function genID(){
        $this['id'] = StringUtil::genID();
        return $this;
    }

    /**
     * 初始化可搜索字段
     * 自动判断字段是否为null, 不为null则添加where的like搜索
     * @param array:searchableFields 设置判断的字段; key如果不为数字则作为搜索字段, value作为从$searchableFields读取值的key
     * @param string:searchPrefix 搜索字段的值的前缀
     * @param string:searchSuffix 搜索字段的值的后缀
     * @param string:defaultValue 默认检索的值
     * @return $this
     */
    public function initSearchableFields($searchableFields=[], $searchPrefix='%', $searchSuffix='%', $compareType='LIKE', $defaultValue=null){
        // 请求内容
        $request = Request::instance();
        foreach($searchableFields as $key=>$value){
            $param = is_null($defaultValue) ? $request->param($value) : $defaultValue;
            if(StringUtil::hasText($param)){
                $this->where(is_numeric($key) ? $value : $key, $compareType, $searchPrefix.''.$param.''.$searchSuffix);
            }
        }
        return $this;
    }

    /**
     * 限制字段长度
     * @param array:names 操作的字段集合
     * @param integer:length 限制的长度
     * @return $this
     */
    public function limitLength($names=['remark'], $length=200){
        foreach($names as $key=>$name)
            $this[$name] = is_null($this[$name]) ? $this[$name] : substr($this[$name], 0, $length);
        return $this;
    }

    /**
     * 限制字段值在提供的数组元素内
     * @param array:fields 限制的字段
     * @param array:values 限制的值
     */
    public function parseFieldsInArray($fields=['disabled'], $values=[0, 1]){
        foreach($fields as $fk=>$field)
            if(!in_array($this[$field], $values)) unset($this[$field]);
        return $this;
    }

    /**
     * 获取当前模型的数据库名称
     */
    public function getTable(){
        return $this->table;
    }

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }
}