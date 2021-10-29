<?php
namespace app\articles\logic;

/*
 * Config 配置数据逻辑层
 */
class Config {

    public $_status = [

        0  => '店铺已打烊',
        1  => '正在营业中',
    ];

    public $_show_view = [

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

        'Green' => ['id'=>1,'title'=>'Green','color'=>'#00ce74'],
        'Blue' => ['id'=>2,'title'=>'Blue','color'=>'#03b8cf'],
        'LightRed' => ['id'=>3,'title'=>'LightRed','color'=>'#ea644a'],
        'Orange' => ['id'=>4,'title'=>'Orange','color'=>'#ff9900'],
        'LightPink' => ['id'=>5,'title'=>'LightPink','color'=>'#FFB6C1'],
        'Magenta' => ['id'=>6,'title'=>'Magenta','color'=>'#8666b8'],
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
            if(empty($data['title'])){
                $data['title'] = '店铺名称未设置';
            }
            //处理缩微图
            $data['cover_100'] = get_thumb_image($data['cover'], 100 , 100);
            $data['cover_200'] = get_thumb_image($data['cover'], 200 , 200);
            $data['cover_400'] = get_thumb_image($data['cover'], 400 , 400);
            //店铺样式
            if(empty($data['style'])) $data['style'] = 'Blue';
            // $data['style_arr'] = $this->_style[$data['style']];
            //店铺底部导航json转数组
            if(empty($data['footer'])){
                $data['footer'] = $this->initFooter([], $data['style']);
            }else{
                $data['footer'] = $this->initFooter($data['footer'], $data['style']);
            }
            
            //客服设置
            if(empty($data['service'])){
                $data['service'] = $this->initService([]);
            }else{
                $data['service'] = $this->initService($data['service']);
            }
            
            //消息模板设置
            if(empty($data['tmplmsg'])){
                $data['tmplmsg'] = $this->initTmplmsg([]);
            }else{
                $data['tmplmsg'] = $this->initTmplmsg($data['tmplmsg']);
            }

            //状态
            $data['status_str'] = $this->_status[$data['status']];
            //站点关闭描述
            if(empty($data['close_desc'])){
                $data['close_desc'] = '系统关闭~请稍后访问！';
            }
        }
        
        return $data;
	}

    /**
     * 分享设置
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function initShare($data)
    {
        if(!empty($data)){
            $share = json_decode($data,true);
            if(empty($share['title'])) $share['title'] = '';
            if(empty($share['desc'])) $share['desc'] = '';
        }else{
            $share['title'] = '';
            $share['desc'] = '';
        }

        $data = $share;
        
        return $data;
    }

}