<?php
// +----------------------------------------------------------------------
// | 微页应用 MuuCmf_micro V1.0.0
// | 多应用链接至页面数据处理
// +----------------------------------------------------------------------
namespace app\articles\service;

class Link
{

    public function links()
    {
        return [
            'articles_list' => [
                'icon' => 'bars',
                'link_type' => 'articles_list',
                'link_type_title' => '文章列表',
                'api' => url('articles/api.Articles/lists'),
                'category_api' => url('articles/api.Category/tree'),
                'static' => [
                    'css' => PUBLIC_PATH . '/static/articles/diy/link/articles_list.min.css',
                    'js' => PUBLIC_PATH . '/static/articles/diy/link/articles_list.min.js',
                ]
            ],
            'articles_detail' => [
                'icon' => 'file-text-o',
                'link_type' => 'articles_detail',
                'link_type_title' => '文章详情',
                'api' => url('articles/api.Articles/lists'),
                'category_api' => url('articles/api.Category/tree'),
                'static' => [
                    'css' => PUBLIC_PATH . '/static/articles/diy/link/articles_detail.min.css',
                    'js' => PUBLIC_PATH . '/static/articles/diy/link/articles_detail.min.js',
                ]
            ],
        ];
    }

    /**
     * 链接至参数转路径
     */
    public function linkToUrl($linkParam = [], $teminal = 'pc')
    {
        $url = '';
        if($teminal == 'pc'){
            switch($linkParam['type']){
                // 列表
                case 'articles_list':
                    $url_params = http_build_query($linkParam['param']);
                    $url = '/articles/lists.html?' . $url_params;
                break;

                // 详情
                case 'articles_detail':
                    $url_params = http_build_query($linkParam['param']);
                    $url = '/articles/detail.html?' . $url_params;
                break;
                
                default:
            }
            
        }
        
        return $url;
    }


}