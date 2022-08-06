<?php
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\Address as AddressModel;
use app\common\logic\Address as AddressLogic;

class Address extends Api
{
    protected $model;
    protected $logic;
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    function __construct()
    {
        parent::__construct();
        $this->model = new AddressModel();
        $this->logic = new AddressLogic();
    }

    /**
     * 获取默认地址
     */
    public function default(){
        $uid = request()->uid;
        $map = [
            ['uid','=',$uid],
            ['shopid' ,'=' , $this->shopid],
            ['first','=',1],
            ['status','=',1],
        ];
        $data = $this->model->getDataByMap($map);
        if (!$data){
            $map = [
                ['uid','=',$uid],
                ['status','=',1],
                ['shopid' ,'=' , $this->shopid],
            ];
            $data = $this->model->getDataByMap($map);
        }
        $data = $this->logic->formatData($data);
        return $this->success('获取成功！',$data);
    }

    public function detail(){
        $id = input('get.id',0);
        $data = $this->model->getDataById($id);
        $data = $this->logic->formatData($data);
        return $this->success('获取成功！',$data);
    }

    public function lists(){
        $uid = request()->uid;
        //初始化查询条件
        $map = [
            ['shopid' ,'=' , $this->shopid],
            ['uid', '=', $uid],
            ['status', '=' , 1]
        ];
        $lists = $this->model->getList($map,99);
        foreach ($lists as &$item){
            $item = $this->logic->formatData($item);
        }
        unset($item);
        return $this->success('获取成功！',$lists);
    }
    public function edit(){
        if (request()->isPost()){
            $param = request()->post();
            $uid = request()->uid;
            $data = [
                'id' => $param['id'],
                'uid' => $uid,
                'shopid' => $this->shopid,
                'name' => $param['name'],
                'phone' => $param['phone'],
                'pos_province' => $param['pos_province'],
                'pos_city' => $param['pos_city'],
                'pos_district' => $param['pos_district'],
                'address' => $param['address'],
                'first' => $param['first'], //默认地址
                'status' => 1
            ];

            // 验证数据
            if(empty($data['name'])){
                $this->error('姓名不能为空！');
            }
            if(empty($data['phone'])){
                $this->error('手机号码不能为空！');
            }
            if(empty($data['pos_province']) || empty($data['pos_city']) || empty($data['pos_district'])){
                $this->error('所在地区未选择！');
            }
            if(empty($data['address'])){
                $this->error('详细地址不能为空！');
            }

            //写入数据
            $res = $this->model->edit($data);
            if($res){
                //关闭其他默认地址
                if ($param['first'] == 1){
                    $id = is_object($res) ? $res->id : $res;
                    $this->model->where([
                        ['id','<>',$id],
                        ['shopid','=',$this->shopid],
                        ['uid','=',$uid]
                    ])->update([
                        'update_time' => time(),
                        'first' => 0
                    ]);
                }
                //返回提示
                return $this->success('编辑成功！', $res);
            }else{
                return $this->error('编辑失败！');
            }
        }
    }

    /**
     * 设为默认地址
     */
    public function setDefault(){
        $uid = request()->uid;
        $id  = input('get.id');
        $this->model->where([
            ['uid','=',$uid],
            ['shopid','=',$this->shopid]
        ])->update([
            'update_time' => time(),
            'first' => 0
        ]);
        $res = $this->model->where([
            ['id','=',$id],
            ['shopid','=',$this->shopid]
        ])->update([
            'update_time' => time(),
            'first' => 1
        ]);
        if($res){
            return $this->success('设置成功！',$res,'refresh');
        }else{
            return $this->error('设置失败！');
        }
    }

    public function del($id){
        $res = $this->model->edit([
            'id' => $id,
            'status' => -1
        ]);
        if($res){
            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败！');
        }
    }
}