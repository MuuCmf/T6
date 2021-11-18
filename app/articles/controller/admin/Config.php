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
            $shop_config = input();
            // 获取配置数据
            $config_data = $this->ConfigModel->getDataByMap(['shopid' => 0]);
            // 初始化
            if(!empty($config_data)){
                $config_data = $this->ConfigLogic->formatData($config_data);
            }
            //店铺基础信息
            if(!empty($shop_config['title'])){
                $data['title'] = $shop_config['title'];
            }
            if(!empty($shop_config['cover'])){
                $data['cover'] = $shop_config['cover'];
            }
            //店铺状态
            if(isset($shop_config['status'])){
                $data['status'] = $shop_config['status'];
            }
            //关闭站点时的描述文字
            if(isset($shop_config['close_desc'])){
                $data['close_desc'] = $shop_config['close_desc'];
            }
            //店铺风格数据
            if(isset($shop_config['style'])){
                $data['style'] = $shop_config['style'];
            }
            //缩微图比例数据
            if(isset($shop_config['thumb'])){
                $data['thumb'] = $shop_config['thumb'];
            }

            //客服数据
            if(!empty($shop_config['service_mobile'])){//联系电话
                $service['mobile'] = $shop_config['service_mobile'];
            }
            if(!empty($shop_config['service_consult'])){//课程咨询服务
                $service['consult'] = $shop_config['service_consult'];
            }
            if(!empty($shop_config['service_business'])){//商务合作
                $service['business'] = $shop_config['service_business'];
            }
            if(!empty($shop_config['service_qq'])){//客服qq
                $service['qq'] = $shop_config['service_qq'];
            }
            if(!empty($shop_config['service_qrcode'])){
                $service['qrcode'] = $shop_config['service_qrcode'];
            }
            if(!empty($service)){
                //转为json字符串
                $service = json_encode($service);
                $data['service'] = $service;
            }
            
            //关注公众号设置
            if(!empty($shop_config['follow_title'])){
                $follow['title'] = $shop_config['follow_title'];
            }
            if(!empty($shop_config['follow_desc'])){
                $follow['desc'] = mb_substr($shop_config['follow_desc'], 0, 80, 'utf-8');
                $follow['desc'] = str_replace(array("\r\n", "\r", "\n"), "", $follow['desc']);
            }
            if(!empty($shop_config['follow_qrcode_url'])){
                $follow['qrcode_url'] = $shop_config['follow_qrcode_url'];
            }
            if(!empty($follow)){
                //转为json字符串
                $follow = json_encode($follow);
                $data['follow'] = $follow;
            }

            //自定义分享设置
            if(!empty($shop_config['share_title'])){
                $share['title'] = $shop_config['share_title'];
            }
            if(!empty($shop_config['share_desc'])){
                $share['desc'] = mb_substr($shop_config['share_desc'], 0, 80, 'utf-8');
                $share['desc'] = str_replace(array("\r\n", "\r", "\n"), "", $share['desc']);
            }
            if(!empty($share)){
                //转为json字符串
                $share = json_encode($share);
                $data['share'] = $share;
            }

            //自定义版权设置
            if(!empty($shop_config['copyright_show'])){
                $copyright['show'] = intval($shop_config['copyright_show']);
            }
            if(!empty($shop_config['copyright_title'])){
                $copyright['title'] = $shop_config['copyright_title'];
            }
            if(!empty($shop_config['copyright_description'])){
                $copyright['description'] = $shop_config['copyright_description'];
            }
            if(!empty($shop_config['copyright_url'])){
                $copyright['url'] = $shop_config['copyright_url'];
            }
            if(!empty($copyright)){
                //转为json字符串
                $copyright = json_encode($copyright);
                $data['copyright'] = $copyright;
            }

            //地图设置
            if(!empty($shop_config['map_cloud'])){
                $map['cloud'] = $shop_config['map_cloud'];
            }
            if(!empty($shop_config['baidumap_id'])){
                $map['baidu']['id'] = $shop_config['baidumap_id'];
            }
            if(!empty($shop_config['baidumap_ak'])){
                $map['baidu']['ak'] = $shop_config['baidumap_ak'];
            }
            
            if(!empty($map)){
                $map = json_encode($map);
                $data['map'] = $map;
            }

            //模板消息
            if(!empty($shop_config['tmplmsg_switch'])){
                $tmplmsg_data['switch'] = $shop_config['tmplmsg_switch'];
            }
            if(!empty($shop_config['tmplmsg_pay_success'])){
                $tmplmsg_data['pay_success'] = $shop_config['tmplmsg_pay_success'];
            }
            
            if(!empty($shop_config['tmplmsg_to'])){
                $tmplmsg_data['to'] = implode(',',$shop_config['tmplmsg_to']);
            }
            if(!empty($shop_config['tmplmsg_manager_uid'])){
                $tmplmsg_data['manager_uid'] = $shop_config['tmplmsg_manager_uid'];
            }
            if(!empty($tmplmsg_data)){
                $tmplmsg_data = json_encode($tmplmsg_data);
                $data['tmplmsg'] = $tmplmsg_data;
            }

            //推荐搜索关键字
            if(!empty($shop_config['search'])){
                $data['search'] = trim($shop_config['search']);
            }

            //是否显示阅读量
            if(isset($shop_config['show_view'])){
                $data['show_view'] = $shop_config['show_view'];
            }
            //是否显示订阅量（购买量）
            if(isset($shop_config['show_sale'])){
                $data['show_sale'] = $shop_config['show_sale'];
            }
            //是否显示收藏量
            if(isset($shop_config['show_favorites'])){
                $data['show_favorites'] = $shop_config['show_favorites'];
            }
            //是否显示划线架构
            if(isset($shop_config['show_marking_price'])){
                $data['show_marking_price'] = $shop_config['show_marking_price'];
            }
            //购买按钮自定义文字设置
            if(isset($shop_config['sale_before'])){
                $sale_btn_data['before'] = $shop_config['sale_before'];
            }
            if(isset($shop_config['sale_after'])){
                $sale_btn_data['after'] = $shop_config['sale_after'];
            }
            if(!empty($sale_btn_data)){
                $sale_btn_data = json_encode($sale_btn_data);
                $data['sale_btn'] = $sale_btn_data;
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
                Cache::delete('MUUCMF_CLASSROOM_CONFIG_DATA');
                return $this->success($msg . '成功！', $result, url('admin.config/index'));
            }else{
                return $this->error($msg . '失败！');
            }
        }else{
            // 获取店铺配置数据
            $data = $this->ConfigModel->getDataByMap(['shopid' => 0]);
            if(empty($data)){
                $data = $this->ConfigModel->defaultData();
            }
            $data = $this->ConfigLogic->formatData($data);
            //dump($data);
            View::assign('data',$data);

            //输出页面
            return View::fetch();
        }
    }
}