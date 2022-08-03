<?php
namespace app\common\logic;

/*
 * 作者数据逻辑层
 */
class Author extends Base
{

    public $_status = [
		0 => '已禁用',
		1 => '已启用',
		-1 => '未审核',
	    -2 => '审核未通过',
        -3 => '已删除',
	];

    /**
     * 条件查询
     * @param  string $keyword       [description]
     * @param  string $status        状态：all:所有 （不包括已删除）1：已上架 0：已下架 -1：未审核 -2：审核未通过 -3：已删除
     * @return array                [description]
     */
    public function getMap($shopid, $keyword = '', $status = 'all')
    {
        //初始化查询条件
        $map = [];
        
        if(!empty($shopid)){
            $map[] = ['shopid', '=', $shopid];
        }
        
        if($status == 'all'){
            $map[] = ['status', '>=', -2];
        }elseif($status == 0){
            $map[] = ['status', '>=', $status];
        }else{
            $map[] = ['status', '=', $status];
        }

        if(!empty($keyword)){
            $map[] = ['title', 'like', '%'. $keyword .'%'];
        }
        
        return $map;
    }

    /**
     * 格式化数据
     */
    public function formatData($data)
    {
        $data = $this->setCoverAttr($data, '1:1');
        $data['content'] = htmlspecialchars_decode($data['content']);
        
        // 绑定用户的讲师获取用户数据
		if($data['uid'] > 0){
			$data['user_info'] = query_user($data['uid']);
		}

		// // 累计总收入
        // if(isset($data['total'])){
        //     $data['total'] = sprintf("%.2f",$data['total']/100);
        // }
		
		// // 余额
        // if(isset($data['charges'])){
        //     $data['charges'] = sprintf("%.2f",$data['charges']/100);
        // }
		
		// 冻结资金
		//$data['freeze'] = sprintf("%.2f",$data['freeze']/100);
		// 可以余额
        //$data['enable_charges'] = sprintf("%.2f",($data['charges'] - $data['freeze']));
        
		// 状态描述
		$data['status_str'] = $this->_status[$data['status']];
        // 时间处理
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
        
        return $data;
    }

}