<?php
namespace app\common\queue;

use think\facade\Db;
use think\queue\Job;
use app\admin\model\AuthGroup;
use app\common\service\Mail;
use app\common\model\Message as MessageModel;
use app\common\model\MessageContent as MessageContentModel;

// # 建议开发测试时使用
//php think queue:listen
// # 建议生产环境使用
//php think queue:work --daemon（不加--daemon为执行单个任务）
class Message
{
    /**
     * 发送至用户
     */
    public function sendToUids(Job $job, $data){
        set_time_limit(0);//不限制程序执行时间
        $to_uids = $data['to_uids'];
        // 排除流失用户
        $to_uids = (new MessageModel())->_removeOldUser($to_uids);
        // 发送类型
        $send_type = $data['send_type'];
        if(in_array('email', $send_type)){
            $content = (new MessageContentModel())->find($data['content_id']);
        }
        // 开始发送
        foreach($to_uids as $i => $to_uid){
            // 发送站内信
            if(in_array('msg', $send_type)){
                $msg['shopid'] = $data['shopid'];
                $msg['uid'] = $data['uid'];
                $msg['to_uid'] = $to_uid;
                $msg['type_id'] = $data['type_id'];
                $msg['content_id'] = $data['content_id'];
                $msg['is_read'] = 0;
                $msg['status'] = 1;
                (new MessageModel())->save($msg);
            }

            // 发送邮件
            if(in_array('email', $send_type)){
                $user = query_user($to_uid,['email']);
                if(!empty($user) && !empty($user['email'])){
                    (new Mail())->sendMailLocal($user['email'], $content['title'], $content['content']);
                }
            }

            if ($i > 0 && $i % 100 == 0) { 
                sleep(5); 
            } 
        }
        
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
    }

    /**
     * 发送至用户组
     */
    public function sendToGroups(Job $job, $data)
    {
        set_time_limit(0);//不限制程序执行时间

        $to_groud_ids = $data['to_groud_ids'];
        $to_groud_ids = implode(',',$to_groud_ids);
        // 获取选择的用户组用户ID
        $prefix = config('database.connections.mysql.prefix');
        $l_table = $prefix . (AuthGroup::MEMBER);
        $r_table = $prefix . (AuthGroup::AUTH_GROUP_ACCESS);
        $where = [
            ['a.group_id', 'in', $to_groud_ids],
            ['status', '>=', 0]
        ];
        $user_list = Db::table($l_table . ' m')->join($r_table . ' a ',' m.uid=a.uid')->where($where)->field('m.uid')->select()->toArray();
        // 转为一维并去重
        $to_uids = array_unique(array_column($user_list, 'uid'));
        // 排除流失用户
        $to_uids = (new MessageModel())->_removeOldUser($to_uids);
        // 发送类型
        $send_type = $data['send_type'];
        if(in_array('email', $send_type)){
            $content = (new MessageContentModel())->find($data['content_id']);
        }
        // 开始发送
        foreach($to_uids as $i => $to_uid){
            // 发送站内信
            if(in_array('msg', $send_type)){
                $msg['shopid'] = $data['shopid'];
                $msg['uid'] = $data['uid'];
                $msg['to_uid'] = $to_uid;
                $msg['type_id'] = $data['type_id'];
                $msg['content_id'] = $data['content_id'];
                $msg['is_read'] = 0;
                $msg['status'] = 1;
                (new MessageModel())->save($msg);
            }

            // 发送邮件
            if(in_array('email', $send_type)){
                $user = query_user($to_uid,['email']);
                if(!empty($user) && !empty($user['email'])){
                    (new Mail())->sendMailLocal($user['email'], $content['title'], $content['content']);
                }
            }

            if ($i > 0 && $i % 100 == 0) { 
                sleep(5); 
            } 
        }
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();

    }

    /**
     * 任务失败处理
     */
    public function failed($data){
    
        // ...任务达到最大重试次数后，失败了
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private static function parse($type, $value){
        switch ($type) {
            case 'entity': //解析成数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
            break;
        }
        return $value;
    } 
    
    

}