<?php
namespace app\admin\model;

use think\Model;

/**
 * 菜单模型
 */

class Menu extends Model {

    public $hide = [
        0 => '否',
        1 => '是'
    ];

    public $type = [
        0 => '系统',
        1 => '应用'
    ];

	//获取树的根到子节点的路径
	public function getPath($id){
		$path = [];
		$nav = $this->where('id',$id)->field('id,pid,title')->find();

		$path[] = $nav;
		if($nav['pid'] !='0'){
			$path = array_merge($this->getPath($nav['pid']),$path);
		}

		return $path;
	}

    /**
     * 写入、编辑方法
     * @param  Array 写入数据的数组
     * @return 写入数据库中的主键ID
     */
	public function edit($data)
    {
        if($data['id']){
            $res=$this->update($data);
        }else{
            $data['id'] = create_guid();
            $res=$this->save($data);
        }

        return $res;
    }

    public function getDataByMap($map=[],$fields= '*'){
        
        $data = $this->where($map)->field($fields)->find();
        
        return $data;
    }

    /**
     * 获取菜单列表
     *
     * @return     <type>  The lists.
     */
    public function getLists($where)
    {
        $menus = $this->where($where)->order('sort asc')->select();

        return $menus;
    }

    /**
     * 获取一级菜单
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function mainMenu()
    {
        $pid = '0';
        $res =  $this->where(array('pid'=>(string)$pid))->select();

        return $res;
    }
    
    /**
     * 判断、读取下级菜单
     * @param  [type] $pid [description]
     * @return [type]      [description]
     */
    public function subMenu($pid){
        $res =  $this->where(['pid'=>$pid])->select();

        return $res;
    }

    /**
     * 数据格式化
     */
    public function handle($data)
    {
        $data['hide_str'] = $this->hide[$data['hide']];
        $data['type_str'] = $this->type[$data['type']];

        return $data;
    }
}

