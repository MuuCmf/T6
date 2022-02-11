<?php

return [
    'info' => [
        'name' => 'articles',
        'alias' => '文章'
    ],
    'panel' => [
        'article_list' => [
            'type' => 'article_list',
            'title' => '文章列表',
            'icon' => 'th-list',  // fa图标
            'list_api' => url('articles/admin.articles/lists'),
            'category_api' => url('articles/admin.category/tree'),
            'bind' => [
                'class' => 'app\\articles\\model\\ArticlesArticles',
                'action' => 'articlesList'
            ],
        ]
    ]
];