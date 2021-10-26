<?php
namespace app\admin\controller;

use think\facade\View;
use app\common\model\Member as MemberModel;
use app\common\model\ScoreLog as ScoreLogModel;
use app\common\model\ScoreType as ScoreTypeModel;
use app\common\model\Vip as VipModel;

/**
 * 付费会员卡控制器
 */
class Vip extends Admin
{
    protected $MemberModel;
    protected $VipModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();

        $this->MemberModel = new MemberModel();
        $this->VipModel = new VipModel();
    }

    /**
     * 付费会员列表
     */
    public function list()
    {

    }

    /**
     * 卡项目管理
     */
    public function card()
    {

    }

    /**
     * 用户开卡订单
     */
    public function orders()
    {

    }

}
