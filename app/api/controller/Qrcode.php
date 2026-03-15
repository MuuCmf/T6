<?php
namespace app\api\controller;

use app\common\controller\Api;

class Qrcode extends Api
{
    /**
     * 生成二维码 输出图片
     */
    public function create($url){
        // 严格验证URL格式
        $url = urldecode($url);
        
        // 检查URL是否为有效格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->error('无效的URL格式');
        }
        
        // 解析URL检查协议和域名（可选：添加白名单验证）
        $parsedUrl = parse_url($url);
        
        // 确保URL有协议且是http或https
        if (!isset($parsedUrl['scheme']) || !in_array(strtolower($parsedUrl['scheme']), ['http', 'https'])) {
            return $this->error('URL必须使用http或https协议');
        }
        
        // 可选：添加域名白名单验证
        // $allowedDomains = ['example.com', 'your-domain.com'];
        // if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], $allowedDomains)) {
        //     return $this->error('不允许的域名');
        // }
        
        ob_clean();//这个一定要加上，清除缓冲区
        $qrcode = qrcode($url,false,false,false,'8','L',2,false);
        echo $qrcode;exit();
    }
}