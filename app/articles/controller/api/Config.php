<?php
namespace app\articles\controller\api;

use app\articles\model\ArticlesConfig as ConfigModel;
use app\articles\logic\Config as ConfigLogic;

class Config extends Base
{
    protected $model;
    protected $logic;
    function __construct()
    {
        parent::__construct();
        $this->logic = new ConfigLogic();
        $this->model = new ConfigModel();
    }

    public function get()
    {
        $map = [
            'shopid' => $this->shopid
        ];
        $res = $this->model->getDataByMap($map);
        $res = $this->logic->formatData($res);
        
        return $this->success('success',$res);
    }
}