<?php
namespace app\member\model;

use allensnape\model\BaseModel;

abstract class MemberBaseModel extends BaseModel{

    /**
     * 插入到数据库之前初始化数据
     * @param boolean:autoGenId         是否自动生成32位字符串的id
     * @param boolean/array:allowField  是否过滤数据库字段
     */
    public function beforeSave($autoGenId=true, $allowField=true){
        if($autoGenId) $this->genID();
        return $this->allowField($allowField);
    }

    /**
     * 更新到数据库之前的操作
     * @param boolean:returnData 是否直接返回$this->getData()的数据, 否则就返回$this
     */
    public function beforeEdit($returnData=true){
        return $returnData === true ? $this->getData() : $this;
    }

    /**
     * 自定义初始化
     */
    protected function initialize()
    {
        // 需要调用`Model`的`initialize`方法
        parent::initialize();
        // TODO: 自定义的初始化
    }
}