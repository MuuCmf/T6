<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: OfficialAccountService.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2021/9/29
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */

namespace app\api\controller;

use app\common\model\QrcodeLogin;
use app\unions\model\WechatAutoReply;
use app\unions\facade\OfficialAccount;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Video;
use EasyWeChat\Kernel\Messages\Voice;

class OfficialAccountService
{
    public function callback()
    {
        //实例化公众号
        $app = OfficialAccount::getApp();
        //获取消息类型
        $message = $app->server->getMessage();

        //获取平台配置消息
        $map = [
            ['status', '=', 1],
        ];
        switch ($message['MsgType']) {
            case 'event'://事件
                $this->doEvent($message);
                break;
            default:
                //自动回复消息
                $map[] = ['type', '=', 2];
                $map[] = ['keyword', '=', $message['Content']];
                $this->doMessage($message, $map);
                break;
        }
        $app->server->serve();


        //token 回调
        OfficialAccount::serverOAath();
    }

    private function doEvent($message)
    {
        switch ($message['Event']) {
            case 'subscribe':
                //关注消息
                $map[] = ['type', '=', 1];
                $this->doMessage($message, $map);
                break;
            case 'scan':
                break;
        }
        //判断是否是扫码登录
        if (isset($message['EventKey'])){
            $event_key = convert_url_query($message['EventKey']);
            if (isset($event_key['islogin'])){
                //获取用户信息
                $user_info = OfficialAccount::getApp()->user->get($message['FromUserName']);
                //保存扫码信息
                $qrcode_login = [
                    'scene_key' => $event_key['scene_key'],
                    'metadata' => json_encode($user_info)
                ];
                $QrcodeLoginModel = (new QrcodeLogin());
                //是否登录过
                $has_login = $QrcodeLoginModel->where('scene_key',$event_key['scene_key'])->count();
                if ($has_login == 0){
                    $QrcodeLoginModel->edit($qrcode_login);
                    //登录消息
                    $map[] = ['type', '=', 3];
                    $this->doMessage($message, $map);
                }else{
                    //消息通知
                    $msg = new Text('二维码失效,请刷新后重试');
                    OfficialAccount::getApp()->customer_service->message($msg)->to($message['FromUserName'])->send();
                }

            }
        }
    }
    private function doMessage($message, $map)
    {
        $list = (new WechatAutoReply())->where($map)->order('sort', 'DESC')->order('id', 'DESC')->select()->toArray();
        foreach ($list as $item) {
            $msg = null;
            switch ($item['msg_type']) {
                case 'text':
                    $msg = new Text($item['text']);
                    break;
                case 'news':
                    if (isset($message['Event']) && $message['Event'] == 'subscribe') {
                        $msg = new Media($item['media_id'], 'mpnews');
                    } else {
                        $news = json_decode($item['material_json'], true);
                        $news = $news['content']['news_item'][0];
                        $items = [
                            new NewsItem([
                                'title' => $news['title'],
                                'description' => $news['digest'],
                                'url' => $news['url'],
                                'image' => $news['thumb_url']
                            ]),
                        ];
                        $msg = new News($items);
                    }
                    break;
                case 'image':
                    $msg = new Image($item['media_id']);
                    break;
                case 'voice':
                    $msg = new Voice($item['media_id']);
                    break;
                case 'video':
                    $msg = new Video($item['media_id']);
                    break;
            }
            //消息通知
            OfficialAccount::getApp()->customer_service->message($msg)->to($message['FromUserName'])->send();
        }
    }

    /**
     * 生成登录二维码
     * @param $scene_key
     * @return \think\response\Json
     */
    public static function loginQrcode($scene_key){
        //模板调用
        //{:app\\api\\controller\\OfficialAccountService::loginQrcode(create_unique())}
        $access_token = OfficialAccount::getToken();
        $qrcode_url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token['access_token'];
        $qrcode_url .= "&islogin=1";
        $qrcode_url .= "&scene_key=" . $scene_key;
        $result = OfficialAccount::createQrcode($qrcode_url , 60 * 60);
        $ticket = $result['ticket'];
        $qrcode = OfficialAccount::getQrcodeUrl($ticket);
        if (request()->isAjax()){
            return json($qrcode);
        }
        echo $qrcode;
    }

    /**
     * 网页授权
     */
    public function oauth()
    {
        $target_url = 'http://www.baidu.com';
        OfficialAccount::oauth($target_url);
    }

    /**
     * 网页授权回调
     */
    public function oauthCallback()
    {
        $app = OfficialAccount::getApp();
        $oauth = $app->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $user = $user->toArray();
        //处理用户数据
        dump($user);
        die();


        //跳回原网页
        $target_url = input('param.target_url');
        header("Location:{$target_url}");
        die;
    }
}