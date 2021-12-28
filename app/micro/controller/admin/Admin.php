<?php
namespace app\micro\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\micro\model\MicroConfig as ConfigModel;
use app\micro\logic\Config as ConfigLogic;
use think\facade\Cache;
use think\facade\View;

class Admin extends MuuAdmin{
    
    protected $ConfigModel;
    protected $ConfigLogic;
    public $config_data;
    public $shopid = 0;

    public function __construct()
    {
        parent::__construct();
        $this->ConfigModel = new ConfigModel();
        $this->ConfigLogic = new ConfigLogic();
        $config_data = [];
        $config_data = Cache::get('MUUCMF_MICRO_CONFIG_DATA');
        if (empty($config_data)){
            $config_data = $this->ConfigModel->getDataByMap(['shopid' => 0])->toArray();
            $config_data = $this->ConfigLogic->formatData($config_data);
            Cache::set('MUUCMF_MICRO_CONFIG_DATA',$config_data);
        }
        $this->config_data = $config_data;
        View::assign('config_data',$config_data);
    }
}