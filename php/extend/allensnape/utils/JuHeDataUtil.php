<?php
namespace allensnape\utils;

/**
 * 聚合数据API工具包
 */
class JuHeDataUtil{

    /**
     * 聚合数据AppKey
     */
    const APP_KEY = '3109771d9214b1eea515f5c2b5409926';

    /**
     * 发送get或者post请求
     * @param string $url               请求的连接
     * @param string $post              post体, 如果不存在则默认为get请求
     * @return string 服务器相应的数据
     */
    private static function curlRequest($url, $post=''){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        return $data;
    }

    /**
     * 根据传入日期返回当天详细信息
     * @param string:date 指定日期, 格式为YYYY-MM-DD, 如月份和日期小于10, 则取个位, 如: 2012-1-1
     */
    public static function calendarDay($date=null){
        // 检查参数
        if(!StringUtil::hasText($date) || !preg_match('/^\d{4}-(1|2|3|4|5|6|7|8|9|10|11|12)-\d{1,2}$/', $date)) throw new \Exception('日期参数格式错误!');

        // 查询API
        $result = self::curlRequest('http://v.juhe.cn/calendar/day?date='.$date.'&key='.self::APP_KEY);

        if(!StringUtil::hasText($date)) return null;

        // 格式化返回数据
        $result = json_decode($result, true);
        // 检查返回参数
        if(isset($result['error_code']) && $result['error_code'] == 0 &&
            isset($result['result']) && isset($result['result']['data'])){
            return $result['result']['data'];
        }else{
            throw new \Exception(isset($result['reason']) ? $result['reason'] : '查询出错! 无错误信息');
        }
    }

}