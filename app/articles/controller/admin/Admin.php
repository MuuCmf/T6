<?php
namespace app\articles\controller\admin;

use app\admin\controller\Admin as MuuAdmin;
use app\articles\model\ArticlesConfig as ConfigModel;
use app\articles\logic\Config as ConfigLogic;
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
        $config_data = Cache::get('MUUCMF_ARTICLES_CONFIG_DATA');
        if (empty($config_data)){
            $config_data = $this->ConfigModel->getDataByMap(['shopid' => 0]);
            $config_data = $this->ConfigLogic->formatData($config_data);
            Cache::set('MUUCMF_ARTICLES_CONFIG_DATA',$config_data);
        }
        $this->config_data = $config_data;
        View::assign([
            'config_data' => $config_data
        ]);
    }
}