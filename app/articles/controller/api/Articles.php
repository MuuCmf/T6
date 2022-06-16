<?php
namespace app\articles\controller\api;

use app\common\controller\Api;
use app\articles\model\ArticlesCategory as CategoryModel;
use app\articles\logic\Category as CategoryLogic;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;

class Articles extends Api {
    protected $ArticlesModel;
    protected $ArticlesLogic;
    function __construct()
    {
        parent::__construct();
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
    }

    

}