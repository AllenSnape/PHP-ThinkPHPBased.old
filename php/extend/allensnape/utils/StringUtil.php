<?php
namespace allensnape\utils;

class StringUtil{

    // 获取加密的密码
    public static function getSaltedPassword($source=null, $salt=null, $round=1024)
    {
        if(is_null($source)) throw new \Exception('source password can\'t be null.');
        if(is_null($salt)) $salt = rand(0, getrandmax());
        for($i = 0; $i < $round; $i++){
            $source = md5($source.($i != 0 ? md5($salt) : ''));
        }
        return $source.md5($source.$salt);
    }

    // 获取没有-的uuid
    public static function genID($prefix='', $suffix='')
    {
        return $prefix . md5(uniqid(mt_rand(), true)).$suffix;
    }

    // 判断字符串是否为手机号格式
    public static function isMobile($mobile){
        return preg_match('/^1[3|4|5|6|7|8|9]\d{9}$/', $mobile);
    }

    // 判断字符串是否为null或为空字符串
    public static function isEmpty($str){
        return is_null($str) || $str === '';
    }

    // 判断字符串是否包含内容(不包括不可见字符\s)
    public static function hasText($str){
        return !is_null($str) && preg_match('/^\S+$/', preg_replace('/\s/', '', $str));
    }

}