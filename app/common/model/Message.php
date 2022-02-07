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
     * 消息发送
     */
    public function sendMessage($shopid = 0, $uid = 0, $to_uids, $title = '您有新的消息', $description = '', $content = '', $type = 1)
    {
        $to_uids = is_array($to_uids) ? $to_uids : explode(',', $to_uids);
        // 排除流失用户
        $to_uids = $this->_removeOldUser($to_uids);
        if(!count($to_uids)){
            return false;
        }

        $uid == 0 && $uid = is_login();
        $to_uids = is_array($to_uids) ? $to_uids : array($to_uids);

        $args = '';
        // 写入消息内容
        $content_id = (new MessageContent())->addMessageContent($shopid, $title, $description, $content, $args);

        // # 建议开发测试时使用
        //php think queue:listen
        // # 建议生产环境使用
        //php think queue:work --daemon（不加--daemon为执行单个任务）

        // 发送消息


        // 仅针对用户组方式使用消息队列
        foreach ($to_uids as $to_uid) {
            $isPushed = Queue::push('\app\common\job\Queue@sendMessage', ['demo']);
            if( $isPushed !== false ){  
                echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
            }else{
                echo 'Oops, something went wrong.';
            }
            // $message['to_uid'] = $to_uid;
            // $message['content_id'] = $content_id;
            // $message['uid'] = $uid;
            // $message['type'] = $type;
            // $message['status'] = 1;

            //$this->save($message);
        }

        return true;
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