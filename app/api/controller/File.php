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
     * 获取文件列表
     * 
     * @return json 返回文件列表数据
     * 
     * 支持以下查询参数:
     * @param string $keyword 关键字搜索
     * @param string $driver 存储驱动类型
     * @param string $type 文件类型
     * @param int $rows 每页数量,默认20条
     * @param string $order_field 排序字段,默认id
     * @param string $order_type 排序方式,默认desc
     * 
     * 返回数据包含:
     * - 基础文件信息
     * - 腾讯云点播媒体文件额外信息(psign和播放地址)
     */
    public function lists()
    {
        // 关键字
        $keyword = input('keyword','','text');
        // 驱动
        $driver = input('driver','','text');
        // 类型
        $type = input('type','','text');
        $rows = input('rows',20, 'intval');
        // 查询条件
        $map = [
            ['shopid', '=', 0],
            ['uid', '=', get_uid()]
        ];
        if(!empty($keyword)){
            $map[] = ['filename', 'like', '%'.$keyword.'%'];
        }
        if(!empty($driver)){
            $map[] = ['driver', '=', $driver];
        }
        if(!empty($type)){
            $map[] = ['type', '=', $type];
        }
        // 排序
        $order_field = input('order_field', 'id', 'text');
        $order_type = input('order_type', 'desc', 'text');
        $order = $order_field . ' ' . $order_type;
        $fields = '*';
        $lists = $this->Attachment->getListByPage($map, $order, $fields, $rows);
        $lists = $lists->toArray();
        
        foreach($lists['data'] as &$val){
            if($val['driver'] == 'tcvod'){
                $data = $this->Attachment->vodMediaHandle($val['file_id'], $val['attachment']);
                if(!empty($data)) {
                    $val['psign'] = $data['psign'];
                    $val['all_media_url'] = $data['all_media_url'];
                }
            }
        }
        unset($val);

        // 返回数据
        return $this->success('success', $lists);
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
