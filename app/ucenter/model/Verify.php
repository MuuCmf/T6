<?php
namespace app\ucenter\model;

use think\Model;

/**
 * 验证码模型
 */
class Verify extends Model
{
    protected $autoWriteTimestamp = true;
    // 关闭自动写入update_time字段
    protected $updateTime = false;

    /**
     * 生成验证码
     */
    public function addVerify($account, $type, $uid=0)
    {
        $uid = $uid ? $uid:is_login();
        if ($type == 'mobile' || $type == 'email') {
            // 生成6位验证码
            $verify = create_rand(6, 'num');
        }

        $this->where(['account'=>$account,'type'=>$type])->delete();

        $data['verify'] = $verify;
        $data['account'] = $account;
        $data['type'] = $type;
        $data['uid'] = $uid;
        
        $res = $this->save($data);
        if(!$res){
            return false;
        }
        return $verify;
    }


    public function getVerify($id){
        $verify = $this->where(['id'=>$id])->value('verify');
        return $verify;
    }

    /**
     * 检测验证码
     * @param      <type>   $account  The account
     * @param      <type>   $type     The type
     * @param      <type>   $verify   The verify
     * @return     boolean  ( description_of_the_return_value )
     */
    public function checkVerify($account, $type, $verify){
        $verify = $this->where(['account'=>$account,'type'=>$type,'verify'=>$verify])->find();
        if(!$verify){
            return false;
        }
        // 删除该验证码
        $this->where(['account'=>$account,'type'=>$type])->delete();
        // 删除一天前的验证码
        $this->where('create_time', '<=', get_some_day(1))->delete();

        return true;
    }


}