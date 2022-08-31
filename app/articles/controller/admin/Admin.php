<?php
namespace app\articles\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\articles\model\ArticlesConfig as ConfigModel;
use think\facade\View;

class Admin extends MuuAdmin{

    public $config_data;

    public function __construct()
    {
        parent::__construct();

        $config_data = (new ConfigModel())->getConfig($this->shopid);
        $this->config_data = $config_data;

        View::assign('config', $config_data);
    }
}