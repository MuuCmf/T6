<?php
namespace app\admin\controller;

use think\facade\View;
use think\facade\Cache;
use thans\jwt\facade\JWTAuth;
use app\common\controller\Base;
use app\common\model\Member as CommonMember;

class Common extends Base
{
    /**
     * 用户登录
     */
    public function login()
    {
        if (request()->isPost()) {
                //获取参数
            $account = input('post.account', '', 'text');
            $password = input('post.password', '', 'text');

            if(empty($account)){
                return $this->error('账号不能为空');
            }
            // 验证账号和密码
            $commonMemberModel = new CommonMember;
            $uid = $commonMemberModel->verifyUserPassword($account, $password);
            if($uid < 1){
                return $this->error($commonMemberModel->error);
            }

            // 登录
            $res = $commonMemberModel->login($uid);

            if ($res) {
                $token = JWTAuth::builder(['uid' => $uid]); //参数为用户认证的信息，请自行添加
                return $this->success('登录成功', $token, url('admin/index/index'));
            } else {
                return $this->error($commonMemberModel->error);
            }
        }else{
            // 模板输出
            return View::fetch();
        }
    }
    
    /* 退出登录 */
    public function logout(){

        if(is_login()){
            $commonMemberModel = new CommonMember;
            $commonMemberModel->logout(is_login());
            return $this->success('退出成功','', url('ucenter/common/login'));
        } else {
            return $this->error('error');
        }
    }

    /**
     * 清理缓存 clear cache
     * @return [type] [description]
     */
    public function clearCache(){

        // 清理缓存
        $res = Cache::clear();

        // 清理运行目录
        $dirname = runtime_path();
        $res2 = rmdirs($dirname, false);

        if($res) return $this->success('缓存清理成功');
    }

}