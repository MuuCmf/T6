<?php
namespace app\micro\controller\admin;

use think\facade\View;
use app\micro\controller\admin\Admin as MicroAdmin;
use app\common\model\Module;
use app\micro\model\MicroPage as PageModel;
use app\micro\logic\Page as PageLogic;
use app\micro\service\Diy as DiyService;

class Api extends MicroAdmin
{
    protected $PageLogic;
    protected $PageModel;
    function __construct()
    {
        parent::__construct();
        $this->ModuleModel = new Module();
        $this->PageLogic = new PageLogic();
        $this->PageModel = new PageModel();
    }

    /**
     * @title 自定义页面列表
     */
    public function pages()
    {
        $keyword = input('get.keyword','');
        View::assign('keyword', $keyword);
        
        // 初始化查询条件
        $map = [
            ['shopid','=', 0],
            ['port_type', '=', 'mobile'],
            ['status','=', 1],
        ];
        if (!empty($keyword)){
            $map[] = ['title','like',"%{$keyword}%"];
        }
        // 每页显示数量
        $r = input('r', 15, 'intval');
        $lists = $this->PageModel->getListByPage($map,'id desc,create_time desc', '*', $r);
        $pager = $lists->render();
        View::assign('pager',$pager);
        $lists = $lists->toArray();

        foreach ($lists['data'] as &$item){
            $item = $this->PageLogic->formatData($item);
        }
        unset($item);

        if (request()->isAjax()){
            return $this->success('success',$lists);
        }
    }
}