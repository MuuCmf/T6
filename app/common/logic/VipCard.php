<?php
namespace app\common\logic;

use app\common\model\Module;

class VipCard extends Base
{
    /**
     * 整理表单数据
     */
    public function queryData($data)
    {
        //初始化支持分类
        $category_ids = '';
        if(!empty($data['category_ids'])){
            $category_ids = implode(',',$data['category_ids']);
        }
        //组装config配置数据
        $title_color = $data['title_color'];
        $description_color = $data['description_color'];
        $card_bg_color = $data['card_bg_color'];
        $card_bg_image = $data['card_bg'];
        $config = [
            'title' => [
                'color' => $title_color,
            ],
            'description' => [
                'color' => $description_color,
            ],
            'bg' => [
                'color' => $card_bg_color,
                'image' => $card_bg_image
            ],
        ];

        //转为json数据
        $config = json_encode($config);
        //价格
        $month_price = empty($data['month_price']) ? 0 : intval($data['month_price'] * 100);
        $quarter_price = empty($data['quarter_price']) ? 0 : intval($data['quarter_price'] * 100);
        $year_price = empty($data['year_price']) ? 0 : intval($data['year_price'] * 100);
        $forever_price = empty($data['forever_price']) ? 0 : intval($data['forever_price'] * 100);
        //权益描述
        $content = '';
        if(!empty($data['content'])) $content = $data['content'];
        //组装数据
        $data = [
            'id' => intval($data['id']),
            'app' => get_module_name(),
            'shopid' => $data['shopid'],
            'title' => $data['title'],
            'description' => $data['description'],
            'cover' => $data['cover'],
            'month_price' => $month_price,
            'quarter_price' => $quarter_price,
            'year_price' => $year_price,
            'forever_price' => $forever_price,
            'category_ids' => $category_ids,
            'content' => $content,
            'discount' => $data['discount'],
            'config' => $config,
            'status' => intval($data['status']),
            'sort' => intval($data['sort']),
        ];
        if(isset($data['discount_free'])){
            $data['discount'] = $data['discount_free'];
        }

        //返回处理后数据
        return $data;
    }
    
    /**
     * @title 数据格式化
     * @param $data
     */
    public function formatData($data)
    {
        if(!empty($data['category_ids'])){
            $category_ids_arr = explode(',',$data['category_ids']);
        }else{
            $category_ids_arr = [];
        }
        $data['category_ids_arr'] = $category_ids_arr;

        if(!empty($data['month_price'])){
            $data['month_price'] = sprintf("%.2f",$data['month_price']/100);
        }
        if(!empty($data['quarter_price'])){
            $data['quarter_price'] = sprintf("%.2f",$data['quarter_price']/100);
        }
        if(!empty($data['year_price'])){
            $data['year_price'] = sprintf("%.2f",$data['year_price']/100);
        }
        if(!empty($data['forever_price'])){
            $data['forever_price'] = sprintf("%.2f",$data['forever_price']/100);
        }
        
        if($data['discount'] == 0){
            $data['discount_str'] = '免费';
        }else{
            $data['discount_str'] = $data['discount'] . '折';
        }

        $data = $this->setStatusAttr($data,$this->_status);
        $data = $this->setTimeAttr($data);
        $data['content'] = htmlspecialchars_decode($data['content']);
        //获取配置数据
        if(!empty($data['config'])){
            $config = json_decode($data['config'],true);
            if(!empty($config['bg']['image'])){
                $config['bg']['image_url'] = get_attachment_src($config['bg']['image']);
            }else{
                $config['bg']['image_url'] = '';
            }
            $data['config'] = $config;
        }else{
            //给默认数据
            $data['config'] = [];
            if(empty($data['config']['title']['color'])){
                $data['config']['title'] = [];
                $data['config']['title']['color'] = '#d9b68b';
            }
            if(empty($data['config']['description']['color'])){
                $data['config']['description'] = [];
                $data['config']['description']['color'] = '#d9b68b';
            }
            if(empty($data['config']['bg']['color'])){
                $data['config']['bg'] = [];
                $data['config']['bg']['color'] = '#333333';
                $data['config']['bg']['image'] = '';
            }
        }

        // 获取应用数据
        $app = (new Module())->getModule($data['app']);
        $data['app_info'] = $app;

        $data = $this->setImgAttr($data,'1:1');

        return $data;
    }

}