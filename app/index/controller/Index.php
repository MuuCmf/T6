<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;
use app\common\controller\Common;

class Index extends Common
{
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
        dump(query_user(is_login()));
    }
}
