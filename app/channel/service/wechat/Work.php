<?php

namespace app\channel\service\wechat;

use think\Exception;
use EasyWeChat\Factory;
use app\channel\model\WechatWorkConfig;

/**
 * 企业微信类
 * Class Work
 * @package app\common\service\wechat
 */
class Work extends Wechat
{
    function __construct()
    {
        $shopid = request()->param('shopid') ?? 0;
        $this->shopid = $shopid;
        $this->type = 'wechat_work';
        //服务配置文件
        $config = $this->config =  $this->initConfig();
        $app =  Factory::work($config);
        parent::__construct($app);
    }

    public function initConfig()
    {
        //获取配置信息
        $data = (new WechatWorkConfig())->getConfigByShopId($this->shopid);
        if (empty($data)) {
            throw  new Exception('企业微信配置数据不存在');
        }
        return [
            'corp_id' => $data['corp_id'],
            'agent_id' => $data['agent_id'],
            'secret' => $data['secret'],
            'token' => $data['token'],
            'aes_key' => $data['encoding_aes_key'],
            'response_type' => 'array',
            //生成日志
            'log' => $this->log(),
        ];
    }

    /**
     * @title 授权验证
     */
    public function serverOAath()
    {
        $response = $this->app->server->serve();
        $response->send();
        exit();
    }

    /**
     * @title 获取微信服务器IP
     * @return mixed
     */
    public function getWechatServerIps()
    {
        return $this->app->base->getValidIps();
    }

    /**
     * @title 读取（查询）已设置菜单
     * @return mixed
     */
    public function getMenu()
    {
        return $this->app->menu->get();
    }

    /**
     * @title 获取素材列表
     * @param $type **图片(image)、视频(video)、语音（voice）、图文（news）
     * @param int $offset 从全部素材的该偏移位置开始返回，可选，默认 0，0 表示从第一个素材 返回
     * @param int $count 返回素材的数量，可选，默认 20, 取值在 1 到 20 之间
     * @return mixed
     */
    public function getMediaList($type, $offset = 0, $count = 20)
    {
        return $this->app->media->list($type, $offset, $count);
    }

    /**
     * @title 根据素材id获取详情
     * @param $media_id
     * @return mixed
     */
    public function getMedia($media_id)
    {
        return $this->app->media->get($media_id);
    }

    /**
     * @title 网页授权
     * @param string $target_url
     * @throws Exception
     */
    public function oauth(array $params = [])
    {
        //授权回调参数处理
        $callbackUrl = request()->domain() . "/channel/work/oauthCallback";
        if ($params) {
            $callbackUrl .= "?muu=muucmf";
            foreach ($params as $key => $item) {
                $callbackUrl .= "&{$key}={$item}";
            }
        }
        // 返回一个 redirect 实例
        $redirect = $this->app->oauth->redirect($callbackUrl);
        // 获取企业微信跳转目标地址
        $targetUrl = $redirect->getTargetUrl();

        return $targetUrl;
    }

    /**
     * @title 获取access token
     * @return mixed
     */
    public function getToken()
    {
        return $this->app->access_token->getToken();
    }

    /**
     * 读取成员
     */
    public function getContacts($userId)
    {
        return $this->app->user->get($userId);
    }

    /**
     * 获取单个用户信息
     */
    public function getUserByOpenid($openid)
    {
        return $this->app->user->get($openid);
    }
}
