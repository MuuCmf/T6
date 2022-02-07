<?php
namespace app\common\queue;

use think\queue\Job;
use think\facade\Log;
use app\common\model\Message as MessageModel;

// # 建议开发测试时使用
//php think queue:listen
// # 建议生产环境使用
//php think queue:work --daemon（不加--daemon为执行单个任务）
class Message
{
    
    public function send(Job $job, $data){
        
        Log::write('开始执行' . $data['shopid']);
        // 
        $to_uids = $data['to_uids'];
        // 开始写表
        foreach($to_uids as $to_uid){

            $msg['shopid'] = $data['shopid'];
            $msg['uid'] = $data['uid'];
            $msg['to_uid'] = $to_uid;
            $msg['type_id'] = $data['type_id'];
            $msg['content_id'] = $data['content_id'];
            $msg['send_type'] = $data['send_type'];
            
            (new MessageModel())->save($msg);
        }
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
        
    }

    public function failed($data){
    
        // ...任务达到最大重试次数后，失败了
    }
    
    

}