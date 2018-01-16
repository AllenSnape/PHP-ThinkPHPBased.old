<?php
namespace allensnape\utils\wechat;

use allensnape\utils\StringUtil;

class WeChatUtil{

    /**
     * 公众号的唯一标识
     */
    const appid = '';

    /**
     * 公众号的appsecret
     */
    const secret = '';

    /**
     * 发送get或者post请求
     * @param string $url               请求的连接
     * @param string $post              post体, 如果不存在则默认为get请求
     * @return string 服务器相应的数据
     */
    public static function curlRequest($url, $post=''){
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
     * 根据code获取openid、access_token等数据
     * @param string $code 授权后获取到的code
     * @return void
     */
    public static function getOpenid($code){
        if(!StringUtil::hasText($code)) throw new \Exception('code参数错误!');
        $data = self::curlRequest(
            'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.self::appid.'&secret='.self::secret.'&code={$code}&grant_type=authorization_code'
        );
        return json_decode($data, true);
    }

    /**
     * 获取微信客户信息
     * @param string $accessToken           获取到的获取到的accessToken
     * @param string $openid                获取到的获取到的openid
     * @return void
     */
    public static function getUserinfo($accessToken=null, $openid=null){
        if(!StringUtil::hasText($accessToken)) throw new \Exception('accessToken参数错误!');
        if(!StringUtil::hasText($openid)) throw new \Exception('openid参数错误!');
        $data = self::curlRequest(
            'https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openid}&lang=zh_CN'
        );
        return json_decode($data, true);
    }

}