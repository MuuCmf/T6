<?php
namespace app\micro\diy;

class Announce
{
    // 名称
    public $_title   = '公告';
    // 类型（唯一标识）
    public $_type    = 'announce';
    // 图标
    public $_icon    = 'bullhorn';
    // 模板文件
    public $_template     = [
        'script' =>  APP_PATH . 'micro/view/diy/announce/script.html',
        'view' =>  APP_PATH . 'micro/view/diy/announce/view.html',
    ];
    // 静态资源
    public $_static = [];
    // API接口
    public $_api = [];

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->_static = $this->setStatic();
    }

    public function setStatic()
    {
        return [
            'mobile' => [
                'css' => request()->domain() . '/static/micro/diy/mobile/announce.min.css',
                'js' => request()->domain() . '/static/micro/diy/mobile/announce.min.js',
            ],
            'pc' => [
                'css' => '',
                'js' => ''
            ]
        ];
    }

    /**
     * 约定数据处理方法
     */
    public function handle($data, $shopid = 0)
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
}