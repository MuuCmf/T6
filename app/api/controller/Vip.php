<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\VipCard as VipCardModel;
use app\common\logic\VipCard as VipCardLogic;

class Vip extends Api
{
    protected $VipCardLogic;
    function __construct()
    {
        parent::__construct();
        $this->VipCardModel = new VipCardModel();
        $this->VipCardLogic = new VipCardLogic();
    }


}