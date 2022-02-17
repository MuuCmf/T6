<?php
namespace app\micro\logic;

use app\micro\service\Diy;
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
                $val = (new Diy())->handle($val,$shopid);
            }
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

        $data['url'] = '';
        
        $data = $this->setStatusAttr($data);
        $data = $this->setTimeAttr($data);

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

