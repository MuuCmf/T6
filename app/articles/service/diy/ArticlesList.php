<?php
namespace app\articles\service\diy;

use think\facade\Cache;
use app\articles\model\ArticlesConfig as ConfigModel;
use app\articles\logic\Config as ConfigLogic;
use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;

class ArticlesList
{
    protected $ArticlesModel;
    protected $ArticlesLogic;

    public $_title   = '文章列表';
    public $_type    = 'articles_list';
    public $_icon    = 'group';
    public $_template     = [
        'script' => APP_PATH . 'articles/view/diy/articles_list/script.html',
        'view' => APP_PATH . 'articles/view/diy/articles_list/view.html',
    ];
    public $_api = [];
    public $_static = [
        'mobile' => [
            'css' => PUBLIC_PATH . '/static/articles/diy/mobile/articles_list.min.css',
            'js' => PUBLIC_PATH . '/static/articles/diy/mobile/articles_list.min.js',
        ],
        'pc' => [
            'css' => '',
            'js' => ''
        ]
    ];

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->ArticlesModel = new ArticlesModel;
        $this->ArticlesLogic = new ArticlesLogic;
        $this->_api = $this->setApi();
    }

    public function setApi()
    {
        return [
            // 列表接口
            'list' => url('articles/admin.articles/lists'), 
            // 分类接口
            'category' => url('articles/admin.category/tree')
        ];
    }

    /**
     * 获取应用配置
     */
    public function getAppConfig()
    {
        // 获取应用配置数据
        $config_data = Cache::get('MUUCMF_Articles_CONFIG_DATA');
        if (empty($config_data)){
            $config_data = (new ConfigModel)->getDataByMap(['shopid' => 0]);
            $config_data = (new ConfigLogic)->formatData($config_data);
            Cache::set('MUUCMF_ARTICLES_CONFIG_DATA',$config_data);
        }

        return $config_data;
    }

    /**
     * 微页约定获取列表数据处理方法
     */
    public function handle($data, $shopid)
    {    
        if(!isset($data['rank'])){
            $data['rank'] = 1;
        }
        $category_id = intval($data['category_id']);
        $map = $this->ArticlesLogic->getMap(0, '', $category_id, 1);
        $rows = $data['rows'];
        $order = $data['order_field'].' '.$data['order_type'];
        $list = $this->ArticlesModel->getList($map, $rows, $order);
        if(!empty($list)){
            $list = $list->toArray();
            
            foreach($list as &$v){
                $v = $this->ArticlesLogic->formatData($v);
            }
            unset($v);
            $data['list'] = $list;
        }

        $data['config'] = $this->getAppConfig();

        return $data;
    }
}