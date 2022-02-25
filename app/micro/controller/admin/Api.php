<?php
namespace app\micro\controller\admin;

use think\facade\View;
use app\micro\controller\admin\Admin as MicroAdmin;
use app\common\model\Module as ModuleModel;
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
        $this->ModuleModel = new ModuleModel();
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
        $r = input('r', 8, 'intval');
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

    /**
     * 所有应用列表
     */
    public function applist()
    {   
        // 每页显示数量
        $r = input('r', 9, 'intval');
        $map = [
            ['is_setup','=',1]
        ];

        $moduleModel = $this->ModuleModel;
        $lists = $this->ModuleModel->getListByPage($map,'sort desc,id desc','*',$r)->each(function ($item,$key) use($moduleModel){
            //获取应用图标
            $item['icon'] = $moduleModel->getIcon($item['name'],$item['icon']);
            return $item;
        });

        return $this->success('success',$lists);
        
    }
}