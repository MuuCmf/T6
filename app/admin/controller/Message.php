<?php
namespace app\admin\controller;

use app\admin\builder\AdminListBuilder;
use app\admin\builder\AdminConfigBuilder;
use think\facade\View;
use app\common\model\MessageType as MessageTypeModel;
use app\common\model\Message as MessageModel;
/**
 * 消息控制器
 */
class Message extends Admin
{
    protected $MessageModel;
    protected $MessageTypeModel;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {
        parent::__construct();
        $this->MessageModel = new MessageModel();
        $this->MessageTypeModel = new MessageTypeModel();
    }

    /**
     * 消息类型
     */
    public function type()
    {
        $map = [];
        $list = $this->MessageTypeModel->getList($map);

        $builder = new AdminListBuilder();

        $builder->title('消息类型');
        $builder->button('新增类型',['url'=>url('edit'),'class'=>'btn btn-info']);
        $builder->data($list);
        $builder->keyId();
        $builder->keyText('title','类型');
        $builder->keyText('description','描述');
        $builder->keyCreateTime();

        $builder->display();
        
    }

    /**
     * 列表
     */
    public function list()
    {
        
    }


}