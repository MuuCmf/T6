<?php
namespace app\api\controller;

use think\Exception;
use think\facade\Db;
use think\exception\ValidateException;
use app\common\controller\Api;
use app\common\validate\Authentication as AuthenticationValidate;
use app\common\model\MemberAuthentication as AuthenticationModel;
use app\common\model\Member as MemberModel;

class Authentication extends Api
{
    protected $MemberModel;
    protected $AuthenticationModel;

    public function __construct()
    {
        parent::__construct();
        $this->MemberModel = new MemberModel();
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

            Db::startTrans();
            try{
                //写入数据
                $res = $this->AuthenticationModel->edit($data);
                if(!$res){
                    throw new Exception('数据写入失败');
                }
                // 更改用户表认证状态值
                $res = $this->MemberModel->edit([
                    'uid' => get_uid(),
                    'authentication' => 1
                ]);
                if(!$res){
                    throw new Exception('数据写入失败');
                }

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                return $this->error('发生错误：' . $e->getMessage());
            }
            
            //返回提示
            return $this->success('提交成功！', $res);
        }
    }

    /**
     * 获取已填写的认证数据
     */
    public function detail()
    {
        $uid = get_uid();
        $map = [
            ['shopid', '=', $this->shopid],
            ['uid', '=', $uid]
        ];

        $data = $this->AuthenticationModel->getDataByMap($map);
        if($data){
            $data = $this->AuthenticationModel->handle($data);

            return $this->success('提交成功！', $data);
        }

        return $this->error('未能获取到数据');
        
    }

}