<?php
namespace app\channel\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\channel\facade\bytedance\MiniProgram as MiniProgramServer;
use app\channel\facade\wechat\MiniProgram;

class Debug extends MuuAdmin
{
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // $a = MiniProgramServer::getAccessToken();
        // dump($a);exit;

        $order_no = '202209171005057549';
        $result = MiniProgramServer::ordersPush($order_no);

        dump($result);

    }

}