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
        if(!empty($data)){
            if (is_object($data)) $data = $data->toArray();

            if(!empty($data['status'])){
                $data['status_str'] = $this->_status[$data['status']];
            }else{
                //$data['article_config']['status'] = 0;
                $data['status_str'] = $this->_status[0];
            }
            if(!empty($data['comment'])){
                $data['comment'] = json_decode($data['comment'],true);
            }
            
            if(!empty($data['comment']['status'])){
                $data['comment']['status_str'] = $this->_status[$data['comment']['status']];
            }else{
                $data['comment']['status'] = 0;
                $data['comment']['status_str'] = $this->_status[0];
            }

            if(!empty($data['comment']['audit'])){
                $data['comment']['audit_str'] = $this->_status[$data['comment']['audit']];
            }else{
                $data['comment']['audit'] = 0;
                $data['comment']['audit_str'] = $this->_status[0];
            }

            //站点关闭描述
            if(empty($data['close_desc'])){
                $data['close_desc'] = '系统关闭~请稍后访问！';
            }
    
            return $data;
        }else{
            $data = (new ConfigModel())->defaultData();

            return $data;
        }
	}

}