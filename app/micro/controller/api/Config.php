<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Config.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/3/18
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\micro\controller\api;
use app\common\controller\Api;
use app\micro\model\MicroConfig;
use app\micro\model\MicroPage as MicroPageModel;
use app\micro\logic\Config as MicroPageLogic;

class Config extends Api{
    protected $MicroConfigModel;
    protected $MicroConfigLogic;

    public function __construct()
    {
        parent::__construct();
        $this->MicroConfigModel = new MicroPageModel();
        $this->MicroConfigLogic = new MicroPageLogic();
    }

    public function getConfig(){
        //获取自定义导航
        $footer_map = [
            ['shopid', '=', $this->shopid]
        ];
        $config = $this->MicroConfigModel->where($footer_map)->field('style,footer,navtar,search')->find();
        $config = $config ?? [];
        $config = $this->MicroConfigLogic->formatData($config);
        $this->success('获取Micro配置',$config);
    }
}