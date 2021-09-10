<?php
declare (strict_types = 1);

namespace app\classroom\controller;

use think\facade\View;
use app\admin\controller\Admin as BaseAdmin;

class Admin extends BaseAdmin
{
    public function index()
    {

        // 模板输出
        return View::fetch('index');
    }
}
