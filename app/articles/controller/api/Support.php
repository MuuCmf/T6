<?php
namespace app\articles\controller\api;

use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;
use app\common\model\Support as SupportModel;

class Support extends Base
{
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    protected $ArticlesModel;
    protected $ArticlesLogic;
    protected $SupportModel;

    public function __construct()
    {
        parent::__construct();
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
        $this->SupportModel = new SupportModel();
    }

    /**
     * 点赞或取消
     */
    public function action()
    {
        if (request()->isPost()){
            $uid = get_uid();
            $info_id = input('info_id', 0, 'intval');
            $info_type = input('info_type', '', 'text');
            if (empty($info_id)){
                return $this->error('数据异常');
            }
            //是否有收藏记录
            $map = [
                ['info_id','=',$info_id],
                ['info_type','=',$info_type],
                ['shopid','=',$this->shopid],
                ['app','=',get_module_name()],
                ['uid','=',$uid]
            ];
            $data = $this->SupportModel->getDataByMap($map);
            $data = $data ?? [];
            if (empty($data)){

                $f_data = [
                    'shopid' => $this->shopid,
                    'app'    => get_module_name(),
                    'uid'    => $uid,
                    'info_id'   => $info_id,
                    'info_type' => $info_type,
                    'status' => 1,
                ];
            }else{
                $f_data = [
                    'status' => $data['status'] == 1 ? 0 : 1,
                    'id'     => $data['id']
                ];
            }
            $msg_tip = $f_data['status'] == 1 ? '点赞' : '取消';
            $inc_value = $f_data['status'] == 1 ? 1 : -1;

            $result = $this->SupportModel->edit($f_data);
            if ($result){
                $this->ArticlesModel->setStep($info_id,'support',$inc_value);

                return $this->success($msg_tip . '成功', $f_data['status']);
            }else{
                return $this->error($msg_tip . '失败');
            }
        }

        return $this->error('请求错误');
    }

    /**
     * 查询是否点赞文章
     */
    public function check()
    {
        $uid = get_uid();
        $info_id = input('info_id', 0, 'intval');
        $info_type = input('info_type', '', 'text');
        if (empty($info_id)){
            return $this->error('数据异常');
        }
        //是否有记录
        $map = [
            ['info_id','=',$info_id],
            ['info_type','=',$info_type],
            ['shopid','=',$this->shopid],
            ['app','=',get_module_name()],
            ['uid','=',$uid]
        ];
        $data = $this->SupportModel->getDataByMap($map);
        $data = $data ?? [];

        if(empty($data)){
            return $this->error('未点赞');
        }else{
            if($data['status'] == 1){
                return $this->success('已点赞');
            }else{
                return $this->error('未点赞');
            }
        }
    }



}