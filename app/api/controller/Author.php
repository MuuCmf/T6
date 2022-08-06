<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Author as AuthorModel;
use app\common\logic\Author as AuthorLogic;


class Author extends Api
{   
    protected $AuthorModel;
    protected $AuthorLogic;
    
    public function __construct()
    {
        parent::__construct();
        $this->_initialize();
    }

    public function _initialize()
    {
        $this->AuthorModel   = new AuthorModel();  //模型
        $this->AuthorLogic   = new AuthorLogic();  //逻辑
    }

    /**
     * 作者列表
     * @return     <type>  ( description_of_the_return_value )
     */
    public function lists()
    {
        $rows = input('rows',20, 'intval');
        $keyword = input('keyword','','text');
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = $order_field . ' ' . $order_type;

        // 查询条件
        $map = $this->AuthorLogic->getMap($this->shopid, $keyword, 1);
        $fields = '*';
        $lists = $this->AuthorModel->getListByPage($map,$order,$fields, $rows);
        $lists = $lists->toArray();
        foreach($lists['data'] as &$val){
            $val = $this->AuthorLogic->formatData($val);
        }
        unset($val);
        // ajax请求返回数据
        return $this->success('success', $lists);
    }

    /**
     * 详情
     *
     * @param      integer  $id     The identifier
     * @return     <type>   ( description_of_the_return_value )
     */
    public function detail()
    {
        $id = input('id',0,'intval');

        if(!empty($id)){
            $data = $this->AuthorModel->getDataById($id);
            $data = $this->AuthorLogic->formatData($data);

            //查询条件
            $map = [
                ['shopid', '=', $this->shopid],
                ['author_id', '=', $id],
                ['status', '=', 1]
            ];
            
            if(!empty($data)){
                // ajax请求返回数据
                return $this->success('success', $data);
            }else{
                return $this->error('error');
            }
        }else{
            return $this->error('缺少参数');
        }
    }

    /**
     * 关注
     */
    public function follow()
    {
        return $this->error('缺少参数');
    }

    /**
     * 是否已关注
     */
    public function isfollow()
    {
        return $this->error('缺少参数');
    }

}