<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\logic\Config;
use app\channel\facade\wechat\OfficialAccount;
use app\common\model\Orders as OrdersModel;
use app\common\model\CapitalFlow;


class Index extends Common
{
    function __construct()
    {
        parent::__construct();
        $this->OrderModel = new OrdersModel();
        $this->CapitalFlowModel = new CapitalFlow();
    }
    public function index()
    {
        $this->setTitle('首页');
        $this->setKeywords('首页,MuuCmf T6');
        $this->setDescription('首页,MuuCmf T6');
        return View::fetch();
    }

    public function debug()
    {
        dump(config());
    }

    
}
