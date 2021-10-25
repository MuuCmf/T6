<?php
use think\facade\Db;
use app\common\model\Attachment;

/**
 * 单图上传组件
 * @param  [type] $name      [description]
 * @param  [type] $image     [description]
 * @return [type]            [description]
 */
function single_image_upload($name, $image, $input = false){

    $image_path = get_attachment_src($image);
    $upload_picture = '上传图片';
    $delete_picture = '删除';
    $api = url('api/file/pic');
    //兼容name数组形式
    $input_name = $name;

    $name = preg_replace('/\[.*?\]/', '', $name);
    $html = <<<EOF
    <div class="single-image-upload image-upload controls">
EOF;
    if(!empty($image)){
    $html .= <<<EOF
        <div class="upload-img-box">
            <div class="upload-pre-item popup-gallery">
                <div class="each">
                    <img src="{$image_path}">
                    <div class="text-center opacity del_btn"></div>
                    <div data-id="{$image}" class="text-center del_btn">{$delete_picture}</div>
                </div>
            </div>
        </div>
EOF;
    }
    if($input == false){
        $html .= <<<EOF
        <div class="input-group">
            <input type="hidden" class="form-control attach" data-name="{$name}" name="{$input_name}" value="{$image}">
            <button id="upload_single_image_{$name}" class="btn btn-default" type="button">{$upload_picture}</button>
        </div>
EOF;
    }else{
        $html .= <<<EOF
        <div class="input-group">
            <input type="text" class="form-control attach" data-name="{$name}" name="{$input_name}" value="{$image}">
            <span class="input-group-btn">
                <button id="upload_single_image_{$name}" class="btn btn-default" type="button">{$upload_picture}</button>
            </span>
        </div>
EOF;
    }

    $html .= <<<EOF
    </div>
EOF;

$html .= <<<EOF
<script>
    $(function () {
        var uploader_{$name}= WebUploader.create({
            // 选完文件后，是否自动上传。
            auto: true,
            // swf文件路径
            swf: 'Uploader.swf',
            // 文件接收服务端。
            server: "{$api}",
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {id:'#upload_single_image_{$name}',multiple: false},
            // 只允许选择图片文件
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/jpg,image/jpeg,image/png'
            }
        });
        uploader_{$name}.on('fileQueued', function (file) {
            uploader_{$name}.upload();
            toast.showLoading();
        });
        /*上传成功**/
        uploader_{$name}.on('uploadSuccess', function (file, data) {
            if (data.code) {
                $("input[name='{$input_name}']").val(data.data.attachment);
                $("input[name='{$input_name}']").parent().parent().find('.upload-pre-item').html(
                    '<div class="each">' +
                    '<img src="'+ data.data.url+'">' +
                    '<div class="text-center opacity del_btn"></div>' +
                    '<div data-id="'+data.data.attachment+'" class="text-center del_btn">{$delete_picture}</div>'+
                    '</div>'
                );
                //重启webuploader,可多次上传
                uploader_{$name}.reset();
            } else {
                updateAlert(data.msg);
                setTimeout(function () {
                    $('#top-alert').find('button').click();
                    $(that).removeClass('disabled').prop('disabled', false);
                }, 1500);
            }
        });
        //上传完成
        uploader_{$name}.on( 'uploadComplete', function( file ) {
            toast.hideLoading();
        });

        //移除图片
        $('.single-image-upload').on('click','.del_btn',function(){
            var id = $(this).data('id');
            admin_image.removeImage($(this),id);
        })

    })
</script>
EOF;
    return $html;
}

/**
 * 音频上传组件
 * @param  string $name      唯一标示
 * @param  string $audio     音频路径
 * @param  bool $input       是否显示输入框
 * @return [type]            [description]
 */
function single_audio_upload($name, $audio, $input = false){

    $audio_path = get_attachment_src($audio);
    $upload = '上传音频';
    $delete = '删除';
    $api = url('api/file/pic');
    //兼容name数组形式
    $name = preg_replace('/\[.*?\]/', '', $name);
    $html = <<<EOF
        <div class="single-audio-upload audio-upload controls">
    EOF;

    $html .= '<div class="upload-audio-box">';
    if(!empty($audio)){
    $html .= <<<EOF
        <div class="upload-pre-item">
            <audio id="audio_play_{$name}" controls="controls">
                <source src="{$audio_path}" />
            </audio>
        </div>
    EOF;
}
    $html .= '</div>';

    if($input == false){
        $html .= <<<EOF
        <div class="input-group">
            <input type="hidden" class="form-control attach" name="{$name}" value="{$audio}">
            <button id="upload_single_audio_{$name}" class="btn btn-default" type="button">{$upload}</button>
        </div>
        EOF;
    }else{
        $html .= <<<EOF
        <div class="input-group">
            <input type="text" class="form-control attach" data-name="{$name}" name="{$name}" value="{$audio}">
            <span class="input-group-btn">
                <button id="upload_single_audio_{$name}" class="btn btn-default" type="button">{$upload}</button>
            </span>
        </div>
EOF;
    }

    $html .= <<<EOF
    </div>
EOF;

$html .= <<<EOF
<script>
    $(function () {
        var uploader_{$name}= WebUploader.create({
            // 选完文件后，是否自动上传。
            auto: true,
            // swf文件路径
            swf: 'Uploader.swf',
            // 文件接收服务端。
            server: "{$api}",
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {id:'#upload_single_audio_{$name}',multiple: false},
            // 只允许选择图片文件
            accept: {
                title: 'Images',
                extensions: 'mp3',
                mimeTypes: 'audio/mpeg3,audio/x-mpeg-3'
            }
        });
        uploader_{$name}.on('fileQueued', function (file) {
            uploader_{$name}.upload();
            toast.showLoading();
        });
        /*上传成功**/
        uploader_{$name}.on('uploadSuccess', function (file, data) {
            if (data.code) {
                $("input[name='{$name}']").val(data.data.attachment);
                $("input[name='{$name}']").parent().parent().find('.upload-audio-box').html(
                    '<div class="upload-pre-item">'+
                        '<audio id="audio_play_{$name}" controls="controls">' +
                            '<source src="'+ data.data.url+'" />' +
                        '</audio>' +
                    '</div>'
                );
                //重启webuploader,可多次上传
                uploader_{$name}.reset();
            } else {
                updateAlert(data.msg);
                setTimeout(function () {
                    $('#top-alert').find('button').click();
                    $(that).removeClass('disabled').prop('disabled', false);
                }, 1500);
            }
        });
        //上传完成
        uploader_{$name}.on( 'uploadComplete', function( file ) {
            toast.hideLoading();
        });
    });
</script>
EOF;
    return $html;
}



function single_video_upload($name, $video){

    $video_path = get_attachment_src($video);
    $api = url('api/file/file');
    $input_name = $name;
    //兼容name数组形式
    $name = preg_replace('/\[.*?\]/', '', $name);
    $html = <<<EOF
<div class="single-video-upload image-upload controls">
    <input class="attach" type="text" data-name="{$name}" name="{$input_name}" value="{$video}"/>
    <div class="upload-video-box">
EOF;
    if(!empty($video)){
        $html .= <<<EOF
        <div class="box-item">
            <video controls >
            	 <source src="{$video_path}" ></source>
	                		您的浏览器暂不支持播放该视频，请升级至最新版浏览器。
            </video>
            <div class="remove-box text-center opacity del_btn">删除</div>
        </div>
EOF;
    }

    $html .= <<<EOF
    </div>
    <div id="upload_single_video_{$name}" class=""></i>上传视频</div>
</div>

<script>
    $(function () {
        var uploader_{$name}= WebUploader.create({
            // 选完文件后，是否自动上传。
            auto: true,
            // swf文件路径
            swf: 'Uploader.swf',
            // 文件接收服务端。
            server: "{$api}",
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: {id:'#upload_single_video_{$name}',multiple: false},
            // 只允许选择图片文件
            accept: {
                title: 'Video',
                extensions: 'mp4,flv,m3u8,m4v'
            }
        });
        uploader_{$name}.on('fileQueued', function (file) {
            uploader_{$name}.upload();
            toast.showLoading();
        });
        /*上传成功**/
        uploader_{$name}.on('uploadSuccess', function (file, data) {
            if (data.code) {
                $("input[name='{$name}']").val(data.data.attachment);
                $("input[name='{$name}']").parent().find('.upload-video-box').html(
                    '<div class="box-item">' +
                            '<video controls >' + 
            	                '<source src="' + data.data.url + '" ></source>' +
	                		    '您的浏览器暂不支持播放该视频，请升级至最新版浏览器。' +
                            '</video>' +
                        '<div class="remove-box text-center opacity del_btn">删除</div>' +
                    '</div>'
                );
                //重启webuploader,可多次上传
                uploader_{$name}.reset();
            } else {
                updateAlert(data.msg);
                setTimeout(function () {
                    $('#top-alert').find('button').click();
                    $(that).removeClass('disabled').prop('disabled', false);
                }, 1500);
            }
        });
        //上传完成
        uploader_{$name}.on( 'uploadComplete', function( file ) {
            toast.hideLoading();
        });

        //移除图片
        $('.single-video-upload').on('click','.del_btn',function(){
            $(this).parent().remove();
        })

    })
</script>
EOF;
    return $html;
}

/**
 * 单文件上传组件
 * @param  [type] $name      [description]
 * @param  [type] $image_id [description]
 * @return [type]           [description]
 */
function single_file_upload($name, $file){
    $api = url('api/file/file');

    $html = <<<EOF
    <div id="uploader-{$name}" >
    <div class="file-list" data-drag-placeholder="请拖拽文件到此处"></div>
    <button type="button" class="btn btn-primary uploader-btn-browse"><i class="icon icon-cloud-upload"></i> 选择文件</button>
    </div>
    <script>
        $(function () {
            $('#uploader-{$name}').uploader({
                autoUpload: true,            // 当选择文件后立即自动进行上传操作
                url: "{$api}",  // 文件上传提交地址
                deleteActionOnDone:function(file, doRemoveFile){
                    console.log(file)
                    console.log(doRemoveFile)
                },
EOF;

    if (is_array($file)){
        $image_path = get_attachment_src($file['attachment']);
        $html .= <<<EOF
        staticFiles: [
                    {name: {$file['name']}, url: "{$image_path}"},
                ],//初始化文件列表
EOF;

    }elseif (!empty($file)){
        $image_path = get_attachment_src($file);
        $html .= <<<EOF
        staticFiles: [
                    {name: '文件', url: "{$image_path}"},
                ],//初始化文件列表
EOF;
    }
    $html .= <<<EOF
                limitFilesCount:1,//限制文件数量
                responseHandler: function(responseObject, file) {
                    responseObject = JSON.parse(responseObject.response);
                    // 当服务器返回的文本内容包含 `'error'` 文本时视为上传失败
                    if(responseObject.code != 200) {
                        return '上传失败。服务器返回了一个错误：' + responseObject.msg;
                    }
                }
            });
        })
    </script>
EOF;
    return $html;
}

/**
 * 多图上传
 * @param  [type] $name [description]
 * @param  [type] $ids  [description]
 * @return [type]       [description]
 */
function multi_image_upload($name, $images = '')
{
    $upload_picture = '选择图片';
    $delete_picture = '删除';
    $picture_exists = '该图片已存在';
    $limit_exceed = '超过图片限制';
    $api = url('api/file/pic');

    $html = '';
    $html .= '
    <div class="multi-image-upload image-upload controls">
        <input class="attach" type="hidden" name="'.$name.'" value="'.$images.'"/>
        <div class="upload-img-box">
            <div class="upload-pre-item popup-gallery">';
    if(!empty($images)){
        $aIds = explode(',',$images);
        foreach($aIds as $aId){
            $path = get_attachment_src($aId);
            $html .= '
                <div class="each">
                    <img src="'.$path.'">
                    <div class="text-center opacity del_btn"></div>
                    <div data-id="'.$aId.'" class="text-center del_btn">'.$delete_picture.'</div>
                </div>
            ';
        }
    }
    
    $html .= '
            </div>
        </div>
        <div id="upload_multi_image_'.$name.'">'.$upload_picture.'</div>
    </div>
    ';       
    $html .= <<<EOF
    <script>
    $(function () {
        var id = "#upload_multi_image_{$name}";
        var limit = parseInt(6);
        var uploader_{$name}= WebUploader.create({
            // 选完文件后，是否自动上传。
            swf: 'Uploader.swf',
            // 文件接收服务端。
            server: "{$api}",
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素
            pick: {'id': id, 'multi': true},
            fileNumLimit: limit,
            // 只允许文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/image/jpg,image/jpeg,image/png'
            }
        });
        uploader_{$name}.on('fileQueued', function (file) {
            uploader_{$name}.upload();
            toast.showLoading();
        });
        uploader_{$name}.on('uploadFinished', function (file) {
            uploader_{$name}.reset();
        });
        /*上传成功**/
        uploader_{$name}.on('uploadSuccess', function (file, data) {
          if (data.code == 200) {
            var ids = $("input[name='{$name}']").val();
            ids = ids.split(',');
            if( ids.indexOf(data.data.attachment) == -1){
                var rids = admin_image.upAttachVal('add',data.data.attachment, $("[name='{$name}']"));
                if(rids.length>limit){
                    updateAlert({$limit_exceed});
                    return;
                }
                
                $("input[name='{$name}']").parent().find('.upload-pre-item').append(
                    '<div class="each">'+
                    '<img src="'+ data.data.url+'">'+
                    '<div class="text-center opacity del_btn"></div>' +
                    '<div data-id="'+data.data.attachment+'" class="text-center del_btn">{$delete_picture}</div>'+
                    '</div>'
                );
            }else{
                updateAlert({$picture_exists});
            }
        } else {
            updateAlert(data.msg);
            setTimeout(function () {
                $('#top-alert').find('button').click();
                $(that).removeClass('disabled').prop('disabled', false);
            }, 1500);
        }
        });
        //上传完成
        uploader_{$name}.on( 'uploadComplete', function( file ) {
            toast.hideLoading();
        });

        //移除图片
        $('.multi-image-upload').on('click','.del_btn',function(){
            var id = $(this).data('id');
            admin_image.removeImage($(this),id);
        })
    });
    </script>
EOF;

    return $html;
}

/**
 * 通过ID获取附件路径
 */
function pic($id)
{
    if (empty($id)) {
        return false;
    }
    $picture = Db::name('attachment')->where(['id'=>$id])->find();
    $picture['url'] = get_attachment_url($picture['attachment']);
    
    return $picture['url'];
}

/**通过ID/路径获取到图片的缩略图
 * @param        $cover_id 图片的ID
 * @param int $width 需要取得的宽
 * @param string $height 需要取得的高
 * @param bool $replace 是否强制替换
 * @return string
 * @auth 大蒙
 */
function get_thumb_image($attachment, $width = 100, $height = 'auto', $replace = false, $type = 'attachment')
{
    //不存在http://
    $not_http_remote=(strpos($attachment, 'http://') === false);
    //不存在https://
    $not_https_remote=(strpos($attachment, 'https://') === false);

    if ($not_http_remote && $not_https_remote) {
        $Attachment = new Attachment();
        $picture = Db::name('attachment')->where(['attachment' => $attachment])->find();
        
        if (empty($picture)) {
            return request()->domain() . '/static/common/images/nopic.png';
        }

        // 本地图片处理
        if ($picture) {
            $attach = $Attachment->getThumbImage($picture['attachment'], $width, $height, $replace);
            return get_attachment_src($attach['src']);
        } else {
        // 远程云存储图片处理
            $new_img = $picture['attachment'];
            
            return get_attachment_src($new_img);
        }
    }else{
        return $attachment;
    }
    
}

/**简写函数，等同于get_thumb_image（）
 * @param $id 图片id
 * @param int $width 宽度
 * @param string $height 高度
 * @param int $type 裁剪类型，0居中裁剪
 * @param bool $replace 裁剪
 * @return string
 */
function thumb($attachment, $width = 100, $height = 'auto', $type = 0, $replace = false)
{
    return get_thumb_image($attachment, $width, $height, $type, $replace);
}

/**
 * 在富文本中获取第一张图
 * @param $str_img
 * @return mixed
 */
function get_pic($str_img)
{
    preg_match_all("/<img.*\>/isU", $str_img, $ereg); //正则表达式把图片的整个都获取出来了
    $img = $ereg[0][0]; //图片
    $p = "#src=('|\")(.*)('|\")#isU"; //正则表达式
    preg_match_all($p, $img, $img1);
    $img_path = $img1[2][0]; //获取第一张图片路径
    return $img_path;
}

/**
 * 附件路径
 * @param $path
 * @return mixed
 */
function get_attachment_src($attachment)
{
    //不存在http://
    $not_http_remote=(strpos($attachment, 'http://') === false);
    //不存在https://
    $not_https_remote=(strpos($attachment, 'https://') === false);

    if ($not_http_remote && $not_https_remote) {
        //获取上传驱动
        $driver = config('extend.PICTURE_UPLOAD_DRIVER');
        if ($driver == 'local') {
            //本地url
            return get_attachment_url() . str_replace('//', '/', $attachment); //防止双斜杠的出现
        }
        if ($driver == 'aliyun') {
            return config('extend.OSS_ALIYUN_BUCKET_DOMAIN') . '/' . $attachment;
        }
        if ($driver == 'tencent') {
            return config('extend.COS_TENCENT_BUCKET_DOMAIN') . '/' . $attachment;
        }
    }else{
        return $attachment;
    }
    
}


/**
 * 获取本地附件目录的根Url
 * @return string
 */
function get_attachment_url()
{
    return get_http_https().$_SERVER['SERVER_NAME'] . '/attachment/';
}