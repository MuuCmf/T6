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
    public $_api = [];
    public $_teminal = ['mobile','pc'];
    public $_template     = [
        'mobile' => [
            'script' => APP_PATH . 'articles/view/diy/mobile/articles_list/script.html',
            'view' => APP_PATH . 'articles/view/diy/mobile/articles_list/view.html',
        ],
        'pc' => [
            'script' => APP_PATH . 'articles/view/diy/pc/articles_list/script.html',
            'view' => APP_PATH . 'articles/view/diy/pc/articles_list/view.html',
        ]
    ];
    
    public $_static = [
        'mobile' => [
            'css' => PUBLIC_PATH . '/static/articles/diy/mobile/articles_list.min.css',
            'js' => PUBLIC_PATH . '/static/articles/diy/mobile/articles_list.min.js',
        ],
        'pc' => [
            'css' => PUBLIC_PATH . '/static/articles/diy/pc/articles_list.min.css',
            'js' => PUBLIC_PATH . '/static/articles/diy/pc/articles_list.min.js',
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
    public function getAppConfig($shopid = 0)
    {
        // 获取应用配置数据
        $config_data = Cache::get(request()->host() . '_MUUCMF_Articles_CONFIG_DATA_' . $shopid);
        if (empty($config_data)){
            $config_data = (new ConfigModel)->getDataByMap(['shopid' => $shopid]);
            $config_data = (new ConfigLogic)->formatData($config_data);
            Cache::set(request()->host() . '_MUUCMF_ARTICLES_CONFIG_DATA_' . $shopid, $config_data);
        }

        return $config_data;
    }

    /**
     * 微页约定获取列表数据处理方法
     */
    public function handle($shopid = 0, $data = [])
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

        $data['config'] = $this->getAppConfig($shopid);

        return $data;
    }
}