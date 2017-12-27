<?php
namespace allensnape\utils;

use think\Log;

class XPrintUtil{

    /** 初始化 */
    const INIT                    = '<1B40>';
    
    /** 文字居左 */
    const TEXT_ALIGN_LEFT         = '<1B6100>';
    /** 文字居中 */
    const TEXT_ALIGN_CENTER       = '<1B6101>';
    /** 文字居右 */
    const TEXT_ALIGN_RIGHT        = '<1B6102>';

    /** 正常大小 */
    const FONT_SIZE_NORMAL        = '<1D2100>';
    /** 纵向放大 */
    const FONT_SIZE_DOUBLE_HEIGHT = '<1D2101>';
    /** 横向放大 */
    const FONT_SIZE_DOUBLE        = '<1D2111>';
    /** 整体放大 */
    const FONT_SIZE_DOUBLE_WIDTH  = '<1D2110>';

    const NEW_LINE                = '<0D0A>';

    const URL                     = 'http://115.28.15.113:60002';

    /**
     * 发送post请求
     *
     * @param string $url           请求的链接
     * @param string $post_data     post体
     * @param integer $timeout      超时时间
     * @return string               请求返回的数据
     */
    private static function post($url, $post_data = '', $timeout = 5){//curl
        if(!StringUtil::hasText($url)) throw new \Exception('URL不得为空!');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if($post_data != ''){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        Log::debug('XPrinter - 发送POST请求返回内容'.$file_contents);
        return $file_contents;
    }

    /**
     * 向目标打印机发送打印请求
     *
     * @param string $sn            打印机sn码
     * @param string $content       打印内容
     * @return void
     */
    public static function print_($sn, $content){
        if(!StringUtil::hasText($sn)) throw new \Exception('请输入打印机SN编号!');
        else if(!StringUtil::hasText($content)) throw new \Exception('请输入打印内容!');
        $content = 'dingdanID=1&dayinjisn='.$sn.'&pages=1&dingdan='.$content.'&replyURL=123';
        Log::debug('XPrinter - 发送POST请求发送内容'.$content);
        return strpos(self::post(self::URL, $content), 'OK');
    }

}