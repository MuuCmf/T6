<?php

namespace app\channel\service\wechat;

use app\channel\model\WechatMpConfig;
use EasyWeChat\Factory;
use think\Exception;

/**
 * 微信小程序类
 * Class MiniProgram
 * @package app\channel\service\wechat
 */
class MiniProgram extends Wechat
{
    function __construct()
    {
        $this->type = 'wechat_mini_program';
        //服务配置文件
        $config = $this->config = $this->initConfig();
        $app = Factory::miniProgram($config);
        parent::__construct($app);
    }

    public function initConfig()
    {
        $this->shopid = request()->param('shopid') ?? 0;
        //获取配置信息
        $map = [
            ['shopid', '=', $this->shopid],
        ];
        $data = (new WechatMpConfig())->where($map)->find();
        if (empty($data)) {
            throw  new Exception('小程序配置信息不存在');
        }
        $data = $data->toArray();
        return [
            'app_id' => $data['appid'],
            'secret' => $data['secret'],
            'response_type' => 'array',
            //生成日志
            'log' => $this->log(),
        ];
    }

    /**
     * @title code获取用户信息
     * @param $code
     * @return mixed
     */
    public function user($code)
    {
        return $this->app->auth->session($code);
    }

    /**
     * @title 解密
     * @param $session
     * @param $iv
     * @param $encryptedData
     * @return mixed
     */
    public function decryptData($session, $iv, $encryptedData)
    {
        return $this->app->encryptor->decryptData($session, $iv, $encryptedData);
    }

    /**
     * @title 生成小程序码
     * @param $scene
     * @param array $optional
     * @return mixed
     */
    public function unlimitQrcode($scene, $optional = [])
    {
        return $this->app->app_code->getUnlimit($scene, $optional);
    }

    /**
     * @title 发送模板消息
     * @param $data
     * @return mixed
     */
    public function sendTemplateMsg($data)
    {
        return $this->app->template_message->send($data);
    }

    /**
     * @title 获取小程序直播间列表
     * @return mixed
     */
    public function getLiveRooms()
    {
        return $this->app->live->getRooms();
    }

    /**
     * @title 获取小程序直播回放视频
     * @param int $roomid
     * @return mixed
     */
    public function getLivePlaybacks(int $roomid)
    {
        return $this->app->live->getPlaybacks($roomid);
    }
}
