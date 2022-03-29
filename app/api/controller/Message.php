<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Message as MessageModel;
use app\common\model\MessageType as MessageTypeModel;

/**
 * @title 消息接口类
 * @package app\api\controller
 */
class Message extends Api{
    protected $params;
    protected $MessageModel;

    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    function __construct()
    {
        parent::__construct();
        $this->MessageModel = new MessageModel();
        $this->MessageTypeModel = new MessageTypeModel();
    }

    /**
     * 消息类型
     */
    public function type()
    {
        // 查询条件
        $map[] = ['shopid', '=', $this->shopid];
        $map[] = ['status', '=', 1];
        $list = $this->MessageTypeModel->getList($map);
        foreach($list as &$val){
            $val = $this->MessageTypeModel->formatData($val);
        }
        unset($val);

        return $this->success('success',$list);
    }

    /**
     * 消息列表
     */
    public function lists(){
        $uid = request()->uid;
        $type_id = input('type_id', 0, 'intval');
        // 查询条件
        $map[] = ['shopid', '=', $this->shopid];
        $map[] = ['status', '=', 1];
        $map[] = ['to_uid', '=', $uid];
        $map[] = ['type_id', '=', $type_id];

        // 搜索关键字
        $keyword = input('keyword', '', 'text');
        if(!empty($keyword)){
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $fields = '*';
        $rows = input('rows', 20, 'intval');
        $lists = $this->MessageModel->getListByPage($map, 'id desc,create_time desc', $fields, $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();

        foreach($lists['data'] as &$val){
            $val = $this->MessageModel->formatData($val);
        }
        unset($val);

        return $this->success('success',$lists);
    }

    /**
     * 未读消息数量
     * 给消息类型ID返回类型数量，未给消息类型ID返回所有未读数量
     */
    public function unread()
    {
        $uid = request()->uid;
        $map[] = ['to_uid', '=', $uid];
        $type_id = input('type_id', 0, 'intval');
        if(!empty($type_id)){
            $map[] = ['type_id', '=', $type_id];
        }
        $map[] = ['is_read', '=', 0];
        $map[] = ['status', '=', 1];
        
        $num = $this->MessageModel->where($map)->count();

        return $this->success('success',$num);

    }
    

}