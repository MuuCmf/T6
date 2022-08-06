<?php
namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Feedback as FeedbackModel;

/**
 * 收藏接口
 * Class Favorites
 * @package app\minishop\controller
 */
class Feedback extends Api
{
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];

    /**
     * 建议、反馈
     */
    public function add()
    {
        $uid = request()->uid;
        $content = input('post.content','');
        if (empty($content)){
            $this->error('内容不能为空');
        }
        if (input('?post.images')){
            $images = input('post.images');
            $images = explode(',', $images);
        }else{
            $images = '';
        }
        $data = [
            'shopid' => $this->shopid,
            'content' => $content,
            'images' => $images,
            'uid' => $uid,
        ];
        $res = (new FeedbackModel())->edit($data);
        if ($res){
            return $this->success('提交成功，我们会尽快处理您的反馈');
        }
        return $this->error('网络异常，请稍后再试');
    }

}