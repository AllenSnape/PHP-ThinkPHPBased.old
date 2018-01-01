<?php
namespace allensnape\controller;

use think\Session;
use think\Controller;

use allensnape\utils\StringUtil;

class BaseController extends Controller
{

    /**
     * 拦截器
     */
    public function _initialize(){
        // 重定向使用
        // $this->error(...);
        // 或
        // $this->success(...);
        // 或
        // ob_end_clean();
        // header('Location: 重定向的网址');
        // abort(301或302);
    }

    // 返回规定格式的成功json数据
    protected function json_success($msg='', $data=null)
    {
        return $this->json_normal(1, $msg, $data);
    }

    // 返回规定格式的失败json数据
    protected function json_error($msg='', $data=null)
    {
        return $this->json_normal(-1, $msg, $data);
    }

    // 格式的json格式数据
    protected function json_normal($result=1, $msg='', $data=null){
        $json = array('result' => $result, 'message' => $msg);
        if(!is_null($data)) $json['data'] = $data;
        return json($json);
    }

    // 获取ip地址
    protected function get_client_ip($type = 0) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        // nginx 代理模式下，获取客户端真实IP
        if (isset($_SERVER['HTTP_X_REAL_IP'])){
            $ip=$_SERVER['HTTP_X_REAL_IP'];     
        } 
        // 客户端的ip
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        } 
        // 浏览当前页面的用户计算机的网关
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        } 
        // 浏览当前页面的用户计算机的ip地址
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        } else{
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    // 获取UserAgent
    protected function get_client_browser($glue = null) {
        $browser = array();
        $agent = $_SERVER['HTTP_USER_AGENT']; //获取客户端信息
        
        /* 定义浏览器特性正则表达式 */
        $regex = array(
            'ie'      => '/(MSIE) (\d+\.\d)/',
            'chrome'  => '/(Chrome)\/(\d+\.\d+)/',
            'firefox' => '/(Firefox)\/(\d+\.\d+)/',
            'opera'   => '/(Opera)\/(\d+\.\d+)/',
            'safari'  => '/Version\/(\d+\.\d+\.\d) (Safari)/',
        );
        foreach($regex as $type => $reg) {
            preg_match($reg, $agent, $data);
            if(!empty($data) && is_array($data)){
                $browser = $type === 'safari' ? array($data[2], $data[1]) : array($data[1], $data[2]);
                break;
            }
        }
        return empty($browser) ? false : (is_null($glue) ? $browser : implode($glue, $browser));
    }

    public function isEmpty($str){
        return StringUtil::isEmpty($str);
    }
    
    public function hasText($str){
        return StringUtil::hasText($str);
    }
    
    // 判断参数是否为0或1
    public function is0Or1($num){
        return $num == '0' || $num == '1' ? true : false;
    }

    /**
     * 生成验证码
     */
    public function getCodeImage($num=6, $w=80, $h=25, $session=null) {
        ob_end_clean();
        header("content-type:image/png");
        $code = "";
        for ($i = 0; $i < $num; $i++) {
            $code .= rand(0, 9);
        }
        //将生成的验证码写入session，备验证时用
        Session::set($session, $code);
        //创建图片，定义颜色值
        $im = imagecreate($w, $h);
        $black = imagecolorallocate($im, 0, 0, 0);
        $gray = imagecolorallocate($im, 200, 200, 200);
        $bgcolor = imagecolorallocate($im, 255, 255, 255);
        //填充背景
        imagefill($im, 0, 0, $gray);
        //画边框
        imagerectangle($im, 0, 0, $w-1, $h-1, $black);
        //随机绘制两条虚线，起干扰作用
        $style = array ($black, $black, $black, $black, $black, $gray, $gray, $gray, $gray, $gray);
        imagesetstyle($im, $style);
        $y1 = rand(0, $h);
        $y2 = rand(0, $h);
        $y3 = rand(0, $h);
        $y4 = rand(0, $h);
        imageline($im, 0, $y1, $w, $y3, IMG_COLOR_STYLED);
        imageline($im, 0, $y2, $w, $y4, IMG_COLOR_STYLED);
        //在画布上随机生成大量黑点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
        }
        //将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
        $strx = rand(3, 8);
        for ($i = 0; $i < $num; $i++) {
        $strpos = rand(1, 6);
        imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
            $strx += rand(8, 12);
        }
        imagepng($im);//输出图片
        imagedestroy($im);//释放图片所占内存
    }

    // 获取所有sessionId
    public static function getAllSessionIDs(){
        $allSessionIDs = [];
        $sessionNames = scandir(session_save_path());
        
        foreach($sessionNames as $sessionName) {
            $sessionName = str_replace("sess_","",$sessionName);
            if(strpos($sessionName,".") === false) { //This skips temp files that aren't sessions
                array_push($allSessionIDs, $sessionName);
                /*session_id($sessionName);
                session_start();
                $allSessions[$sessionName] = $_SESSION;
                session_abort();*/
            }
        }
        return $allSessionIDs;
    }

}