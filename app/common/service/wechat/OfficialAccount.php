<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: OfficialAccount.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/24
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\common\service\wechat;
use EasyWeChat\Factory;

/**
 * 微信公众号类
 * Class OfficialAccount
 * @package app\common\service\wechat
 */
class OfficialAccount extends Wechat {

    function __construct()
    {
        $app =  Factory::officialAccount($this->config());
        parent::__construct($app);
    }
    protected function config()
    {
        //获取配置信息
        $data = [
            'appid' => 'wx90fcefad8616a371',
            'secret' => 'b0cdc7e33d76712be26ba233728f0fbb'
        ];
        return [
            'app_id' => $data['appid'],
            'secret' => $data['secret'],
            'response_type' => 'array',
            //生成日志
            'log' => $this->log()
        ];
    }

    /**
     * 公众号授权网页验证
     */
    public function serverOAath(){
        $response = $this->app->server->serve();
        $response->send();
        exit();
    }


}