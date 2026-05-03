<?php

namespace app\admin\controller;

use think\exception\ValidateException;
use app\common\model\WechatWorkConfig;
use app\admin\validate\WechatWork as WechatWorkValidate;

class WechatWork extends Admin
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

            // json response
            return $this->success('', $data);
        }
    }
}
