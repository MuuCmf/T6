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
use app\common\model\UniAccount;
use EasyWeChat\Factory;
use think\Exception;

/**
 * 微信公众号类
 * Class OfficialAccount
 * @package app\common\service\wechat
 */
class OfficialAccount extends Wechat {

    function __construct()
    {
        $this->type = 'wechat_official_account';
        $app =  Factory::officialAccount($this->config());
        parent::__construct($app);
    }
    public function config()
    {
        //获取配置信息
        $data = (new UniAccount())->findDataByWhere(['group' => $this->type]);
        if (empty($data)){
            throw  new Exception('公众号配置文件不存在');
        }
        return [
            'app_id' => $data['MP_APPID'],
            'secret' => $data['MP_APP_SECRET'],
            'response_type' => 'array',
            //生成日志
            'log' => $this->log()
        ];
    }

    /**
     * 公众号授权验证
     */
    public function serverOAath(){
        $response = $this->app->server->serve();
        $response->send();
        exit();
    }

    /**
     * 获取微信服务器IP
     * @return mixed
     */
    public function getWechatServerIps(){
        return $this->app->base->getValidIps();
    }

    /**
     * 读取（查询）已设置菜单
     * @return mixed
     */
    public function getMenu(){
        return $this->app->menu->list();
    }

    /**
     * 获取当前菜单
     * @return mixed
     */
    public function currentMenu(){
        return $this->app->menu->current();
    }

    /**
     * 设置菜单
     * @param $menu
     * @return mixed
     */
    public function createMenu($menu){
        return $this->app->menu->create($menu);
    }

    /**
     * 获取当前设置的回复规则
     * @return mixed
     */
    public function currentMessage(){
        return $this->app->auto_reply->current();
    }

    /**
     * @title 获取素材列表
     * @param $type **图片(image)、视频(video)、语音（voice）、图文（news）
     * @param int $offset 从全部素材的该偏移位置开始返回，可选，默认 0，0 表示从第一个素材 返回
     * @param int $count 返回素材的数量，可选，默认 20, 取值在 1 到 20 之间
     * @return mixed
     */
    public function getMaterialList($type, $offset = 0 ,$count = 20){
        return $this->app->material->list($type, $offset, $count);
    }
}