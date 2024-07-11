<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Attachment;

/**
 * 文件控制器
 * 主要用于下载模型的文件上传和下载
 */

class File extends Api
{
    //添加token验证中间件
    protected $middleware = [
        'app\\common\\middleware\\CheckAuth',
    ];
    protected $Attachment;
    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        parent::__construct();

        $this->Attachment = new Attachment();
    }

    /* 通用文件上传 */
    public function upload()
    {
        $shopid = input('shopid', 0, 'intval');
        // 强制上传方法，默认自动
        $enforce = input('enforce', 'auto', 'text');
        // 自定义文件名参数
        $filename = input('filename', '', 'text');
        $uid = get_uid();
        $files = request()->file();

        if (empty($files)) {
            return $this->error('未选择文件');
        }

        $result = $this->Attachment->upload($shopid, $files, 'file', $uid, $enforce, $filename);

        if (is_array($result) && $result['code'] == 200) {
            return $this->result(200, '上传成功', $result);
        } else {
            $err_msg = '上传失败';
            if(!empty($result['msg'])){
                $err_msg = $result['msg'];
            }
            return $this->result(0, $err_msg);
        }
    }

    /**
     * 用户头像上传
     * @return [type] [description]
     */
    public function avatar()
    {
        $shopid = input('shopid', 0, 'intval');
        $uid = get_uid();
        /* 调用文件上传组件上传文件 */
        $files = request()->file();

        if (empty($files)) {
            $return['code'] = 0;
            $return['msg'] = 'No Avatar Image upload or server upload limit exceeded';
            return json($return);
        }

        $arr = $this->Attachment->upload($shopid, $files, 'avatar', $uid);

        if (is_array($arr)) {
            $return['code'] = 200;
            $return['msg'] = 'Upload successful';
            $return['data'] = $arr;
        } else {
            $return['code'] = 0;
            $return['msg'] = 'Upload failed';
        }

        return json($return);
    }
    /**
     * [ueditor 编辑器方法]
     * @return [type] [description]
     */
    public function ueditor()
    {
        $shopid = input('shopid', 0, 'intval');
        $action = input('action', '', 'text');
        switch ($action) {

            case 'config':
                $result = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(PUBLIC_PATH . '/static/common/lib/ueditor/php/config.json')), true);
                return json($result);
                break;

            case 'uploadimage':
                $files = request()->file();
                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $res = $this->Attachment->upload($shopid, $files, 'file');
                $res['state'] = 'SUCCESS';

                return json($res);
                break;

            case 'uploadscrawl':
                $files = input('upfile');
                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $arr = $this->Attachment->Attachment($shopid, $files, 'base64');

                $result['state'] = 'SUCCESS';
                $result['url'] = $arr['url'];
                return json($result);
                break;

            case 'uploadfile':

                $files = request()->file();

                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $arr = $this->Attachment->upload($shopid, $files, 'file');

                if (is_array($arr) && $arr['code'] == 200) {
                    $result['state'] = 'SUCCESS';
                    $result['url'] = $arr['url'];
                    $result['original'] = $arr['filename'];
                } else {
                    $result['state'] = 'error';
                    $result['msg'] = 'Upload failed';
                }
                return json($result);

                break;

            case 'uploadvideo':
                $files = request()->file();

                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $arr = $this->Attachment->upload($shopid, $files, 'file');

                if (is_array($arr) && $arr['code'] == 200) {
                    $result['state'] = 'SUCCESS';
                    $result['url'] = $arr['url'];
                    $result['original'] = $arr['filename'];
                } else {
                    $result['state'] = 'error';
                    $result['msg'] = 'Upload fail';
                }
                return json($result);

                break;

            default:
                break;
        }
    }

    /**
     * 文件数据写入附件表接口
     */
    public function attachment()
    {
        $data = input('post.');
        $data['uid'] = get_uid();
        
        $res = $this->Attachment->edit($data);
        if ($res) {
            return $this->success('success');
        } else {
            return $this->error('error');
        }
    }

    /**
     * 删除附件数据风险较大，仅可删除自身上传数据
     * （前台暂不提供）
     */
    public function delete()
    {
    }
}
