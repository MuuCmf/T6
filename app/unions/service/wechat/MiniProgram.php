<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: MiniProgram.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/10/15
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\unions\service\wechat;

use app\unions\model\MiniProgramConfig;
use EasyWeChat\Factory;
use think\Exception;
use think\facade\Cache;

/**
 * 微信小程序类
 * Class MiniProgram
 * @package app\unions\service\wechat
 */
class MiniProgram extends Wechat{
    function __construct()
    {
        $this->type = 'wechat_mini_program';
        //服务配置文件

        $config = $this->config = $this->initConfig();
        $app = Factory::miniProgram($config);
        parent::__construct($app);
    }

    public function initConfig(){
        $this->shopid = Cache::get('shopid') ?: 0;
        $this->module = Cache::get('module_name') ?: '';
        //获取配置信息
        $map = [
            ['shopid' ,'=' ,$this->shopid],
            ['name' ,'=' , $this->module],
            ['platform' ,'=' ,'wechat']
        ];
        $data = (new MiniProgramConfig())->where($map)->find();
        if (empty($data)){
            throw  new Exception('小程序配置信息不存在');
        }
        return [
            'app_id' => $data['appid'],
            'secret' => $data['secret'],
            'response_type' => 'array',
            //生成日志
            'log' => $this->log(),
        ];
    }

    /**
     * code获取用户信息
     * @param $code
     * @return mixed
     */
    public function user($code){
        return $this->app->auth->session($code);
    }
}