<?php
namespace app\channel\controller\api;

use app\common\controller\Base;
use app\common\model\Member;
use app\common\model\MemberSync;
use app\channel\facade\wechat\MiniProgram as MiniProgramServer;
use thans\jwt\facade\JWTAuth;
use think\facade\Cache;

/**
 * 微信小程序服务类
 * Class MiniProgram
 * @package app\channel\controller\service
 */
class WechatMiniProgram extends Base
{
    protected $MemberSyncModel;
    protected $MemberModel;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth' => ['only'=>['bindMobile']],
    ];
    function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        //初始化用户平台标识模型
        $this->MemberSyncModel = new MemberSync();
        //初始化用户模型
        $this->MemberModel = new Member();
    }

    /**
     * code 换取用户信息
     * @param $code
     */
    public function code($code)
    {
        $result = MiniProgramServer::user($code);
        if (!isset($result['openid'])){
            $this->error($result['errmsg']);
        }
        //查询是否注册过
        $map = [];
        $map[] = ['openid','=',$result['openid']];
        $map[] = ['type','=', 'weixin_mp'];
        $user = $this->MemberSyncModel->getDataByMap($map);
        if ($user){
            $user = query_user($user['uid'],['uid','nickname','avatar','email','mobile','realname','sex','qq','score1']);
            $this->MemberModel->updateLogin($user['uid']);
            $token = JWTAuth::builder(['uid'=>$user['uid']]);
            $token = 'Bearer ' . $token;
            $res = [
                'token'     => $token
            ];
            return $this->success('success',$res);
        }else{
            return $this->error('error','没有查询到用户信息');
        }

    }

    public function login()
    {
        $params = input('param.');
        $oauth = MiniProgramServer::user($params['code']);
        $result = MiniProgramServer::decryptData($oauth['session_key'],$params['iv'],$params['encrypted_data']);
        $data = [
            'unionid'   => $oauth['unionid'] ?? '',
            'openid'    => $oauth['openid'],
            'nickname'  => $result['nickName'],
            'avatar'    => $result['avatarUrl'],
            'sex'       => $result['gender'],
            'shopid'    => $params['shopid'],
            'oauth_type' => 'weixin_mp'
        ];
        $user = $this->MemberModel->oauth($data);
        if ($user){
            $token = JWTAuth::builder(['uid'=>$user['uid']]);
            $token = 'Bearer ' . $token;
            return $this->success('success',['token'=>$token]);
        }
        return $this->error('需要登录','login');
    }

    /**
     * 获取小程序码：适用于需要的码数量极多，或仅临时使用的业务场景
     * @return mixed
     */
    public function unlimitQrcode()
    {
        //小程序路径
        $path = input('param.path');
        //二维码url参数
        $scene = input('param.scene','');
        $width = input('param.width','500');
        $option = [
            'page' => $path,
            'width' => $width
        ];
        $result = MiniProgramServer::unlimitQrcode($scene, $option);
        Header("Content-type: image/jpeg");//直接输出显示jpg格式图片
        echo $result;
    }

    /**
     * 绑定手机号
     */
    public function bindMobile()
    {
        $uid = request()->uid;
        $code = input('code');
        $iv = input('iv');
        $encrypted = input('encrypted');
        $code_decode = MiniProgramServer::user($code);
        $session_key = $code_decode['session_key'];
        $data = MiniProgramServer::decryptData($session_key,$iv,$encrypted);
        //保存手机号
        $res = $this->MemberModel->edit([
            'uid' => $uid,
            'mobile' => $data['phoneNumber']
        ]);

        if ($res){
            return $this->success('绑定手机号成功');
        }
        return $this->error('绑定手机号失败');
    }
}