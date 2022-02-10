<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: WechatConfig.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/10/14
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\model;
use app\common\model\Base;

/**
 * 公众号配置表
 * Class WechatConfig
 * @package app\unions\model
 */
class WechatConfig extends Base{

    /**
     * @title 根据shopid获取公众号配置
     * @param int $shopid
     * @return WechatConfig|array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getWechatConfigByShopId($shopid = 0)
    {
        $res = $this->where(
            [
                ['shopid','=',$shopid]
            ]
        )->find();
        if ($res){
            $res = $res->toArray();
            return $res;
        }
        return null;
    }

    /**
     * @title 获取回调地址
     * @return string
     */
    public function callbackUrl(){
        return request()->domain() . "/unions/api.WechatOfficialAccount/callback";
    }
}