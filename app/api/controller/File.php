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
        // 强制上传方法，默认自动
        $enforce = input('enforce', 'auto', 'text');
        $uid = get_uid();
        $files = request()->file();

        if (empty($files)) {
            return $this->error('未选择文件');
        }

        $result = $this->Attachment->upload($files, 'file', $uid, $enforce);

        if(is_array($result)){
            return $this->result(200, '上传成功', $result);
        }else{
            return $this->result(0, '上传失败');
        }
    }

    /**
     * 用户头像上传
     * @return [type] [description]
     */
    public function avatar()
    {
        $uid = is_login();
        /* 调用文件上传组件上传文件 */
        $files = request()->file();
        
        if (empty($files)) {
            $return['code'] = 0;
            $return['msg'] = 'No Avatar Image upload or server upload limit exceeded';
            return json($return);
        }
        
        $arr = $this->Attachment->upload($files,'avatar',$uid);

        if(is_array($arr)){
            $return['code'] = 1;
            $return['msg'] = 'Upload successful';
            $return['data'] = $arr;
        }else{
            $return['code'] = 1;
            $return['msg'] =$this->Attachment->getError();
        }

        return json($return);
    }
    /**
     * [ueditor 编辑器方法]
     * @return [type] [description]
     */
    public function ueditor(){

        $action = input('action', '', 'text');
        switch($action){
            
            case 'config':
                $result = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(PUBLIC_PATH . '/static/common/lib/ueditor/php/config.json')), true);
            break;

            case 'uploadimage':
                $files = request()->file();
                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $res = $this->Attachment->upload($files,'file');
                
                $result['state'] ='SUCCESS';
                $result['url'] = $res['url'];
                
            break;

            case 'uploadscrawl':
                $files = input('upfile');
                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $arr = $this->Attachment->Attachment($files,'base64');
                
                $result['state'] ='SUCCESS';
                $result['url'] = $arr['url'];

            break;

            case 'uploadfile':

                $files = request()->file();

                if (empty($files)) {
                    $return['code'] = 0;
                    $return['msg'] = 'No file upload or server upload limit exceeded';
                    return json($return);
                }

                $arr = $this->Attachment->upload($files,'file');

                if(is_array($arr)){
                    $result['state'] ='SUCCESS';
                    $result['url'] = $arr['url'];
                    $result['original'] = $arr['filename'];
                }else{
                    $result['state'] = 'error';
                    $result['msg'] = $this->Attachment->getError();
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

                $arr = $this->Attachment->upload($files,'file');

                if(is_array($arr)){
                    $result['state'] ='SUCCESS';
                    $result['url'] = $arr['url'];
                    $result['original'] = $arr['filename'];
                }else{
                    $result['state'] = 'error';
                    $result['msg'] = $this->Attachment->getError();
                }
                return json($result);

            break;

            default:
            break;
        }
        return json($result);
    }

    /**
     * 文件数据写入附件表接口
     */
    public function attachment()
    {
        $data = input('post.');

        $res = $this->Attachment->edit($data);
        if($res){
            return $this->success('success');
        }else{
            return $this->error('error');
        }
    }

}
