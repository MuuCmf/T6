<?php
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\MemberAuthentication as AuthenticationModel;

class Authentication extends Api
{
    protected $AuthenticationModel;

    public function __construct()
    {
        parent::__construct();
        $this->AuthenticationModel = new AuthenticationModel();
    }

    /**
     * 提交、编辑认证资料
     */
    public function edit()
    {

    }

}