<?php
namespace app\knowledge\logic;

use think\Model;
use think\Db;
use app\knowledge\model\Knowledge;
use app\knowledge\model\KnowledgeColumn;
/*
 * 搜索逻辑层
 */
class Search extends Model{

	/**
     * 知识内容的类型
     *
     * @var        array
     */
    public $_type = [
        'all'        => '所有',
    	'image_text' => '图文',
    	'video'      => '视频',
    	'audio'      => '音频',
    	'down'       => '资料',
    	'column'     => '专栏'
    ];
    /**
     * 知识内容状态
     */
    public $_status = [
        '1'  => '已上架',
        '0'  => '已下架',
        '-1' => '已删除'
    ];
	/**
	 * Searches for the first match.
	 *
	 * @param      <type>   $map    The map
	 * @param      string   $field  The field
	 * @param      integer  $rows   The rows
	 *
	 * @return     <type>   ( description_of_the_return_value )
	 */
	public function search($map,$order='create_time desc',$field='*',$rows=20){

		$knowledge = model('knowledge/KnowledgeColumn')->field($field)->where($map)->order($order)->buildSql();
        $mres = model('knowledge/Knowledge')->field($field)->where($map)->order($order)->union($knowledge)->buildSql();

        $result = Db::table($mres.' a')
			->field($field)
			->paginate($rows,false,['query'=>request()->param()]);
		
		$data = $result->all();
		foreach($data as $k => &$v){
			$v = $this->_formatList($v);
			$result->offsetSet($k,$v);//分页初始对象设置新数据
		}
		unset($v);

		return $result;
	}

	/**
	 * { function_description }
	 *
	 * @param      <type>  $data   The data
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function _formatList($data)
    {	
        if(isset($data['price'])) {
            $data['price'] = sprintf("%.2f",$data['price']/100);
        }
        if(isset($data['marking_price'])){
            $data['marking_price'] = sprintf("%.2f",$data['marking_price']/100);
        }

        if(isset($data['type'])){
            $data['type_str'] = $this->_type[$data['type']];
        }

        if(isset($data['category_id']) && $data['category_id'] != 0){
            $data['category_title'] = model('knowledge/KnowledgeCategory')->getDataById($data['category_id'])['title'];
        }else{
            $data['category_title'] = '未分类';
        }
        
        if(isset($data['status'])){
            $data['status_str'] = $this->_status[$data['status']];
        }

        if(isset($data['cover'])){
            $data['cover_src_80'] = getThumbImageById($data['cover'], 80, 60);
            $data['cover_src_120'] = getThumbImageById($data['cover'], 120, 90);
            $data['cover_src_200'] = getThumbImageById($data['cover'], 200, 150);
            $data['cover_src_400'] = getThumbImageById($data['cover'], 400, 300);
        }

        if(isset($data['create_time'])){
            $data['create_time_str'] = time_format($data['create_time']);
        }

        if(isset($data['update_time'])){
            $data['update_time_str'] = time_format($data['update_time']);
        }

        return $data;
    }
}