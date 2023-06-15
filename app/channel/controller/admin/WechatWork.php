<?php

namespace app\channel\controller\admin;

use think\facade\View;
use think\Exception;
use think\exception\ValidateException;
use app\admin\controller\Admin as MuuAdmin;
use app\admin\builder\AdminConfigBuilder;
use app\channel\model\WechatWorkConfig;
use app\channel\validate\WechatWork as WechatWorkValidate;

class WechatWork extends MuuAdmin
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 配置
     */
    public function config()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['shopid'] = $this->shopid;
            // 数据验证
            try {
                validate(WechatWorkValidate::class)->scene('edit')->check($data);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }

            $res = (new WechatWorkConfig())->edit($data);
            if ($res) {
                return $this->success('保存成功', $data, 'refresh');
            }
            return $this->error('网络异常，请稍后再试');
        } else {
            //查询微信平台配置
            $data = (new WechatWorkConfig())->getConfigByShopId($this->shopid);
            View::assign('data', $data);
            //设置页面title
            $this->setTitle('企业微信配置');

            return View::fetch();
        }
    }

}
