<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Module as ModuleModel;

/**
 * 公告控制器
 */
class Announce extends Admin
{
    protected $ModuleModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
        $this->ModuleModel = new ModuleModel();
    }

    /**
     * 列表
     */
    public function list()
    {

        return View::fetch();
    }

}
