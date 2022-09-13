<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;
use app\common\controller\Common;

class Index extends Common
{
    function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $this->setTitle('首页');
        $this->setKeywords('MuuCmf T6,开发框架,CMS,Muu云课堂,Muu云小店,Muu云课堂V2,知识付费,知识服务,电商系统,电商软件,');
        $this->setDescription('MuuCmf T6开源低代码应用开发框架，北京火木科技有限公司 版权所有并提供技术支持');
        return View::fetch();
    }
}
