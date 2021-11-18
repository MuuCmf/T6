<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Api.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/11/4
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\ucenter\controller;
use app\common\controller\Api as ApiBase;
use app\common\model\Member;
use think\Request;

class Api extends ApiBase{
    protected $MemberModel;
    function __construct()
    {
        parent::__construct();
        //添加token验证中间件
        $this->middleware[] = 'app\\common\\middleware\\CheckAuth';
        $this->MemberModel = new Member();
    }

    function getUserInfo(){
        $uid = request()->uid;
        //查询用户信息
        $user = query_user($uid,['uid','nickname','avatar','email','mobile','realname','sex','qq','balance','score1']);
        if ($user){
            $this->success('success',$user);
        }
        $this->error('没有查询到用户数据');
    }

    /**
     * 更换用户头像
     */
    function avatar(){
        $uid = request()->uid;
        $avatar = input('post.avatar');
        $res = $this->MemberModel->where('uid',$uid)->update([
            'avatar' => $avatar
        ]);
        if ($res !== false){
            $this->success('更新成功');
        }
        $this->error('更新失败');
    }
}