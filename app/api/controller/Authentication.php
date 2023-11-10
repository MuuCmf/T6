<?php
namespace app\api\controller;

use think\exception\ValidateException;
use app\common\controller\Api;
use app\common\validate\Authentication as AuthenticationValidate;
use app\common\model\MemberAuthentication as AuthenticationModel;

class Authentication extends Api
{
    protected $AuthenticationModel;

    public function __construct()
    {
        parent::__construct();
        $this->AuthenticationModel = new AuthenticationModel();
    }

    /**
     * 提交、编辑认证资料
     */
    public function edit()
    {
        if (request()->isPost()) {
            $param = request()->post();
            $uid = get_uid();

            $data = [
                'id' => $param['id'],
                'uid' => $uid,
                'shopid' => $this->shopid,
                'name' => $param['name'],
                'card_type' => $param['card_type'],
                'card_no' => $param['card_no'],
                'front' => $param['front'],
                'back' => $param['back'],
                'status' => 1, //默认待审核状态
            ];

            // 数据验证
            try {
                validate(AuthenticationValidate::class)->check($data);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return $this->error($e->getError());
            }

            //写入数据
            $res = $this->AuthenticationModel->edit($data);
            if ($res) {
                //返回提示
                return $this->success('提交成功！', $res);
            } else {
                return $this->error('提交失败！');
            }
        }
    }

}