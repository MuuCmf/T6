<?php
namespace app\ucenter\controller;

use think\facade\View;
use app\common\model\Address as AddressModel;
use app\common\logic\Address as AddressLogic;

class Address extends Base
{
    protected $AddressModel;
    protected $AddressLogic;
    
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->AddressModel = new AddressModel();
        $this->AddressLogic = new AddressLogic();
    }

    /**
     * 地址列表
     */
    public function lists()
    {
        $uid = get_uid();
        //初始化查询条件
        $map = [
            ['shopid' ,'=' , $this->shopid],
            ['uid', '=', $uid],
            ['status', '=' , 1]
        ];
        $lists = $this->AddressModel->getList($map,99);
        $lists = $lists->toArray();
        foreach ($lists as &$item){
            $item = $this->AddressLogic->formatData($item);
        }
        unset($item);
        View::assign('lists', $lists);

        // 设置菜单识别TAB
        View::assign('tab', 'address');
        $this->setTitle('我的地址');
        // 输出模板
        return View::fetch();
    }

    /**
     * 新增/编辑地址
     */
    public function edit()
    {
        $id = input('id',0,'intval');
        $title = $id ? "编辑" : "新建";
        View::assign('title',$title);

        $data = [];
        if(!empty($id)){
            $data = $this->AddressModel->getDataById($id);
            $data = $this->AddressLogic->formatData($data);
        }
        View::assign('data',$data);

        $this->setTitle($title.'地址');
        // 输出模板
        return View::fetch();
    }
}