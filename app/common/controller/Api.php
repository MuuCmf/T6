<?php
namespace app\common\controller;

class Api extends Base{

    public $shopid;//店铺ID
    public $module;//请求的应用
    function __construct()
    {
        parent::__construct();
        $this->initModuleName();
        $params = request()->param();
        $this->shopid = $params['shopid'] ?? 0;
    }

    /**
     * 实例化应用名称
     */
    protected function initModuleName(){
        $this->module = App('http')->getName();
    }
}