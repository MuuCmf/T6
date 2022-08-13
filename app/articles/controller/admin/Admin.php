<?php
namespace app\articles\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\articles\model\ArticlesConfig as ConfigModel;
use think\facade\View;

class Admin extends MuuAdmin{
    
    protected $ConfigModel;
    public $config_data;
    public $shopid = 0;

    public function __construct()
    {
        parent::__construct();
        $this->ConfigModel = new ConfigModel();

        $config_data = $this->ConfigModel->getConfig($this->shopid);
        $this->config_data = $config_data;
        View::assign([
            'config_data' => $config_data
        ]);
    }
}