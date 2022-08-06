<?php
namespace app\articles\controller\api;

use app\articles\model\ArticlesArticles as ArticlesModel;
use app\articles\logic\Articles as ArticlesLogic;
use app\articles\model\ArticlesComment as CommentModel;
use app\articles\logic\Comment as CommentLogic;
use app\common\model\Support as SupportModel;

class Comment extends Base 
{
    protected $ArticlesModel;
    protected $ArticlesLogic;
    protected $CommentModel;
    protected $CommentLogic;
    protected $SupportModel;
    function __construct()
    {
        parent::__construct();
        $this->ArticlesModel = new ArticlesModel();
        $this->ArticlesLogic = new ArticlesLogic();
        $this->CommentModel = new CommentModel();
        $this->CommentLogic = new CommentLogic();
        $this->SupportModel = new SupportModel();
    }


    public function lists()
    {
        $keyword = input('keyword', '', 'text');
        $article_id = input('article_id', 0, 'intval');

        // 获取查询条件
        $map = $this->CommentLogic->getMap(0, $keyword, $article_id, 1);
        // 获取列表
        $lists = $this->CommentModel->getListByPage($map, 'id DESC', '*', 20);
        $pager = $lists->render();
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            $val = $this->CommentLogic->formatData($val);
        }
        unset($val);

        return $this->success('success', $lists);
    }

    /**
     * 新增文章评论
     */
    public function add()
    {
        if (request()->isPost()) {
            if($this->config_data['comment']['status'] == 0){
                return $this->error('评论功能暂时关闭！');
            }
            $article_id = input('article_id', 0, 'intval');
            $content = input('content', '', 'text');
            $uid = get_uid();

            if(empty($article_id)) return $this->error('参数错误');
            if(empty($content)) return $this->error('内容不能为空');

            if($this->config_data['comment']['audit'] == 1){
                $status = -1;
            }
            $res = $this->CommentModel->edit([
                'shopid' => $this->shopid,
                'uid' => $uid,
                'article_id' => $article_id,
                'content' => $content,
                'status' => $status
            ]);
            
            if($res){
                return $this->success('评论成功', $res);
            }else{
                return $this->error('评论失败');
            }
        }

        return $this->error('error');
    }

    /**
     * 点赞或取消
     */
    public function support()
    {
        if (request()->isPost()){
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
                $rs = $this->CommentModel->setStep($info_id,'support',$inc_value);
                return $this->success($msg_tip . '成功', $f_data['status']);
            }else{
                return $this->error($msg_tip . '失败');
            }
        }

        return $this->error('请求错误');
    }

}