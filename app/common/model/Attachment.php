<?php

namespace app\common\model;

use think\Model;
use think\facade\Db;
use think\facade\Filesystem;
use think\Image;
use OSS\OssClient;
use OSS\Core\OssException;
use Qcloud\Cos\Client as CosClient;
use Qcloud\Cos\Exception\ServiceResponseException;

class Attachment extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    public function setUploadtimeAttr($value)
    {
        return strtotime($value);
    }

    /**
     * 通用上传
     *
     * @param      <type>  $files   The files
     * @param      string  $type    The type
     * @param      array   $params  The parameters
     *
     * @return     <type>  ( description_of_the_return_value )
     */
    public function upload($files, $type = "file", $uid = 0, $enforce = 'auto')
    {
        if($type=='file'){
            $result = $this->file($files, $enforce);
        }
        if($type=='avatar'){
            $result = $this->avatar($files, $uid);
        }
        if($type=='base64'){
            $result = $this->base64($files);
        }

        return $result;

    }

    /**
     * 文件上传
     * @param      <type>         $files  The files
     * @return     array|boolean  ( description_of_the_return_value )
     */
    public function file($files, $enforce  = 'auto')
    {   
        if (empty($files)) {
            return false;
        }
        
        foreach($files as $file){
            //判断是否已经存在
            $sha1 = $file->hash('sha1');
            //处理已存在
            $file_info = $this->where(['sha1'=>$sha1])->find();
            if(!empty($file_info)){
                $file_res = [];
                $data = $file_info->toArray();
                $file_res['filename'] = $data['filename'];
                $file_res['ext'] = $data['ext'];
                $file_res['size'] = $data['size'];
                $file_res['attachment'] = $data['attachment'];
                $file_res['url'] = get_attachment_src($data['attachment']);
            }else{
                //构建返回数据
                $data['filename'] = $file->getOriginalName();
                $data['ext'] = $file->getOriginalExtension();
                $data['md5'] = $file->hash('md5');
                $data['sha1'] = $file->hash('sha1');
                $data['size'] = $file->getSize();
                $data['mime'] = $file->getMime();
                $data['type'] = 'file';  // 类型用字符串 image file audio video
                // 根据不同mimeType放入不同目录
                $mime_arr = explode('/', $data['mime']);

                switch($mime_arr[0])
                {
                    case 'image':
                        $file_dir = 'images';
                    break;
                    case 'audio':
                        $file_dir = 'audio';
                    break;
                    case 'video':
                        $file_dir = 'video';
                    break;
                    default:
                        $file_dir = 'file';
                }
                //获取上传驱动
                if($enforce == 'auto'){
                    // 获取上传驱动
                    if($file_dir == 'images'){
                        $driver = config('extend.PICTURE_UPLOAD_DRIVER');
                    }else{
                        $driver = config('extend.FILE_UPLOAD_DRIVER');
                    }
                }
                if($enforce == 'local'){
                    // 强制本地驱动
                    $driver = 'local';
                }

                $data['type'] = $mime_arr[0];
                $data['driver'] = $driver;
                $savename = Filesystem::disk('public')->putFile( $file_dir, $file);
                // 成功上传后 获取上传信息
                $data['attachment'] = $savename;
                $data['attachment'] = str_replace("\\","/",$data['attachment']);
                
                if($driver == 'local'){
                    // 本地无需处理
                }

                // 阿里云OSS
                if($driver == 'aliyun') {
                    $oss_res = $this->ossUpload('attachment/' . $data['attachment'], $file->getPathname());
                    // 上传成功
                    if($oss_res === true){
                        // 删除本地文件
                        $attachment_path = app()->getRootPath() . 'public/attachment';
                        $file_path = $attachment_path . '/' . $data['attachment'];
                        if(file_exists($file_path)){
                            unlink($file_path);
                        }
                        $data['driver'] = 'oss';
                    }
                }
                // 腾讯云COS
                if($driver == 'tencent') {
                    $cos_res = $this->cosUpload('attachment/' . $data['attachment'], $file->getPathname());
                    // 上传成功
                    if($cos_res === true){
                        // 删除本地文件
                        $attachment_path = app()->getRootPath() . 'public/attachment';
                        $file_path = $attachment_path . '/' . $data['attachment'];
                        if(file_exists($file_path)){
                            unlink($file_path);
                        }
                        $data['driver'] = 'cos';
                    }
                }

                // 写入数据库
                $this->save($data);
                // 返回数据
                $file_res = [];
                $file_res['filename'] = $data['filename'];
                $file_res['ext'] = $data['ext'];
                $file_res['size'] = $data['size'];
                $file_res['attachment'] = $data['attachment'];
                $file_res['url'] = get_attachment_src($data['attachment']);
            }
        }
        return $file_res;
    }

    /**
     * 头像上传
     *
     * @param      <type>         $files  The files
     * @return     array|boolean  ( description_of_the_return_value )
     */
    private function Avatar($files, $uid)
    {
        if (empty($files)) {
            return false;
        }
        foreach($files as $file){
            //判断是否已经存在
            $sha1 = $file->hash('sha1');
            //处理已存在图片
            $pic_info = $this->where(['sha1'=>$sha1])->find();
            if(!empty($pic_info)){
                $img = [];
                $data = $pic_info->toArray();
                $img['filename'] = $data['filename'];
                $img['ext'] = $data['ext'];
                $img['size'] = $data['size'];
                $img['attachment'] = $data['attachment'];
                $img['url'] = get_attachment_src($data['attachment']);
            }else{
                //构建返回数据
                $data['filename'] = $file->getOriginalName();
                $data['ext'] = $file->getOriginalExtension();
                $data['md5'] = $file->hash('md5');
                $data['sha1'] = $file->hash('sha1');
                $data['size'] = $file->getSize();
                $data['mime'] = $file->getMime();
                $data['type'] = 'image';  // 类型用字符串 pic file audio video
                $savename = Filesystem::disk('public')->putFile( 'avatar/' . $uid, $file);
                // 成功上传后 获取上传信息
                $data['attachment'] = $savename;
                $data['attachment'] = str_replace("\\","/",$data['attachment']);
                //获取上传驱动
                $driver = config('extend.PICTURE_UPLOAD_DRIVER');
                if($driver == 'local'){
                    // 本地无需处理
                }
                // 阿里云OSS
                if($driver == 'aliyun') {
                    $oss_res = $this->ossUpload($data['attachment'], $file->getPathname());
                    // 上传成功
                    if($oss_res === true){
                        // 删除本地文件
                        $attachment_path = app()->getRootPath() . 'public/attachment';
                        $file_path = $attachment_path . '/' . $data['attachment'];
                        if(file_exists($file_path)){
                            unlink($file_path);
                        }
                    }
                }
                // 腾讯云COS
                if($driver == 'tencent') {
                    $cos_res = $this->cosUpload($data['attachment'], $file->getPathname());
                    // 上传成功
                    if($cos_res === true){
                        // 删除本地文件
                        $attachment_path = app()->getRootPath() . 'public/attachment';
                        $file_path = $attachment_path . '/' . $data['attachment'];
                        if(file_exists($file_path)){
                            unlink($file_path);
                        }
                    }
                }

                // 写入数据库
                $this->save($data);
                // 返回数据
                $avatar = [];
                $avatar['filename'] = $data['filename'];
                $avatar['ext'] = $data['ext'];
                $avatar['size'] = $data['size'];
                $avatar['attachment'] = $data['attachment'];
                $avatar['url'] = get_attachment_src($data['attachment']);
                
            }
        }
        return $avatar;
    }

    /**
     * [base64 description] 未完成
     * @param  [type] $files [description]
     * @return [type]        [description]
     */
    public function base64($files)
    {
        $aData = $files;
        if ($aData == '' || $aData == 'undefined') {
            return false;
        }

        $result = [];
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $aData, $result)) {
            $base64_body = substr(strstr($aData, ','), 1);

            empty($aExt) && $aExt = $result[2];
        } else {
            $base64_body = $aData;
        }

        empty($aExt) && $aExt = 'jpg';

        $md5 = md5($base64_body);
        $sha1 = sha1($base64_body);

        $file_info = $this->where(['sha1'=>$sha1])->find();
        
        if ($file_info) {
            $file_res = [];
            $data = $file_info->toArray();
            $file_res['filename'] = $data['filename'];
            $file_res['size'] = $data['size'];
            $file_res['attachment'] = $data['attachment'];
            $file_res['url'] = get_attachment_src($data['attachment']);

        } else {
            //不存在则上传并返回信息
            //获取上传驱动
            $driver = config('extend.PICTURE_UPLOAD_DRIVER');
            if($driver == 'local'){
                // 本地无需处理
            }
            $date = date('Y-m-d');
            $saveName = uniqid();
            $savePath = '/attachment/images/' . $date . '/';

            $path = $savePath . $saveName . '.' . $aExt;
            if($driver == 'local'){
                //本地上传
                if(!file_exists('.' . $savePath)){
                    mkdir('.' . $savePath, 0777, true);
                }
                
                $data = base64_decode($base64_body);
                $rs = file_put_contents('.' . $path, $data);
            }
            else{
                $rs = false;
                //使用云存储
                $name = get_addon_class($driver);
                if (class_exists($name)) {
                    $class = new $name();
                    if (method_exists($class, 'uploadBase64')) {
                        $path = $class->uploadBase64($base64_body,$path);
                        $rs = true;
                    }
                }
            }
            if ($rs) {
                
                $pic['path'] = $path;
                $pic['driver'] = $driver;
                $pic['md5'] = $md5;
                $pic['sha1'] = $sha1;
                $pic['status'] = 1;
                $pic['create_time'] = time();
                $id = Db::name('attachment')->insertGetId($pic);

                return ['id' => $id, 'path' => get_pic_src($path)];
            } else {
                return false;
            }
        }
    }


    /**
     * 阿里云OSS上传
     * $object 文件名
     * $filepath 文件路径
     */
    public function ossUpload($object, $filePath)
    {
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录RAM控制台创建RAM账号。
        $accessKeyId = config('extend.OSS_ALIYUN_ACCESSKEYID');
        $accessKeySecret = config('extend.OSS_ALIYUN_ACCESSKEYSECRET');
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = config('extend.OSS_ALIYUN_ENDPOINT');
        // 设置存储空间名称。
        $bucket= config('extend.OSS_ALIYUN_BUCKET');
        // 设置文件名称。
        //$object = $file->getOriginalName();
        // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt。
        //$filePath = $file->getPathname();

        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $ossClient->uploadFile($bucket, $object, $filePath);


        } catch(OssException $e) {
            //printf(__FUNCTION__ . ": FAILED\n");
            //printf($e->getMessage() . "\n");
            return $e->getMessage();
        }

        return true;
    }

    /**
     * 腾讯云COS上传
     */
    protected function cosUpload($object, $filePath)
    {
        // SECRETID和SECRETKEY请登录访问管理控制台进行查看和管理
        $appid = '';
        $secretId = config('extend.COS_TENCENT_SECRETID'); //"云 API 密钥 SecretId";
        $secretKey = config('extend.COS_TENCENT_SECRETKEY'); //"云 API 密钥 SecretKey";
        $region = config('extend.COS_TENCENT_REGION'); //设置一个默认的存储桶地域
        $cosClient = new CosClient([
                'region' => $region,
                'schema' => 'http', //协议头部，默认为http
                'credentials'=> [
                    'secretId'  => $secretId ,
                    'secretKey' => $secretKey
                ]
        ]);
        
        try {
            $bucket = config('extend.COS_TENCENT_BUCKET'); //存储桶名称 格式：BucketName-APPID
            $key = $object; //此处的 key 为对象键，对象键是对象在存储桶中的唯一标识
            $srcPath = $filePath;//本地文件绝对路径
            $file = fopen($srcPath, "rb");
            if ($file) {
                $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => $file));
                //print_r($result);exit;
                return true;
            }
        } catch (\Exception $e) {
            echo "$e\n";
        }
    }

    /** 
     * 获取缩微图
     * @param $filename
     * @param int $width
     * @param string $height
     * @param int $type
     * @param bool $replace
     * @return mixed|string
     */
    public function getThumbImage($attachment, $width = 100, $height = 'auto', $replace = false)
    {
        // 获取图片存储类型
        $driver = config('extend.PICTURE_UPLOAD_DRIVER');
        if (strtolower($driver) == 'local') {
            $info = $this->localThumb($attachment, $width, $height, $replace);
            return $info;
        }else{
            // 远程图片处理
            // 阿里云OSS
            if(strtolower($driver) == 'aliyun'){
                try {
                    $oldimageinfo = getimagesize(config('extend.OSS_ALIYUN_BUCKET_DOMAIN') . '/attachment/' . $attachment);
                    $old_image_width = intval($oldimageinfo[0]);
                    $old_image_height = intval($oldimageinfo[1]);
                    if ($height == "auto") $height = $old_image_height * $width / $old_image_width;
                    if ($width == "auto") $width = $old_image_width * $width / $old_image_height;
                    if (intval($height) == 0 || intval($width) == 0) {
                        return 0;
                    }
                    $src = config('extend.OSS_ALIYUN_BUCKET_DOMAIN') . '/attachment/' . $attachment . '?x-oss-process=image/resize,m_fill,h_'.$height.',w_'.$width;
                    $info['src'] = $src;
                    $info['width'] = $old_image_width;
                    $info['height'] = $old_image_height;
                    return $info;
                } catch (\Exception $e) {
                    // 返还本地路径
                    $info = $this->localThumb($attachment, $width, $height, $replace);
                    $info['src'] = get_attachment_url() . $info['src'];
                    return $info;
                }
            }
            // 腾讯云COS
            if(strtolower($driver) == 'tencent'){
                try {
                    $oldimageinfo = getimagesize(config('extend.COS_TENCENT_BUCKET_DOMAIN') . '/attachment/' . $attachment);
                    $old_image_width = intval($oldimageinfo[0]);
                    $old_image_height = intval($oldimageinfo[1]);
                    if ($height == "auto") $height = $old_image_height * $width / $old_image_width;
                    if ($width == "auto") $width = $old_image_width * $width / $old_image_height;
    
                    $src = config('extend.COS_TENCENT_BUCKET_DOMAIN') . '/attachment/' . $attachment . '?imageView2/1/w/'.$width.'/h/'.$height;
                    $info['src'] = $src;
                    $info['width'] = $old_image_width;
                    $info['height'] = $old_image_height;
                    return $info;
                } catch (\Exception $e) {
                    // 返还本地路径
                    $info = $this->localThumb($attachment, $width, $height, $replace);
                    $info['src'] = get_attachment_url() . $info['src'];
                    return $info;
                }
            }
        }
    }

    /**
     * 本地缩微图处理
     */
    public function localThumb($attachment, $width = 100, $height = 'auto', $replace = false)
    {
        $UPLOAD_URL = '';
        $UPLOAD_PATH = PUBLIC_PATH . '/attachment/';
        $attachment = str_ireplace($UPLOAD_URL, '', $attachment); //将URL转化为本地地址
        $info = pathinfo($attachment);
        
        $oldFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'];
        $thumbFile = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_' . $width . '_' . $height . '.' . $info['extension'];

        $oldFile = str_replace('\\', '/', $oldFile);
        $thumbFile = str_replace('\\', '/', $thumbFile);

        $filename = ltrim($attachment, '/');
        $oldFile = ltrim($oldFile, '/');
        $thumbFile = ltrim($thumbFile, '/');
        
        if (!file_exists($UPLOAD_PATH . $oldFile)) {
            //原图不存在直接返回
            @unlink($UPLOAD_PATH . $thumbFile);
            $info['src'] = $oldFile;
            $info['width'] = intval($width);
            $info['height'] = intval($height);
            return $info;
        } elseif (file_exists($UPLOAD_PATH . $thumbFile) && !$replace) {
            //缩图已存在并且  replace替换为false
            $imageinfo = getimagesize($UPLOAD_PATH . $thumbFile);
            $info['src'] = $thumbFile;
            $info['width'] = intval($imageinfo[0]);
            $info['height'] = intval($imageinfo[1]);
            return $info;
        } else {
            //执行缩图操作
            // 获取原图尺寸
            $oldimageinfo = getimagesize($UPLOAD_PATH . $oldFile);
            $old_image_width = intval($oldimageinfo[0]);
            $old_image_height = intval($oldimageinfo[1]);
            if ($old_image_width <= $width && $old_image_height <= $height) {
                @unlink($UPLOAD_PATH . $thumbFile);
                @copy($UPLOAD_PATH . $oldFile, $UPLOAD_PATH . $thumbFile);
                $info['src'] = $thumbFile;
                $info['width'] = $old_image_width;
                $info['height'] = $old_image_height;
                return $info;
            } else {
                if ($height == "auto") $height = $old_image_height * $width / $old_image_width;
                if ($width == "auto") $width = $old_image_width * $width / $old_image_height;
                if (intval($height) == 0 || intval($width) == 0) {
                    return 0;
                }
                // 打开图片并处理
                $thumb = Image::open($UPLOAD_PATH . $filename);
                //默认裁切类型标识缩略图居中裁剪类型，先写死，后续版本增加后台设置
                $thumb->thumb($width, $height, Image::THUMB_CENTER);
                $thumb->save($UPLOAD_PATH . $thumbFile);
                $info['src'] = $thumbFile;
                $info['width'] = $old_image_width;
                $info['height'] = $old_image_height;
                return $info;
            }
        }
    }

    /** 
     * 裁切图片
     * @return mixed|string
     */
    public function cropImage($attachment, $crop)
    {
        $UPLOAD_PATH = PUBLIC_PATH . '/attachment/';
        $info = pathinfo($attachment);
        $file_path = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . $info['extension'];
        $file_path = str_replace("\\","/", $file_path);
        $file_path = ltrim($file_path, '/');
        $file_path = $UPLOAD_PATH . $file_path;

        //如果不裁剪，则发生错误
        if (!$crop) {
            return $attachment;
        }

        //解析crop参数
        $crop = explode(',', $crop);
        $x = $crop[0];
        $y = $crop[1];
        $w = $crop[2];
        $h = $crop[3];

        $driver = config('extend.PICTURE_UPLOAD_DRIVER');
        if (strtolower($driver) == 'local') {
            //本地图片处理
            $image = Image::open($file_path);
            //生成将单位换算成为像素
            //$x = $x * $image->width();
            //$y = $y * $image->height();
            //$w = $w * $image->width();
            //$h = $h * $image->height();

            //如果宽度和高度近似相等，则令宽和高一样
            if (abs($h - $w) < $h * 0.01) {
                $h = min($h, $w);
                $w = $h;
            }
            //调用组件裁剪
            $image->crop($w, $h, $x, $y);
            $image->save($file_path);
            
        }else{
            // 远程图片处理
            if(strtolower($driver) == 'aliyun'){
                $attachment = config('extend.OSS_ALIYUN_BUCKET_DOMAIN') . '/' . $attachment . '?x-oss-process=image/crop,x_'.$x.',y_'.$y.',w_'.$w.',h_'.$h;
            }

            if(strtolower($driver) == 'tencent'){
                $attachment = config('extend.COS_TENCENT_BUCKET_DOMAIN') . '/' . $attachment . '?imageMogr2/cut/' . $w .'x' . $h .'x'. $x .'x' . $y;
            }
        }

        //返回新文件的路径
        return  $attachment;
        
        
    }

    /**
     * 获取文件名
     */
    public function getFileName($attachment)
    {
        $filename = $this->where('attachment', $attachment)->value('filename');

        return $filename;
    }

    /**
     * 写入、更新数据表
     */
    public function edit($data)
    {
        if(!empty($data['id'])){
            $res = $this->update($data);
        }else{
            $res = $this->save($data);
        }
        
        if($res) $res = $this->id;

        return $res;
    }
}
