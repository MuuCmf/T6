<?php
namespace app\articles\controller\admin;

use think\facade\View;
use think\facade\Cache;
use app\articles\model\ArticlesConfig as ConfigModel;

class Config extends Admin
{   
    protected $ConfigModel;
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->ConfigModel = new ConfigModel();
    }

    /**
     * 配置页面
     * @return [type] [description]
     */
    public function index()
    {
        //数据提交
        if (request()->isPost()) {
            $params = input();
            // 获取配置数据
            $config_data = $this->config_data;
            
            //店铺状态
            if(isset($params['status'])){
                $data['status'] = $params['status'];
            }
            //关闭站点时的描述文字
            if(isset($params['close_desc'])){
                $data['close_desc'] = $params['close_desc'];
            }
            //缩微图比例数据
            if(isset($params['thumb'])){
                $data['thumb'] = $params['thumb'];
            }

            // 显示配置
            if(!empty($config_data['comment']) && !empty($params['comment'])){
                $old_comment_data = $config_data['comment'];
                if($old_comment_data && is_array($old_comment_data)){
                    //合并数组
                    $comment_data = array_merge($old_comment_data, $params['comment']);
                    $comment_data = json_encode($comment_data);
                    $data['comment'] = $comment_data;
                }
            }
            
            //提交数据
            if($config_data){
                $msg = '更新配置';
                $data['id'] = $config_data['id'];
            }else{
                $msg = '新增配置';
            }

            $result = $this->ConfigModel->edit($data);

            if($result){
                Cache::delete(request()->host() . '_MUUCMF_ARTICLES_CONFIG_DATA_'. $this->shopid);
                return $this->success($msg . '成功！', $result, url('admin.config/index'));
            }else{
                return $this->error($msg . '失败！');
            }
        }else{
            // 获取店铺配置数据
            $data = $this->config_data;
            View::assign('data',$data);

            // 设置页面Title
            $this->setTitle('基础配置');
            // 输出模板
            return View::fetch();
        }
    }

    public function api()
    {
        return $this->success('success', $this->config_data);
    }

}