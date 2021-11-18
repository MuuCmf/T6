<?php
namespace app\articles\logic;

use app\articles\model\ArticlesConfig as ConfigModel;
/*
 * Config 配置数据逻辑层
 */
class Config {

    public $_status = [

        0  => '禁用',
        1  => '启用',
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
            if(!empty($data['status'])){
                $data['status_str'] = $this->_status[$data['status']];
            }else{
                //$data['article_config']['status'] = 0;
                $data['status_str'] = $this->_status[0];
            }
            if(!is_array($data['config'])){
                $data['config'] = json_decode($data['config'],true);
            }
            
            if(!empty($data['config']['comment']['switch'])){
                $data['config']['comment']['switch_str'] = $this->_status[$data['config']['comment']['switch']];
            }else{
                //$data['article_config']['comment']['switch'] = 0;
                $data['config']['comment']['switch_str'] = $this->_status[0];
            }
    
            return $data;
        }else{
            return $data;
        }
	}

}