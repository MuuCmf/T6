<?php
namespace app\channel\service\channel;

use app\channel\model\WechatConfig;
use app\channel\model\WechatMpConfig;
use think\Exception;

class Channel{
    /**
     * 获取渠道配置信息
     * @return WechatMpConfig|WechatConfig|array
     */
    public function config($channel ,$shopid = 0)
    {

        switch ($channel){
            //微信公众号
            case 'weixin_h5':
                $data = (new WechatConfig())->getWechatConfigByShopId($shopid);
                if (empty($data)){
                    throw  new Exception('公众号配置文件不存在');
                }
                break;
            //微信小程序
            case 'weixin_mp':
                //获取配置信息
                $map = [
                    ['shopid' ,'=' , $shopid],
                ];
                $data = (new WechatMpConfig())->where($map)->find();
                if (empty($data)){
                    throw  new Exception('小程序配置信息不存在');
                }
                break;
        }
        return $data;
    }
}