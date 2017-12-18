<?php
namespace app\index\controller;

use allensnape\controller\BaseController;

class Index extends BaseController{
    
    public function index($field=null){
        return $this->redirect(config('view_replace_str')['__ROOT__'].'/index.php/admin/home/home');
    }
    
    /**
     * 二进制文件上传
     */
    public function upload(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                return $this->json_success('上传成功!', DS . 'uploads'. DS . $info->getSaveName());
            }else{
                return $this->json_error($file->getError());
            }
        }else{
            return $this->json_error('请选择要上传的文件!');
        }
    }

    /**
     * base64文件上传
     */
    public function uploadInBase64($file=null){
        // 检查参数
        if(!$this->hasText($file)) return $this->json_error('请选择要上传的文件!');
        // 整理数据
        $base64File = trim($file);
        // 保存的路径
        $savePath = ROOT_PATH . 'public' . DS . 'uploads' . DS;
        // 检查路径是否存在
        if(!file_exists($savePath)){
            mkdir($savePath, 0777);
        }
        // 检查格式
        if(preg_match('/^(data:\s*\w+\/(\w+);base64,)/', $base64File, $result)){
            // 文件格式
            $type = $result[2];
            $dataPath = date('Ymd').DS;
            $newFile = $dataPath.md5($base64File).'.'.$type;
            // 检查路径是否存在
            if(!file_exists($savePath.$dataPath)){
                mkdir($savePath.$dataPath, 0777);
            }
            $savePath = $savePath.$newFile;
            if(file_put_contents($savePath, base64_decode(str_replace($result[1], '', $base64File)))){
                return $this->json_success('上传成功!', DS . 'uploads'. DS . $newFile);
            }
        }
        return $this->json_error('上传的base64格式文件有误!');

        /* try{
            document.getElementById("sendMsgInput").addEventListener('paste', function(e){
                if (e.clipboardData && e.clipboardData.items[0].type.indexOf('image') > -1) {
                    var that = this, reader =  new FileReader();
                    file = e.clipboardData.items[0].getAsFile();
                    var layerLoadingFlag = layer.load(2);
                    reader.onload = function (e) {
                        var xhr = new XMLHttpRequest(), fd = new FormData();
                        xhr.open('POST', '__ROOT__/index.php/index/index/uploadInBase64.html', true);
                        xhr.onload = function(){
                            layer.close(layerLoadingFlag);
                            var data = JSON.parse(this.responseText);
                            if(data.result == 1){
                                回调('__ROOT__'+data.data);
                            }else{
                                layer.alert(data.message, {icon: 5});
                            }
                        }
                        fd.append('file', this.result);
                        xhr.send(fd);
                    }
                    reader.readAsDataURL(file);
                }
            }, false);
        }catch(e){
            layer.alert("初始化粘贴图片上传功能失败!", {icon: 5});
            console.error(e);
        } */
    }
}
