<?php
/**
 * 生成宣传海报
 * @param array  参数,包括图片和文字
 * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
 * @return [type] [description]
 */
function createPoster($config=array(),$filename="")
{
    $imageDefault = array(
        'top'=>0,
        'width'=>100,
        'height'=>100,
        'opacity'=>100
    );
    $textDefault = array(
        'text'=>'',
        'top'=>0,
        'fontSize'=>32,       //字号
        'fontColor'=>'255,255,255', //字体颜色
        'angle'=>0,
    );
    $background = $config['background'];//海报最底层得背景

    //背景方法
    $backgroundInfo = getimagesize($background);

    $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
    $background = $backgroundFun($background);
    $backgroundWidth = imagesx($background);  //背景宽度
    $backgroundHeight = imagesy($background);  //背景高度
    $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
    $color = imagecolorallocate($imageRes, 0, 0, 0);
    imagefill($imageRes, 0, 0, $color);
    //imageColorTransparent($imageRes, $color);  //颜色透明
    imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
    //处理图片
    if(!empty($config['image'])){
        foreach ($config['image'] as $key => $val) {
            if(empty($val['url'])) continue;
            //dump($val['url']);
            $val = array_merge($imageDefault,$val);
            //dump($val);
            $referer = $_SERVER["HTTP_REFERER"];
            if(empty($referer)){
                $referer = request()->domain();
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $val['url']);
            curl_setopt($ch, CURLOPT_REFERER, $_SERVER["HTTP_REFERER"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            $output = curl_exec($ch);
            curl_close($ch);

            $info = getimagesizefromstring($output);
            $resWidth = $info[0];
            $resHeight = $info[1];
            $res = imagecreatefromstring($output);
            //圆角图片
            if(isset($val['radius']) && $val['radius'] == true){
                $res = radius_img($output);
            }

            //建立画板 ，缩放图片至指定尺寸
            $canvas = imagecreatetruecolor($val['width'], $val['height']);
            $color = imagecolorallocate($canvas,255,255,255);
            imagecolortransparent($canvas,$color);
            imagefill($canvas, 0, 0, $color);
            //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
            imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
            if(!empty($val['left'])){
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
            }else{
                $val['left'] = ($backgroundWidth-$val['width'])/2;//居中对齐
            }

            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
            //放置图像
            //imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
            imagecopymerge($imageRes,$canvas, $val['left'] ?? 0,$val['top'] ?? 0,$val['right'] ?? 0,$val['bottom'] ?? 0,$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
        }
    }
    //处理文字
    if(!empty($config['text'])){
        foreach ($config['text'] as $key => $val) {
            $val = array_merge($textDefault,$val);
            $content = '';
            // 将字符串拆分成一个个单字 保存到数组 letter 中
            $letter = [];
            for ($i=0;$i<mb_strlen($val['text']);$i++) {
                $letter[] = mb_substr($val['text'], $i, 1);
            }
            foreach ($letter as $l) {

                $teststr = $content.''.$l;
                $fontBox = imagettfbbox($val['fontSize'],0,$val['fontPath'],$teststr);
                // 判断拼接后的字符串是否超过预设的宽度

                if (($fontBox[2] > ($backgroundWidth-350)) && ($content !== "")) {
                    $content .= "\n";
                }else{
                    $content .= $l;
                }
            }
            list($R,$G,$B) = explode(',', $val['fontColor']);
            $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
            $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];

            //换行后居中
            $text_arr = explode(PHP_EOL,$content);

            $left = 0;
            $top = 0;
            foreach ($text_arr as $key=>$v) {
                $arr = imagettfbbox($val['fontSize'],0,$val['fontPath'],$v);
                $text_width = $arr[2]-$arr[0];
                if(!empty($val['left'])){
                    $left = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                }else{
                    $left = ($backgroundWidth-$text_width)/2;//居中对齐
                }
                if($key > 0){
                    $top = $key * ($val['top'] + $val['fontSize'] + 20);
                }else{
                    $top = $val['top'];
                }

                imagefttext($imageRes, $val['fontSize'], $val['angle'], $left, $top, $fontColor, $val['fontPath'], $v);
            }

            /*
            $arr = imagettfbbox($val['fontSize'],0,$val['fontPath'],$content);
            $text_width = $arr[2]-$arr[0];
            imagefttext($imageRes, $val['fontSize'], $val['angle'], ($backgroundWidth-$text_width)/2, $val['top'], $fontColor, $val['fontPath'], $content);
            */
        }
    }
    //dump($filename);
//    dump($imageRes);exit;

    //生成图片
    if(!empty($filename)){
        $res = imagejpeg ($imageRes,$filename,90); //保存到本地
        imagedestroy($imageRes);
        if(!$res) return false;
        return $filename;
    }else{
        header("content-type: image/png");
        imagejpeg ($imageRes);     //在浏览器上显示
        imagedestroy($imageRes);
    }
}

/**
 * 处理圆角图片
 * @return [type]           [description]
 */
function radius_img($src) {

    $info = getimagesizefromstring($src);
    $w = $info[0];
    $h = $info[1];
    $src = imagecreatefromstring($src);
    $newpic = imagecreatetruecolor($w,$h);
    imagealphablending($newpic,false);
    $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
    imagefill($newpic, 0, 0, $transparent);

    $r=$w/2;
    for($x=0;$x<$w;$x++)
        for($y=0;$y<$h;$y++){
            $c = imagecolorat($src,$x,$y);
            $_x = $x - $w/2;
            $_y = $y - $h/2;
            if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
                imagesetpixel($newpic,$x,$y,$c);
            }else{
                imagesetpixel($newpic,$x,$y,$transparent);
            }
        }
    imagesavealpha($newpic, true);
    //imagepng($newpic);
    //imagedestroy($newpic);
    //imagedestroy($src);

    return $newpic;
}