<?php
namespace app\api\controller;

use app\common\controller\Base;
use app\common\model\Module as ModuleModel;
/**
 * 应用模块数据接口
 */

class Module extends Base
{

    protected $ModuleModel;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->ModuleModel = new ModuleModel();
    }

    public function enable()
    {
        $name = input('name', '', 'text');
        $map = [
            'name' => $name,
            'is_setup' => 1
        ];
        $data = $this->ModuleModel->getDataByMap($map);

        if($data){
            return $this->success('已安装应用', $data);
        }else{
            return $this->error('未安装应用');
        }

    }

}