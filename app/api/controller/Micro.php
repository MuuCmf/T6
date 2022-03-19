<?php
/**
 * +----------------------------------------------------------------------
 *                                  |
 *     __     __  __     __  __     | FILE: Micro.php
 *    /\ \   /\_\_\_\   /\_\_\_\    | AUTHOR: 季骁宣
 *   _\_\ \  \/_/\_\/_  \/_/\_\/_   | EMAIL: jxx0410@sina.com
 *  /\_____\   /\_\/\_\   /\_\/\_\  | QQ: 516036855
 *  \/_____/   \/_/\/_/   \/_/\/_/  | DATETIME: 2022/3/18
 *                                  |-------------------------------------
 *                                  | 登山则情满于山,观海则意溢于海
 * +----------------------------------------------------------------------
 */
namespace app\api\controller;
use app\common\controller\Base;
use app\micro\logic\Page as MicroPageLogic;
use app\micro\model\MicroPage as MicroPageModel;

/**
 * @title 主页接口类
 * Class Micro
 * @package app\api\controller
 */
class Micro extends Base{
    protected $params;
    protected $MicroPageModel;
    protected $MicroPageLogic;

    protected $middleware = [
        'app\\common\\middleware\\CheckParam',
    ];
    function __construct()
    {
        parent::__construct();
        $this->MicroPageModel = new MicroPageModel();
        $this->MicroPageLogic = new MicroPageLogic();
        $this->params = request()->param();

    }

    /**
     * @title 获取页面数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPageData(){
        $map = [
            ['shopid', '=', $this->params['shopid']],
            ['status', '=', 1]
        ];
        //是否存在指定页面搜索
        if (isset($this->params['micro_id'])){
            $map[] = ['id', '=', $this->params['micro_id']];
        }else{
            //获取首页
            $map[] = ['home', '=', 1];
        }
        //页面类型
        $type = $this->params['port_type'] ?? 'mobile';
        $map[] = ['port_type', '=', $type];

        $data = $this->MicroPageModel->field('id,title,header,header_show,footer_show,description,data,shopid,port_type,status')->where($map)->find();
        if ($data){
            $data = $data->toArray();
            $data = $this->MicroPageLogic->formatData($data);
            $data = $this->MicroPageLogic->handlingNoParamJson($data);
            $this->success('获取页面数据成功',$data);
        }
        $this->error('没有查询到数据');
    }


}