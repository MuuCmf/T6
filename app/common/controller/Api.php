<?php
namespace app\common\controller;

class Api extends Base{

    public $shopid;//店铺ID
    public $module;//请求的应用
    public $params;//参数
    function __construct()
    {
        parent::__construct();
        $this->params = request()->param();
        $this->shopid = $params['shopid'] ?? 0;
        $this->initModuleName();

    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName(){
        $this->module = $this->params['app'] ?? App('http')->getName();
    }
}