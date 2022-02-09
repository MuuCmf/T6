<?php
namespace app\micro\logic;

use app\common\logic\Base;
use think\helper\Str;
use app\common\model\module;

class Page 
{
    /**
     * 自定义页类型
     *
     * @var        array
     */
    public $_type = [
        0 => '自定义页',
        1 => '主页'
    ];

    public $_status = [
        1  => '启用',
        0  => '禁用',
        -1 => '已删除'
    ];

    /**
     * 连接至参数配置
     */
    public function links()
    {
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
    }

    /**
     * 链接至参数处理
     */
    public function linkParams(){

        $links = $this->links();

        // if($_GPC['action'] == 'pc_diy' || $_GPC['action'] == 'pc_head' || $_GPC['action'] == 'pc_foot'){
        //     // pc端
        //     $port = 'webapp';
        //     unset($link[5]); //删除分类页
        //     unset($link[6]); //删除会员页
        // }

        // foreach($link as $k=>&$v){
            
        // }
        // unset($v);
        return $links;
    }

    public function linkToUrl($linkParam = [], $channel = 'mobile'){
        //初始化返回值
        $result = '';

        return $result;
    }

    /**
     * 获取系统图标列表
     *
     * @return     array  The icon lists.
     */
    public function getIconLists()
    {
        //获取系统图标
        //取得系统图标所在目录
        $dir  =  PUBLIC_PATH . '/static/common/images/icon';
        //初始化空数组
        $file_arr = array();
        //判断目标目录是否是文件夹
        if(is_dir($dir)){
            //打开
            if($dh = @opendir($dir)){
                //读取
                while(($file = readdir($dh)) !== false){

                    if($file != '.' && $file != '..'){

                        $file_arr[] = $file;
                    }
                }
                //关闭
                closedir($dh);
            }
        }

        $icon_arr = array();
        foreach($file_arr as $val){
            $icon_dir = $dir .'/'.$val;

            if($dh = @opendir($icon_dir)){
                //读取
                while(($file = readdir($dh)) !== false){

                    if($file != '.' && $file != '..'){

                        $icon_arr_item = array(
                            'title' => $file,
                            'url' => request()->domain() . '/static/common/images/icon/' . $val .'/'. $file,
                        );
                        $icon_arr[$val][] = $icon_arr_item;
                    }
                }
                //关闭
                closedir($dh);
            }

        }

        return $icon_arr;
    }

    /**
     * 数据二次处理
     * @return [type] [description]
     */
    public function handlingNoParamJson($data){
        //页面装修数据的二次处理
        if(!empty($data) && !empty($data['data'])){

            foreach($data['data'] as &$v){
                if(isset($v['data']) && $v['type'] == 'slideshow' && is_array($v['data']) ){
                    foreach($v['data'] as &$b){
                        if(!empty($b['link']['param'])){
                            $b['link']['param'] = json_encode($b['link']['param']);
                        }
                    }
                    unset($b);
                }

                //图文导航数据处理
                if($v['type'] == 'category_nav' && is_array($v['data'])){
                    foreach($v['data'] as &$c){
                        //外部链接类型增加url参数
                        if($c['link']['sys_type'] == 'out_url'){
                            $c['link']['url'] = $c['link']['param']['url'];
                        }
                        if(!empty($c['link']['param'])){
                            $c['link']['param'] = json_encode($c['link']['param']);
                        }
                    }
                    unset($c);
                }
                //单图链接至数据处理
                if($v['type'] == 'single_img' && is_array($v['data'])){
                    foreach($v['data'] as &$s){
                        if(!empty($s['link']['param'])){
                            $s['link']['param'] = json_encode($s['link']['param']);
                        }
                    }
                    unset($s);
                }
            }
            unset($v);
        }

        return $data;
    }

    /**
     * 格式化数据
     */
    public function formatData($data)
    {
        $shopid = $data['shopid'];
        //data 反编译为数组
        $data['data'] = json_decode($data['data'],true);
        if(!empty($data['data'])){
            foreach($data['data'] as &$val){
                switch ($val['type']){
                    // 单图组件处理
                    case 'single_img':
                        $val = $this->singleImg($val,$shopid);
                    break;
                    // 轮播图组件处理
                    case 'slideshow':
                        $val = $this->slideshow($val,$shopid);
                    break;
                    // 图文导航组件处理
                    case 'category_nav':
                        $val = $this->categoryNav($val,$shopid);
                    break;
                    // 公告组件处理
                    case 'announce':
                        $val = $this->announce($val,$shopid);
                    break;
                    // 课程列表
                    case 'knowledge_list':
                        $val = $this->knowledgeList($val,$shopid);
                    break;
                    // 专栏列表
                    case 'column_list':
                        $val = $this->columnList($val,$shopid);
                    break;
                    // 资料下载列表
                    case 'material_list':
                        $val = $this->materialList($val,$shopid);
                    break;
                    // 线下课列表
                    case 'offline_list':
                        $val = $this->offlineList($val,$shopid);
                    break;
                    // 直播课列表
                    case 'live_list':
                        $val = $this->liveList($val,$shopid);
                    break;
                    // 老师列表
                    case 'teacher_list':
                        $val = $this->teacherList($val,$shopid);
                    break;
                    // 云小店商品列表
                    case 'minishop_goods_list':
                        $val = $this->minishopGoodsList($val,$shopid);
                    break;
                    // 积分商城商品数据
                    case 'scoreshop_goods_list':
                        $val = $this->scoreshopGoodsList($val,$shopid);
                    break;
                    //自定义HTML
                    // case 'custom_html':
                    //     $val['data']['content'] = htmlspecialchars_decode($val['data']['content']);
                    // break;
                    //文章列表数据处理
                    case 'article_list':
                        $val = $this->articleList($val,$shopid);
                    break;
                    //分类&筛选数据处理
                    case 'category':
                        $val = $this->category($val,$shopid);
                    break;
                    //关注微信公众号数据处理
                    case 'weixin':
                        $val = $this->weixin($val,$shopid);
                    break;
                }
            }
            $data['url'] = '';
        }

        if($data['port_type'] == 'pc'){
            //通用头部配置数据
            if(!empty($data['header'])){
                $data['header'] = json_decode($data['header'],true);
                if(!empty($data['header']['logo'])){
                    $data['header']['logo'] = get_attachment_src($data['header']['logo']);
                }else{
                    $data['header']['logo'] = '';
                }
            }else{
                $data['header'] = json_decode($data['header'],true);
                $data['header']['style'] = 1;
                $data['header']['logo'] = '';
            }
        }
        
        $data = $this->setStatusAttr($data);
        $data = $this->setTimeAttr($data);

        return $data;
    }

    /**
     * 单图广告
     */
    public function singleImg($data)
    {
        foreach($data['data'] as &$v){
            $v['img_url'] = get_attachment_src($v['img_url']);
            if(!empty($v['link'])){
                $v['link']['url'] = $this->linkToUrl($v['link']);
            }
        }

        return $data;
    }

    /**
     * 轮播图
     */
    public function slideshow($data)
    {
        if(!empty($data['data'])){//数据不为空时执行
            foreach($data['data'] as &$v){
                $v['img_url'] = get_attachment_src($v['img_url']);
                if(!empty($v['link'])){
                    $v['link']['url'] = $this->linkToUrl($v['link']);
                }
            }
        }
        return $data;
    }

    /**
     * 图文导航
     */
    public function categoryNav($data)
    {
        foreach($data['data'] as &$v){

            if(empty($v['icon_url'])){
                $v['icon_url'] = request()->domain() . '/static/classroom/images/diy/noimg.png';
            }else{
                $v['icon_url'] = get_attachment_src($v['icon_url']);
            }
            if(!empty($v['link'])){
                $v['link']['url'] = $this->linkToUrl($v['link']);
            }

        }
        return $data;
    }

    /**
     * 公告
     */
    public function announce($data,$shopid)
    {
        $rows = $data['data']['rows'] = isset($data['data']['rows'])? $data['data']['rows'] : 2;
        $map = [
            ['shopid','=',$shopid],
            ['status','=',1]
        ];
        $list = (new \app\common\model\Announce())->getList($map,$rows,'sort DESC,id DESC');
        if(!empty($list)){
            $list->toArray();
            foreach($list as &$v){
                $v =  (new \app\common\model\Announce())->formatData($v);
            }
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 点播课列表
     */
    public function knowledgeList($data, $shopid)
    {    
        if(!isset($data['data']['rank'])){
            $data['data']['rank'] = 1;
        }
        
        $type = !empty($data['data']['type']) ? strtolower($data['data']['type']) : 'all';
        $category_id = intval($data['data']['category_id']);
        $attribute_ids = empty($data['data']['attribute_ids'])? '' : $data['data']['attribute_ids'];

        $map = (new \app\classroom\logic\Knowledge())->getMap($shopid,'',$category_id,$attribute_ids,$type,'',1,1);
        $rows = $data['data']['rows'];
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $list = (new \app\classroom\model\ClassroomKnowledge())->getList($map, $rows, $order);

        if(!empty($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\classroom\logic\Knowledge())->formatData($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 专栏列表
     */
    public function columnList($data,$shopid)
    {
        if(!isset($data['data']['rank'])){
            $data['data']['rank'] = 1;
        }

        $category_id = intval($data['data']['category_id']);
        $attribute_ids = empty($data['data']['attribute_ids'])? '' : $data['data']['attribute_ids'];
        // 获取查询条件
        $map = (new \app\classroom\logic\Column())->getMap($shopid,'',$category_id,$attribute_ids,'',1);
        
        $rows = $data['data']['rows'];
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $list = (new \app\classroom\model\ClassroomColumn())->getList($map, $rows, $order);
        if(!empty($list) && is_object($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\classroom\logic\Column())->formatData($v, false);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 老师列表
     */
    public function teacherList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $map[] = ['status', '=', 1];
        if(!empty($shopid)){
            $map[] = ['shopid', '=', $shopid];
        }
        if(empty($data['data']['order_field'])){
            $data['data']['order_type'] = 'sort';
        }
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $rows = $data['data']['rows'];

        $list = (new \app\classroom\model\ClassroomTeacher())->getList($map, $rows, $order);
        if(!empty($list) && is_object($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\classroom\logic\Teacher())->formatData($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 线下活动（独立应用）列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function activityList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $where = ' WHERE `uniacid` = '. $uniacid . ' AND `status` = 1';
        //分类id
        if(!empty(intval($data['data']['category_id']))){
            $category_ids = load_class('logic/ActivityCategoryLogic','muu_activity')->yesParent(intval($data['data']['category_id'])); 
            if(!empty($category_ids)){
                $category_ids = implode(',',$category_ids);
                $where .= ' AND `category_id` IN (' . $category_ids .')';
            }else{
                $where .= ' AND `category_id` = ' . intval($data['data']['category_id']);
            }
        }
        if(empty($data['data']['order_field'])){
            $data['data']['order_type'] = 'sort';
        }
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];

        $rows = $data['data']['rows'];
        $list = (new \app\activity\model\ClassroomTeacher())->getList($where, $rows, 'ORDER BY '.$order);
        if(!empty($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\activity\model\ClassroomTeacher())->handling($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 直播课列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function liveList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $category_id = intval($data['data']['category_id']);
        $attribute_ids = empty($data['data']['attribute_ids'])? '' : $data['data']['attribute_ids'];

        // 获取查询条件
        $map = (new \app\livecourse\logic\Room())->getMap($shopid,'',$category_id,$attribute_ids,'',1);

        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $rows = $data['data']['rows'];
        $list = (new \app\livecourse\model\ClassroomLivecourseRoom())->getList($map, $rows, $order);
        if(!empty($list) && is_object($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\livecourse\logic\Room())->formatData($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 小程序直播组件列表
     */
    public function miniprogramLiveList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        
        $rows = $data['data']['rows'];
        //获取access_token
	    $access_token = load_plugin_class('logic/MiniprogramLiveLogic','muu_classroom_plugin_livecourse')->getAccessToken();
        list($total, $lists, $pager) = load_plugin_class('logic/MiniprogramLiveLogic','muu_classroom_plugin_livecourse')->roomPageList(1, $rows, $access_token['access_token']);
        
        $data['data']['list'] = $lists;
        
        return $data;
    }

    /**
     * 线下课列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function offlineList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $category_id = intval($data['data']['category_id']);
        $attribute_ids = empty($data['data']['attribute_ids'])? '' : $data['data']['attribute_ids'];

        // 获取查询条件
        $map = (new \app\classroom\logic\Offline())->getMap($shopid,'',$category_id,$attribute_ids,'',1);
        
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $rows = $data['data']['rows'];
        $list = (new \app\classroom\model\ClassroomOffline())->getList($map, $rows, $order);
        if(!empty($list) && is_object($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\classroom\logic\Offline())->formatData($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 资料下载列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function materialList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $category_id = intval($data['data']['category_id']);
        $attribute_ids = empty($data['data']['attribute_ids'])? '' : $data['data']['attribute_ids'];

        // 获取查询条件
        $map = (new \app\classroom\logic\Material())->getMap($shopid,'',$category_id,$attribute_ids,'',1);
        
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $rows = $data['data']['rows'];
        $list = (new \app\classroom\model\ClassroomMaterial())->getList($map, $rows, $order);
        if(!empty($list) && is_object($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\classroom\logic\Material())->formatData($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 试卷列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function examPaperList($data,$uniacid,$rewrite_switch)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $where = ' WHERE `uniacid` = '. $uniacid . ' AND `status` = 1';
        //分类id
        if(!empty(intval($data['data']['category_id']))){
            $category_ids = load_main_class('logic/CategoryLogic')->yesParent(intval($data['data']['category_id'])); 
            if(!empty($category_ids)){
                $category_ids = implode(',',$category_ids);
                $where .= ' AND `category_id` IN (' . $category_ids .')';
            }else{
                $where .= ' AND `category_id` = ' . intval($data['data']['category_id']);
            }
        }
        if(empty($data['data']['order_field'])){
            $data['data']['order_type'] = 'sort';
        }
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];

        $rows = $data['data']['rows'];

        $list = load_plugin_class('model/ExamPaperModel','muu_classroom_plugin_exam')->getList($where, $rows, 'ORDER BY '.$order);
        foreach($list as &$v){
            $v = load_plugin_class('logic/ExamPaperLogic','muu_classroom_plugin_exam')->handling($v);
        }
        unset($v);

        $data['data']['list'] = $list;
        
        return $data;
    }

    /**
     * 云小店商品列表
     */
    public function minishopGoodsList($data,$shopid)
    {
        $map = [
            ['shopid','=',$shopid],
            ['status','=',1],
        ];
        if(!isset($data['data']['category_id'])){
            $data['data']['category_id'] = 0;
        }
        if(!empty(intval($data['data']['category_id']))){
            $category_id = $data['data']['category_id'];
            $category_ids = (new \app\minishop\model\MinishopCategory())->yesParent($category_id);
            if(!empty($category_ids)){
                $category_ids = implode(',',$category_ids);
                $map[] = ['category_id','in',$category_ids];
            }else{
                $map[] = ['category_id','=',$data['data']['category_id']];
            }
        }
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $rows = $data['data']['rows'];
        $list = (new \app\minishop\model\MinishopGoods())->getList($map, $rows ,$order);
        $minishopLogic = new \app\minishop\logic\Goods();
        foreach($list as &$v){
            $v = $minishopLogic->_formatData($v);
        }
        $data['data']['list'] = $list;
        if(!isset($data['data']['rank'])){
            $data['data']['rank'] = 1;
        }

        return $data;
    }

    /**
     * 文章列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function articleList($data, $shopid = 0)
    {
        if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
        $category_id = intval($data['data']['category_id']);

        // 获取查询条件
        $map = (new \app\articles\logic\Articles())->getMap($shopid, '', $category_id, 1);
        
        $order = $data['data']['order_field'].' '.$data['data']['order_type'];
        $rows = $data['data']['rows'];
        $list = (new \app\articles\model\ArticlesArticles())->getList($map, $rows, $order);
        if(!empty($list) && is_object($list)){
            $list = $list->toArray();
            foreach($list as &$v){
                $v = (new \app\articles\logic\Articles())->formatData($v);
            }
            unset($v);
            $data['data']['list'] = $list;
        }
        
        return $data;
    }

    /**
     * 分类&筛选组件数据处理
     */
    public function category($data,$shopid = 0)
    {
        // 默认给文章模块分类数据
        $app = !empty($data['data']['app'])?$data['data']['app']:'articles';
        // 判断APP是否安装并启用
        $installed = (new module())->checkInstalled($app);
        // 应用已安装
        if($installed){

        }

        return $data;
    }

    /**
     * 关注微信公众号
     */
    public function weixin($data,$shopid = 0)
    {
        if(empty($data['style'])) $data['style'] = 0; //样式默认为0
        
        return $data;
    }

    /**
     * 积分商品列表
     * @param  [type] $data    [description]
     * @param  [type] $uniacid [description]
     * @return [type]          [description]
     */
    public function scoreshopGoodsList($data,$shopid = 0)
    {
//        if($data['type'] == 'scoreshop_goods_list'){
//            if(!isset($data['data']['rank'])) $data['data']['rank'] = 1;
//            $where = ' WHERE `uniacid` = '. $uniacid . ' AND `status` = 1';
//            if(empty($data['data']['order_field'])){
//                $data['data']['order_type'] = 'sort';
//            }
//            $order = $data['data']['order_field'].' '.$data['data']['order_type'];
//
//            $rows = $data['data']['rows'];
//            $list = load_class('plugin/ScoreshopGoodsPlugin')->getList($where, $rows, 'ORDER BY '.$order);
//            foreach($list as &$v){
//                $v = load_class('plugin/ScoreshopGoodsPlugin')->handling($v);
//            }
//            unset($v);
//            $data['data']['list'] = $list;
//        }
        $data['data']['list'] = [];
        return $data;
    }

    private function setStatusAttr($data,$attrArray = [])
    {
        if(empty($attrArray)){
            $attrArray = $this->_status;
        }
        $data['status_str'] = $attrArray[$data['status']];

        return $data;
    }
    private function setTimeAttr($data)
    {
        if(!empty($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
            $data['create_time_friendly_str'] = friendly_date($data['create_time']);
        }
        if(!empty($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
            $data['update_time_friendly_str'] = friendly_date($data['update_time']);
        }
        if(!empty($data['start_time'])){
            $data['start_time_str'] = time_format($data['start_time']);
        }
        if(!empty($data['end_time'])){
            $data['end_time_str'] = time_format($data['end_time']);
        }
        if(!empty($data['use_time'])){
            $data['use_time_str'] = time_format($data['use_time']);
        }
        if(!empty($data['paid_time'])){
            $data['paid_time_str'] = time_format($data['paid_time']);
        }
        if(!empty($data['logistic_time'])){
            $data['logistic_time_str'] = time_format($data['logistic_time']);
        }
        if(!empty($data['reply_time'])){
            $data['reply_time_str'] = time_format($data['reply_time']);
        }


        return $data;
    }
}

