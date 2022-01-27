<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Tominiprogram.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/1/27
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\admin\controller;
use think\facade\Db;
use think\facade\View;
use app\common\logic\Tominiprogram as TominiprogramLogic;
use app\common\model\Tominiprogram as TominiprogramModel;

/**
 * @title 跳转小程序
 * Class Tominiprogram
 * @package app\admin\controller
 */
class Tominiprogram extends Admin{
    protected $TominiprogramModel;
    protected $TominiprogramLogic;
    protected $shopid;
    public function __construct()
    {
        parent::__construct();
        $this->TominiprogramLogic = new TominiprogramLogic();
        $this->TominiprogramModel = new TominiprogramModel();
        $this->shopid = request()->param('shopid') ?? 0;
    }

    public function index(){
        $rows = 10;
        $map = [
            ['shopid' ,'=' ,$this->shopid]
        ];
        // 获取列表
        $lists = $this->TominiprogramModel->getListByPage($map, 'id DESC', '*', $rows);
        $pager = $lists->render();
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->TominiprogramLogic->_formatData($val);
        }
        unset($val);
        View::assign('page',$pager);
        View::assign('lists', $lists['data']);
        $this->setTitle('跳转小程序');
        return view();
    }

    public function edit(){
        $id = input('id',0);
        if (request()->isAjax()){
            $data = [
                'id'    => $id,
                'title' => request()->param('title'),
                'appid' => request()->param('appid'),
                'qrcode' => request()->param('qrcode'),
                'shopid' => $this->shopid,
                'type'  => request()->param('type')
            ];
            $result = $this->TominiprogramModel->edit($data);
            if ($result){
                $this->success('保存成功',null, url('index')->build());
            }
            $this->error('保存失败');
        }
        $id = input('id',0);
        $data = $this->TominiprogramModel->where('id',$id)->where('shopid',$this->shopid)->find();
        if ($data){
            $data = $data->toArray();
            $data = $this->TominiprogramLogic->_formatData($data);
        }else{
            $data = [];
        }
        View::assign([
           'data' => $data
        ]);
        return \view();
    }

    /**
     * 设置分组状态：删除=-1，禁用=0，启用=1
     * @param $status
     */
    public function del()
    {
        $status = input('status',0 , 'intval');
        $id = array_unique((array)input('id', 0));
        if ($id[0] == 0) {
            return $this->error('请选择要操作的数据');
        }
        $id = is_array($id) ? $id : explode(',', $id);
        $result = $this->TominiprogramModel->where('id' ,'in', $id)->delete();
        if ($result){
            $this->success('删除成功');
        }
        $this->error('删除失败,请稍后再试');
    }
}