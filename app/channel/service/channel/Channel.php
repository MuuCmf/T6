<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Channel.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/2/21
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
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
            case 'weixin_app':
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