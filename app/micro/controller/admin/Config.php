<?php
namespace app\micro\controller\admin;

use think\facade\View;
use think\facade\Cache;
use app\admin\controller\Admin as Admin;
use app\micro\model\MicroConfig as ConfigModel;
use app\micro\logic\Config as ConfigLogic;

class Config extends Admin
{   
    protected $ConfigModel;
    protected $ConfigLogic;
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->ConfigModel = new ConfigModel();
        $this->ConfigLogic = new ConfigLogic();
    }

    /**
     * 配置页面
     * @return [type] [description]
     */
    public function index()
    {
        //数据提交
        if (request()->isPost()) {
            $shop_config = input();
            // 获取配置数据
            $config_data = $this->ConfigModel->getDataByMap(['shopid' => 0]);
            // 初始化
            if(!empty($config_data)){
                $config_data = $this->ConfigLogic->formatData($config_data);
            }
            
            //店铺风格数据
            if(isset($shop_config['style'])){
                $data['style'] = $shop_config['style'];
            }

            //dump($data);exit;
            //提交数据
            if($config_data){
                $msg = '更新配置';
                $data['id'] = $config_data['id'];
            }else{
                $msg = '新增配置';
            }
            //dump($shop_config);exit;
            $result = $this->ConfigModel->edit($data);

            if($result){
                Cache::delete('MUUCMF_MICRO_CONFIG_DATA');
                return $this->success($msg . '成功！', $result, url('admin.config/index'));
            }else{
                return $this->error($msg . '失败！');
            }
        }else{
            // 获取店铺配置数据
            $data = $this->ConfigModel->getDataByMap(['shopid' => 0])->toArray();
            $data = $this->ConfigLogic->formatData($data);
            
            // 获取风格样式数组
            $style = $this->ConfigLogic->_style;
            View::assign('data',$data);
            View::assign('style',$style);

            //输出页面
            return View::fetch();
        }
    }

    public function api()
    {
        return $this->success('success', $this->config_data);
    }

}