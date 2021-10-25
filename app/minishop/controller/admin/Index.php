<?php
namespace app\minishop\controller\admin;

use think\facade\Db;
use think\facade\View;
use app\admin\controller\Admin as MuuAdmin;

class Index extends MuuAdmin
{

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 系统默认展示的首页
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function index()
    {
        //默认首页
        return View::fetch();
    }

}