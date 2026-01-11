<?php
namespace app\api\controller;

use app\common\controller\Api;
use think\facade\Cache;

/**
 * API通用控制器
 * 用于提供通用的API接口
 */
class Proxy extends Api
{
    /**
     * 图片代理接口
     * 接收外部图片URL，由服务器获取并返回base64数据
     * 用于解决前端H5跨域访问图片资源的问题
     *
     * @return \think\Response
     */
    public function image()
    {
        try {
            $imageUrl = input('imageUrl', '', 'text');

            // 参数校验
            if (empty($imageUrl)) {
                return $this->error('图片URL不能为空');
            }

            // 安全校验：只允许http/https协议
            if (!preg_match('/^https?:\/\//i', (string)$imageUrl)) {
                return $this->error('不支持的URL协议');
            }

            // 安全校验：防止SSRF攻击，检查黑名单
            if ($this->isBlacklistedDomain($imageUrl)) {
                return $this->error('该域名不在允许访问范围内');
            }

            // 检查缓存
            $cacheKey = 'proxy_image_' . md5((string)$imageUrl);
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                return $this->success('success', $cachedData);
            }

            // 使用curl获取图片数据
            $imageData = $this->fetchImageData($imageUrl);

            if (!$imageData) {
                return $this->error('获取图片失败');
            }

            // 转换为base64
            $mimeType = $this->detectMimeType($imageData);
            $base64Data = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

            // 尝试缓存24小时
            Cache::set($cacheKey, $base64Data, 86400);

            return $this->success('success', $base64Data);
        } catch (\Exception $e) {
            // 异常错误
            $this->error('图片代理失败: ' . $e->getMessage());
        }
    }

    /**
     * 使用curl获取图片数据
     *
     * @param string $url
     * @return string|false
     */
    private function fetchImageData($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        //error_log('Fetch image - URL: ' . $url . ', HTTP Code: ' . $httpCode . ', Error: ' . $error . ', Data length: ' . strlen($data));

        if ($error || $httpCode >= 400) {
            return false;
        }

        return $data;
    }

    /**
     * 检测图片MIME类型
     *
     * @param string $data
     * @return string
     */
    private function detectMimeType($data)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($data);

        if (!$mimeType || strpos($mimeType, 'image/') !== 0) {
            // 默认使用png
            return 'image/png';
        }

        return $mimeType;
    }

    /**
     * 检查域名是否在黑名单中
     * 可根据实际需求配置白名单或黑名单
     *
     * @param string $url
     * @return bool
     */
    private function isBlacklistedDomain($url)
    {
        // 提取域名
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            return true;
        }

        // 黑名单示例（可根据需要配置）
        $blacklist = [
            'localhost',
            '127.0.0.1',
        ];

        foreach ($blacklist as $blocked) {
            if (strpos($domain, $blocked) !== false) {
                return true;
            }
        }

        return false;
    }
}
