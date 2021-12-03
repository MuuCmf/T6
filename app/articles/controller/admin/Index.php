<?php
namespace app\articles\controller\admin;

use think\exception\HttpResponseException;
use think\exception\ValidateException;

class Index extends Admin
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 入口文章列表页
     */
    public function index()
    {
        $this->redirect(url('articles/admin.articles/lists'));
    }

}