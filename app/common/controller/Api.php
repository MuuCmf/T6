<?php
namespace app\common\controller;

use think\facade\Config;

class Api extends Base
{
    public $shopid = 0;//店铺ID
    public $module;//请求的应用
    public $app_name;//应用别名
    public $params;//参数

    public function __construct()
    {
        $this->params = request()->param();
        $this->shopid = $params['shopid'] ?? 0;
        $this->initSiteStatus();
        $this->initModuleName();
    }

    /**
     * 初始化站点状态
     */
    protected function initSiteStatus()
    {
        // 判断站点是否关闭
        if (strtolower(App('http')->getName()) != 'ucenter' && strtolower(App('http')->getName()) != 'admin') {
            if (!Config::get('system.SITE_CLOSE')) {
                $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
                $result = [
                    'code' => 0,
                    'msg' => '站点临时关闭，请稍后访问',
                ];
                if ($type == 'html') {
                    $response = view(Config::get('app.dispatch_error_tmpl'), $result);
                } else if ($type == 'json') {
                    $response = json($result);
                }
                throw new \think\exception\HttpResponseException($response);
            }
        }
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName()
    {
        $this->module = $this->app_name = $this->params['app'] ?? App('http')->getName();
    }
}