<?php
// 连接至的配置参数

return [
    [
        'icon' => 'fa-desktop',
        'sys_type' => 'detail',
        'link_type' => 'micro_page',
        'link_type_title' => '自定义页面',
        'api' => url('micro/admin.page/api')
    ],[
        'icon' => 'fa-bars',
        'sys_type' => 'list',
        'link_type' => 'knowledge_list',
        'link_type_title' => '点播课列表',
        'api' => url('classroom/admin.knowledge/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-file-text-o',
        'sys_type' => 'detail',
        'link_type' => 'knowledge_detail',
        'link_type_title' => '点播课详情',
        'api' => url('classroom/admin.knowledge/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-indent',
        'sys_type' => 'list',
        'link_type' => 'column_list',
        'link_type_title' => '专栏列表',
        'app' => 'classroom',
        'controller' => 'column',
        'action' => 'lists',
        'api' => url('classroom/admin.column/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-newspaper-o',
        'sys_type' => 'detail',
        'link_type' => 'column_detail',
        'link_type_title' => '专栏详情',
        'app' => 'classroom',
        'controller' => 'column',
        'action' => 'lists',
        'api' => url('classroom/admin.column/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-indent',
        'sys_type' => 'list',
        'link_type' => 'offline_list',
        'link_type_title' => '线下课列表',
        'api' => url('classroom/admin.offline/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-map-marker',
        'sys_type' => 'detail',
        'link_type' => 'offline_detail',
        'link_type_title' => '线下课详情',
        'api' => url('classroom/admin.column/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-indent',
        'sys_type' => 'list',
        'link_type' => 'material_list',
        'link_type_title' => '资料列表',
        'api' => url('classroom/admin.material/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-download',
        'sys_type' => 'detail',
        'link_type' => 'material_detail',
        'link_type_title' => '资料详情',
        'api' => url('classroom/admin.material/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-indent',
        'sys_type' => 'list',
        'link_type' => 'exam_paper_list',
        'link_type_title' => '试卷列表',
        'api' => url('exam/admin.paper/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-newspaper-o',
        'sys_type' => 'detail',
        'link_type' => 'exam_paper_detail',
        'link_type_title' => '试卷详情',
        'api' => url('exam/admin.paper/lists'),
        'category_api' => url('classroom/admin.category/tree'),
    ],[
        'icon' => 'fa-indent',
        'sys_type' => 'direct',
        'link_type' => 'category',
        'link_type_title' => '分类页',
        'api' => url('exam/admin.category/lists')
    ],[
        'icon' => 'fa-user',
        'sys_type' => 'direct',
        'link_type' => 'member',
        'link_type_title' => '会员服务',
        'api' => url('exam/admin.vip/lists')
    ]
];