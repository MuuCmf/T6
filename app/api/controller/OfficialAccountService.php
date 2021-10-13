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
use app\unions\model\WechatAutoReply;
use app\unions\facade\OfficialAccount;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Video;
use EasyWeChat\Kernel\Messages\Voice;

class OfficialAccountService{
    public function callback()
    {
        //实例化公众号
        $app = OfficialAccount::getApp();
        //获取消息类型
        $message = $app->server->getMessage();

        //获取平台配置消息
        $map = [
            ['status' ,'=' ,1],
        ];
        if ($message['MsgType'] == 'event' && $message['Event'] == 'subscribe'){
            //关注消息
            $map[] = ['type','=',1];
        }else{
            //自动回复消息
            $map[] = ['type','=',2];
            $map[] = ['keyword','=',$message['Content']];
        }
        $list = (new WechatAutoReply())->where($map)->order('sort','DESC')->order('id','DESC')->select()->toArray();
        foreach ($list as $item){
            $msg = null;
            switch ($item['msg_type']){
                case 'text':
                    $msg = new Text($item['text']);
                    break;
                case 'news':
                    if (isset($message['Event']) && $message['Event'] == 'subscribe'){
                        $msg = new Media($item['media_id'], 'mpnews');
                    }else{
                        $news = json_decode($item['material_json'],true);
                        $news = $news['content']['news_item'][0];
                        $items = [
                            new NewsItem([
                                'title'       => $news['title'],
                                'description' => $news['digest'],
                                'url'         => $news['url'],
                                'image'       => $news['thumb_url']
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
            $app->customer_service->message($msg)->to($message['FromUserName'])->send();
        }
        $app->server->serve();


        //token 回调
        OfficialAccount::serverOAath();
    }
    public function autoReply(){
        $app = OfficialAccount::getApp();
        $list = $app->material->list('image', 0, 10);

        dump($list);
    }
}