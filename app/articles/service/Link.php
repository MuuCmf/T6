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
                'api' => url('articles/admin.articles/lists'),
                'category_api' => url('articles/admin.category/tree'),
                'static' => [
                    'css' => PUBLIC_PATH . '/static/articles/diy/link/articles_list.min.css',
                    'js' => PUBLIC_PATH . '/static/articles/diy/link/articles_list.min.js',
                ]
            ],
            'articles_detail' => [
                'icon' => 'file-text-o',
                'link_type' => 'articles_detail',
                'link_type_title' => '文章详情',
                'api' => url('articles/admin.articles/lists'),
                'category_api' => url('articles/admin.category/tree'),
                'static' => [
                    'css' => PUBLIC_PATH . '/static/articles/diy/link/articles_detail.min.css',
                    'js' => PUBLIC_PATH . '/static/articles/diy/link/articles_detail.min.js',
                ]
            ],
        ];
    }


}