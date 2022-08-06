<?php
namespace app\articles\controller\api;

use app\common\controller\Api;
use app\articles\model\ArticlesConfig as ConfigModel;
use app\articles\logic\Config as ConfigLogic;
use think\facade\Cache;
use think\facade\View;

class Base extends Api
{
    
    protected $ConfigModel;
    protected $ConfigLogic;
    public $config_data;

    public function __construct()
    {
        parent::__construct();
        $this->ConfigModel = new ConfigModel();
        $this->ConfigLogic = new ConfigLogic();

        if (empty($config_data)){
            $config_data = $this->ConfigModel->getDataByMap(['shopid' => $this->shopid]);
            $config_data = $this->ConfigLogic->formatData($config_data);
            Cache::set(request()->host() . '_MUUCMF_ARTICLES_CONFIG_DATA_' . $this->shopid, $config_data);
        }
        $this->config_data = $config_data;
        View::assign([
            'config_data' => $config_data
        ]);
    }
}