<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Channel;
use think\facade\Db;

class Layouts extends Api
{
    /** 
     * 获取布局数据接口
     * 给PC端layouts使用
     */
    public function index()
    {
        // 获取前端导航菜单
        $channelModel = new Channel();
        $navbar = $channelModel->lists('navbar');
        
        // 获取底部导航菜单
        $footer_nav = $channelModel->lists('footer');
        
        // 获取用户菜单
        $user_nav = Db::name('UserNav')->order('sort asc')->where('status', '=', 1)->select();
        
        // 构造返回数据
        $data = [
            'navbar' => $navbar,
            'footer_nav' => $footer_nav,
            'user_nav' => $user_nav
        ];
        
        // 返回JSON格式
        return $this->success('success', $data);
    }
}