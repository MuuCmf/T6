<?php
declare (strict_types = 1);

namespace app\ucenter\controller;

use think\facade\View;
use app\common\controller\Common;
use app\common\model\Message as MessageModel;
use app\common\model\MessageType as MessageTypeModel;

class Message extends Common
{
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    /**
     * 消息模态框
     */
    public function modal()
    {
        // 获取消息类型
        $MessageModel = new MessageModel();
        $MessageTypeModel = new MessageTypeModel();
        // 查询条件
        $map[] = ['shopid', '=', 0];
        $map[] = ['status', '=', 1];
        $type_list = $MessageTypeModel->getList($map);
        foreach($type_list as &$val){
            $val = $MessageTypeModel->formatData($val);
        }
        unset($val);
        View::assign('type_list', $type_list);
        
        return View::fetch();
    }

}