<?php
namespace app\common\model;

use think\facade\Queue;
use app\common\model\Member as MemberModel;
use app\common\model\MessageType;
use app\common\model\MessageContent;

class Message extends Base
{
    //自动写入创建和更新的时间戳字段
    protected $autoWriteTimestamp = true; 

    /**
     * 发送消息
     * 
    */
    public function sendMessage($shopid = 0, $uid = 0, $to_uids, $title = '您有新的消息', $description = '', $content = '', $type_id = 1, $send_type = 'msg')
    {
        // 指定用户ID
        $to_uids = is_array($to_uids) ? $to_uids : explode(',', $to_uids);
        // 排除流失用户
        $to_uids = $this->_removeOldUser($to_uids);
        if(!count($to_uids)){
            return false;
        }

        $uid == 0 && $uid = is_login();
        
        // 写入消息内容
        $content_id = (new MessageContent())->addMessageContent($shopid, $title, $description, $content);
        // 组装传递的参数
        $data = [
            'shopid' => $shopid,
            'uid' => $uid,
            'to_uids' => $to_uids,
            'type_id' => $type_id,
            'content_id' => $content_id,
            'send_type' => $send_type
        ];
        

        // 发送消息
        // 仅针对用户组方式使用消息队列
        // # 建议开发测试时使用
        //php think queue:listen
        // # 建议生产环境使用
        //php think queue:work --daemon（不加--daemon为执行单个任务）
        $isPushed = Queue::push('\app\common\queue\Message@send', $data);
        if( $isPushed !== false ){  
            return true;
        }
        
        return false;
    }

    /**
     * 去除一个月没有登录的用户
     * @param $to_uids
     * @return array
     */
    private function _removeOldUser($to_uids)
    {
        $to_uids = is_array($to_uids) ? implode(',',$to_uids) : $to_uids;
        if(!empty($to_uids)){
            $map[] = ['uid', 'in', $to_uids];
        }
        
        $map[] = ['status', '=', 1];
        $map[] = ['last_login_time', '>',get_time_ago('month')];

        $uids = (new MemberModel())->where($map)->field('uid')->select()->toArray();
        $uids = array_column($uids,'uid');
        return $uids;
    }

}