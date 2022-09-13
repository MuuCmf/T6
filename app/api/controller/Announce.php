<?php
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\Announce as AnnounceModel;
use app\common\logic\Announce as AnnounceLogic;

class Announce extends Api
{
    protected $model;
    protected $logic;

    function __construct()
    {
        parent::__construct();
        $this->model = new AnnounceModel();
        $this->logic = new AnnounceLogic();
    }

    public function detail(){
        $id = input('get.id',0);
        $data = $this->model->getDataById($id);
        $data = $this->logic->formatData($data);

        return $this->success('获取成功！',$data);
    }

    public function lists(){

        //初始化查询条件
        $map = [
            ['shopid' ,'=' , $this->shopid],
            ['status', '=' , 1]
        ];
        $lists = $this->model->getList($map,5);
        foreach ($lists as &$item){
            $item = $this->logic->formatData($item);
        }
        unset($item);

        return $this->success('获取成功！',$lists);
    }
}