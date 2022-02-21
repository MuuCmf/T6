<?php
namespace app\micro\logic;

use app\micro\service\Diy;
/*
 * Config 配置数据逻辑层
 */
class Config {

    public $_status = [

        '1'  => '正在营业中',
        '0'  => '店铺已打烊',
    ];

    public $_show_view = [

        '1'  => '显示',
        '0'  => '隐藏',
    ];

    public $_show_sale = [

        '1'  => '显示',
        '0'  => '隐藏',
    ];

    public $_show_marking_price = [

        '1'  => '显示',
        '0'  => '隐藏',
    ];

    public $_show_favorites = [

        '1'  => '显示',
        '0'  => '隐藏',
    ];

    /**
     * 店铺风格
     *
     * @var        array
     */
    public $_style = [

        'Green' => ['title'=>'Green','color'=>'#00ce74'],
        'Blue' => ['title'=>'Blue','color'=>'#03b8cf'],
        'LightRed' => ['title'=>'LightRed','color'=>'#ea644a'],
        'Orange' => ['title'=>'Orange','color'=>'#ff9900'],
        'LightPink' => ['title'=>'LightPink','color'=>'#FFB6C1'],
        'Magenta' => ['title'=>'Magenta','color'=>'#8666b8'],
    ];

	/**
	 * 格式化数据
	 *
	 * @param      <type>  $data   The data
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function formatData($data)
	{
        if($data){
            
            //店铺样式(单用户版调用系统配置，多用户版调用这里配置)
            if(empty($data['style'])) $data['style'] = 'Blue';

            //店铺底部导航json转数组
            if(empty($data['footer'])){
                $data['footer'] = $this->initFooter([], $data['style']);
            }else{
                $data['footer'] = $this->initFooter($data['footer'], $data['style']);
            }

            //店铺PC导航json转数组
            if(empty($data['navtar'])){
                $data['navtar'] = $this->initNavtar([]);
            }else{
                $data['navtar'] = $this->initNavtar($data['navtar']);
            }
            
            //推荐搜索关键字
            if(!empty($data['search'])){
                $data['search'] = trim($data['search']);
            }else{
                $data['search'] = '';
            }
        }
        
        return $data;
	}

    /**
     * 格式化底部导航
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function initFooter($data = '', $style)
    {
        if(!empty($data)) {
            $footer_arr = json_decode($data, true);
            foreach($footer_arr['data'] as &$v){
                if(empty($v['icon_url']) || $v['icon_url'] == ''){
                    $v['icon_url'] = request()->domain() . '/static/micro/images/diy/noimg.png';
                }
            }
            unset($v);
            $data = $footer_arr;
        }else{
            //数据为空时给一组默认数据，单页面逻辑相同
            $data = [
                'title' => '底部导航',
                'colume' => 2,
                'data' => [[
                    'nav_title' => '首页',
                    'icon_url' => request()->domain() . '/static/micro/images/icon/'. $style .'/gongsi.png',
                    'link' => [
                        "type" => "index",
                        "title" => "首页"
                    ]
                ],[
                    'nav_title' => '我的',
                    'icon_url' => request()->domain() . '/static/micro/images/icon/'. $style .'/wo.png',
                    'link' => [
                        "type" => "user",
                        "title" => "我的"
                    ]
                ]],
            ];
        }

        return $data;
    }

    /**
     * pc端导航设置
     */
    public function initNavtar($data)
    {
        if(!empty($data)) {
            $navtar_arr = json_decode($data,true);

            if(!empty($navtar_arr['head_nav']) && isset($navtar_arr['head_nav'])){
                foreach($navtar_arr['head_nav'] as &$v){
                    if(!empty($v['link'])){
                        $v['link']['url'] = (new Diy())->linkToUrl($v['link'], 'pc');
                    }
                    if($v['link']['sys_type'] == 'out_url'){
                        if(isset($v['link']['param']['url'])){
                            $v['link']['url'] = $v['link']['param']['url'];
                        }
                    }
                }
                unset($v);
            }
            
            $data = $navtar_arr;
        }else{
            $data = [];
        }

        return $data;
    }

}