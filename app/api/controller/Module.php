<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Module as ModuleModel;
/**
 * 应用模块数据接口
 */

class Module extends Api
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

    /**
     * 获取已安装应用列表
     */
    public function lists()
    {
        //获取已安装模块列表
        $map = [
            ['is_setup', '=', 1]
        ];
        $list = $this->ModuleModel->where($map)->field('name')->select()->toArray();
        $arr = array_column($list,'name');

        return $this->success('success', $arr);
    }

    /**
     * 查询某应用是否启用
     */
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