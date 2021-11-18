<?php
namespace app\articles\controller\admin;

use think\facade\View;
use think\facade\Cache;

class Config extends Admin
{   
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 配置页面
     * @return [type] [description]
     */
    public function index()
    {
        //数据提交
        if (request()->isPost()) {
            $input_config = input();
            // 获取配置数据
            $old_config = $this->config_data;
            //是否启用插件 0关闭，1启用
            if(isset($input_config['status'])){
                $data['status'] = $input_config['status'];
            }
            //评论开关
            if(isset($input_config['comment_switch'])){
                $article_config['comment']['switch'] = $input_config['comment_switch'];
                if($old_config && is_array($old_config['config'])){
                    //合并数组
                    $article_config = array_merge($old_config['config'],$article_config);
                }
                //转为json字符串
                $article_config = json_encode($article_config);
                $data['config'] = $article_config;
            }

            //提交数据
            if(!empty($old_config['id'])){
                $msg = '更新配置';
                $data['id'] = $old_config['id'];
            }else{
                $msg = '新增配置';
            }
            //dump($shop_config);exit;
            $result = $this->ConfigModel->edit($data);

            if($result){
                Cache::delete('MUUCMF_ARTICLES_CONFIG_DATA');
                return $this->success($msg . '成功！', $result, url('admin.config/index'));
            }else{
                return $this->error($msg . '失败！');
            }
        }else{
            // 获取店铺配置数据
            $data = $this->config_data;
            View::assign('data',$data);

            //输出页面
            return View::fetch();
        }
    }
}