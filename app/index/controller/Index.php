<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\facade\View;
use app\common\controller\Common;

class Index extends Common
{
    public function index()
    {
        return View::fetch();
    }

    public function debug()
    {
        dump(config());
    }
}
