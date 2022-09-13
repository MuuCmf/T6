<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Message as MessageModel;
use app\common\model\MessageType as MessageTypeModel;

/**
 * @title 消息接口类
 * @package app\api\controller
 */
class Message extends Api
{

    protected $MessageModel;
    protected $MessageTypeModel;

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
        $uid = request()->uid;
        // 查询条件
        $t_map[] = ['shopid', '=', $this->shopid];
        $t_map[] = ['status', '=', 1];
        $list = $this->MessageTypeModel->getList($t_map);
        foreach($list as &$val){
            $val = $this->MessageTypeModel->formatData($val);
            // 未读消息数量
            $map[] = ['to_uid', '=', $uid];
            $map[] = ['type_id', '=', $val['id']];
            $map[] = ['is_read', '=', 0];
            $map[] = ['status', '=', 1];
            $num = (new MessageModel())->where($map)->count();
            if($num > 99){
                $val['unread'] = '99+';
            }else{
                $val['unread'] = $num;
            }
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
        if(!empty($type_id)){
            $map[] = ['type_id', '=', $type_id];
        }
        
        // 搜索关键字
        $keyword = input('keyword', '', 'text');
        if(!empty($keyword)){
            $map[] = ['title', 'like', '%' . $keyword . '%'];
        }

        $fields = '*';
        $rows = input('rows', 20, 'intval');
        $lists = $this->MessageModel->getListByPage($map, 'id desc,create_time desc', $fields, $rows);
        $lists = $lists->toArray();

        foreach($lists['data'] as &$val){
            $val = $this->MessageModel->formatData($val);
        }
        unset($val);

        return $this->success('success',$lists);
    }

    /**
     * 消息详细内容
     */
    public function detail()
    {
        $uid = get_uid();
        $id = input('id', '0', 'intval');
        if(!empty($id)){
            $map = [
                'id' => $id,
                'to_uid' => $uid,
                'status' => 1
            ];
            $data = $this->MessageModel->where($map)->find();
            $data = $data->toArray();
            $data = $this->MessageModel->formatData($data);
            // 设置已读
            $this->MessageModel->where($map)->update([
                'is_read' => 1
            ]);

            return $this->success('success', $data);

        }

        return $this->error('参数错误');
        
    }

    /**
     * 未读消息数量
     * 给消息类型ID返回类型数量，未给消息类型ID返回所有未读数量
     */
    public function unread()
    {
        $uid = request()->uid;
        $map[] = ['to_uid', '=', $uid];
        $map[] = ['shopid' ,'=' ,$this->shopid];
        $type_id = input('type_id', 0, 'intval');
        if(!empty($type_id)){
            $map[] = ['type_id', '=', $type_id];
        }else{
            $t_map[] = ['shopid', '=', $this->shopid];
            $t_map[] = ['status', '=', 1];
            $list = $this->MessageTypeModel->getList($t_map);
            $type_ids = [];
            foreach($list as $v){
                $type_ids[] = $v['id'];
            }
            $map[] = ['type_id', 'in', $type_ids];
        }
        $map[] = ['is_read', '=', 0];
        $map[] = ['status', '=', 1];
        
        $num = $this->MessageModel->where($map)->count();

        if($num > 99){
            $friendly_num = '99+';
        }else{
            $friendly_num = $num;
        }

        return $this->success('success',[
            'num' => $num,
            'friendly_num' => $friendly_num
        ]);

    }

    /**
     * 全部未读消息标识为已读
     */
    public function isread()
    {
        $uid = request()->uid;
        $map[] = ['to_uid', '=', $uid];
        $map[] = ['shopid' ,'=' ,$this->shopid];
        $map[] = ['is_read', '=', 0];

        $res = $this->MessageModel->where($map)->update(['is_read'=> 1]);

        if($res){
            return $this->success('success', '更新成功');
        }

        return $this->error('error', '更新失败');
    }
    

}