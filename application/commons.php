<?php
// 应用公共文件

use \app\common\model\AdminLog;
use \app\backman\model\Agent;

function AdminLog($uid,$action){
    $logInfo = [
        'user_id'=>$uid,
        'desc'=>$action,
        'action'=>request()->path(),
        'timestamp'=>date('Y-m-d H:i:s'),
    ];
    return AdminLog::insertGetId($logInfo);
}
/**
 * 密码加密方法
 * @param string $pw 要加密的字符串
 * @return string
 */
function sp_password($pw,$authcode=''){
    $result="###".md5(md5($authcode.$pw));
    return $result;
}
/**
 * 密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $password_in_db 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function sp_compare_password($password,$password_in_db,$authcode = ''){
    if(strpos($password_in_db, "###")===0){
        return sp_password($password,$authcode)==$password_in_db;
    }else{
        return sp_password_old($password)==$password_in_db;
    }
}
/**
 * 密码加密方法 (X2.0.0以前的方法)
 * @param string $pw 要加密的字符串
 * @return string
 */
function sp_password_old($pw,$authcode = ''){
    $decor=md5($authcode);
    $mi=md5($pw);
    return substr($decor,0,12).$mi.substr($decor,-4,4);
}


if(!function_exists('load_config')){

    function load_config($file, $enforce = true) {
        $file = Env::get('root_path') . $file. '.php';
        if (!is_file($file)) {
            if ($enforce) {
                throw new \Exception("File '{$file}' not found", 500);
            }
            return array();
        }
        return require($file);
    }
}
if(!function_exists('save_config')){
    /**
     * 配置保存
     * @param $file
     * @param $config
     * @return array|bool
     */
    function save_config($file, $config) {
        if (empty($config) || !is_array($config)) {
            return array();
        }
        $conf = load_config($file);
        $config = array_merge($conf, $config);
        $confString = var_export($config, true);
        $find = array("'true'", "'false'", "'1'", "'0'");
        $replace = array("true", "false", "1", "0");
        $confString = str_replace($find, $replace, $confString);
        $confString = "<?php \n return " . $confString . ';';
        if (file_put_contents(Env::get('root_path') . $file . '.php', $confString)) {
            return true;
        } else {
            return false;
        }
    }
}
/**
 * GET 请求
 * @param string $url
 */
function http_get($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

/**
 * 遍历获取目录下的指定类型的文件
 * @param $path
 * @param array $files
 * @return array
 */
function getfiles($path, $allowFiles, &$files = array())
{
    $config = load_config('application/upload');
    if (!is_dir($path)) return null;
    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
    $handle = opendir($path);
    while (false !== ($file = readdir($handle))) {
        if ($file != '.' && $file != '..') {
            $path2 = $path . $file;
            if (is_dir($path2)) {
                getfiles($path2, $allowFiles, $files);
            } else {
                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
                    $Rootpath = env('root_path') . 'public' . DS.'uploads/';
                    $files[] = array(
                        // 'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
                        'url'=> '/uploads/'.substr($path2, strlen($Rootpath)),
                        'mtime'=> filemtime($path2)
                    );
                }
            }
        }
    }
    return $files;
}
/**
 * curl请求指定url (get)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curl($url, $data = [])
{
    // 处理get数据
    if (!empty($data)) {
        $url = $url . '?' . http_build_query($data);
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

/**
 * curl请求指定url (post)
 * @param $url
 * @param array $data
 * @return mixed
 */
function curlPost($url, $data = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
/**
 * 获取代理的直属上级
 */
function getAgentParent($id,$field = 'nickname'){
    if($id == 0){
        $value = '总部';
    }else{
        $value = Agent::where('id',$id)->value($field);
    }
    return $value;
}

if (!function_exists('createPoster2')) {
    /**
     * 生成宣传海报
     * @param array  参数,包括图片和文字
     * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
     * @return [type] [description]
     */
    function createPoster2($config = array(), $filename = ""){
        if(empty($filename)){
            header("content-type: image/png");
        }
        $imageDefault = array(
            "left"=>0,
            "top"=>0,
            "right"=>0,
            "bottom"=>0,
            "width"=>100,
            "height"=>100,
            "opacity"=>100
        );
        $textDefault = array(
            "text"=>'',
            "left"=>0,
            "top"=>0,
            "fontSize"=>32,
            "fontColor"=>'255,255,255',
            "angle"=>0,
        );
        // $background = @imagecreatefrompng($config['background']);
        $backgroundInfo = getimagesize($config['background']);
        if($backgroundInfo['mime'] == 'image/jpeg'){
            $background = @imagecreatefromjpeg($config['background']);
        }else if($backgroundInfo['mime'] == 'image/gif'){
            $background = @imagecreatefromgif($config['background']);
        }else if($backgroundInfo['mime'] == 'image/png'){
            $background = @imagecreatefrompng($config['background']);
        }
        $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2],false);
        $backgroundFun = $backgroundFun($config['background']);
        // $backgroundWidth = imagesx($background); // 图片宽度
        // $backgroundHeight = imagesy($background); // 图片高度
        $backgroundWidth = $backgroundInfo[0]; // 图片宽度
        $backgroundHeight = $backgroundInfo[1]; // 图片高度
        $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagecolortransparent($imageRes,$color); //3.设置透明色
        imagefill($imageRes, 0, 0, $color);
        imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
        // imagecopyresampled($imageRes,$background,0,0,0,0,$backgroundWidth,$backgroundHeight,$backgroundWidth,$backgroundHeight);
        if(!empty($config['image'])){
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault,$val);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
                if($val['stream']){
                    //如果传的是字符串图像流
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                //建立画板 ，缩放图片至指定尺寸
                $canvas=imagecreatetruecolor($val['width'], $val['height']);
                $color=imagecolorallocate($canvas,255,255,255); //2.上色
                imagecolortransparent($canvas,$color); //3.设置透明色
                imagefill($canvas, 0, 0, $color);
                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                //放置图像
                if($val['center'] == '1'){

                    $x         = ceil((750 - $val['width']) / 2); //计算文字的水平位置

                    imagecopymerge($imageRes,$canvas, $x,$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);
                }else{
                    imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);
                }
            }
        }
        //处理文字
        if(!empty($config['text'])){
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault,$val);
                list($R,$G,$B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
                $text = autowrap($val['fontSize'],0,$val['fontPath'],$val['text'],710); // 自动转行
                if($val['center'] == '1'){
                    $fontWidth = imagefontwidth($val['fontSize']);
                    $textWidth = $fontWidth * mb_strlen($val['text']);
                    $x         = ceil((750 - $textWidth) / 2); //计算文字的水平位置
                    imagettftext($imageRes,$val['fontSize'],$val['angle'],$x,$val['top'],$fontColor,$val['fontPath'],$text);
                }else{
                    imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$text);
                }
            }
        }
        //生成图片
        if(!empty($filename)){
            $res = imagejpeg ($imageRes,$filename,90); //保存到本地
            imagedestroy($imageRes);
            if(!$res) return false;
            return $filename;
        }else{
            imagejpeg ($imageRes);//在浏览器上显示
            imagedestroy($imageRes);
        }
    }
}

/**
 * 生成订单号
 * @return string
 */
function get_order_sn()
{
    //当前时间+4位随机数
    return date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 时间日期格式化为多少天前
 * @param sting|intval $date_time
 * @param intval $type 1、'Y-m-d H:i:s' 2、时间戳
 * @return string
 */
function format_datetime($date_time,$type=1,$format=''){
    if($type == 1){
        $timestamp = strtotime($date_time);
    }elseif($type == 2){
        $timestamp = $date_time;
        $date_time = date('Y-m-d H:i:s',$date_time);
    }
    if(!empty($format)){
        return date($format,$timestamp);
    }
    $difference = time()-$timestamp;
    if($difference <= 180){
        return '刚刚';
    }elseif($difference <= 3600){
        return ceil($difference/60).'分钟前';
    }elseif($difference <= 86400){
        return ceil($difference/3600).'小时前';
    }elseif($difference <= 2592000){
        return ceil($difference/86400).'天前';
    }elseif($difference <= 31536000){
        return ceil($difference/2592000).'个月前';
    }else{
        return ceil($difference/31536000).'年前';
        //return $date_time;
    }
}

if(!function_exists('curl_get_contents')){
    // curl处理
    function curl_get_contents($url)
    {
        if(isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $agent = '';
        }

        if(isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        } else {
            $referer = '';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_REFERER,$referer);
        if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    if (!function_exists('thumb')) {
        //生成缩略图
        function thumb($img,$w,$h){
            $imgFile = \think\facade\Env::get('root_path').'public'.str_replace("\\", '/', $img);
            if(file_exists($imgFile)){
                $isImg = \think\facade\Env::get('root_path').'public'.str_replace("\\", '/', $img);
                $image = \think\Image::open($isImg);
                $image->thumb($w, $h,\think\Image::THUMB_FIXED)->save($isImg);
                $file=$img;
            }else{
                $file='uploadfile/nopic.gif';
            }
            return $file;
        }
    }


    if(!function_exists('isPost')){
        /**
         * 判断POST
         */
        function isPost() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                return true;
            } else {
                return false;
            }
        }
    }

    function hex2rgba($color, $opacity = false, $raw = false) {
        $default = 'rgb(0,0,0)';
        //Return default if no color provided
        if(empty($color))
            return $default;
        //Sanitize $color if "#" is provided
        if ($color[0] == '#' ) {
            $color = substr( $color, 1 );
        }
        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);

        if($raw){
            if($opacity){
                if(abs($opacity) > 1) $opacity = 1.0;
                array_push($rgb, $opacity);
            }
            $output = $rgb;
        }else{
            //Check if opacity is set(rgba or rgb)
            if($opacity){
                if(abs($opacity) > 1)
                    $opacity = 1.0;
                $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
            } else {
                // $output = 'rgb('.implode(",",$rgb).')';
                $output = implode(",",$rgb);
            }
        }

        //Return rgb(a) color string
        return $output;
    }
    if (!function_exists('createPoster')) {
        /**
         * 生成宣传海报
         * @param array  参数,包括图片和文字
         * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
         * @return [type] [description]
         */
        function createPoster($config = array(), $filename = ""){
            if(empty($filename)){
                header("content-type: image/png");
            }
            $imageDefault = array(
                "left"=>0,
                "top"=>0,
                "right"=>0,
                "bottom"=>0,
                "width"=>100,
                "height"=>100,
                "opacity"=>100
            );
            $textDefault = array(
                "text"=>'',
                "left"=>0,
                "top"=>0,
                "fontSize"=>32,
                "fontColor"=>'255,255,255',
                "angle"=>0,
            );
            // $background = @imagecreatefrompng($config['background']);
            $backgroundInfo = getimagesize($config['background']);
            if($backgroundInfo['mime'] == 'image/jpeg'){
                $background = @imagecreatefromjpeg($config['background']);
            }else if($backgroundInfo['mime'] == 'image/gif'){
                $background = @imagecreatefromgif($config['background']);
            }else if($backgroundInfo['mime'] == 'image/png'){
                $background = @imagecreatefrompng($config['background']);
            }
            $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2],false);
            $backgroundFun = $backgroundFun($config['background']);
            // $backgroundWidth = imagesx($background); // 图片宽度
            // $backgroundHeight = imagesy($background); // 图片高度
            $backgroundWidth = $backgroundInfo[0]; // 图片宽度
            $backgroundHeight = $backgroundInfo[1]; // 图片高度
            $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
            $color = imagecolorallocate($imageRes, 0, 0, 0);
            imagefill($imageRes, 0, 0, $color);
            imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
            // imagecopyresampled($imageRes,$background,0,0,0,0,$backgroundWidth,$backgroundHeight,$backgroundWidth,$backgroundHeight);
            if(!empty($config['image'])){
                foreach ($config['image'] as $key => $val) {
                    $val = array_merge($imageDefault,$val);
                    $info = getimagesize($val['url']);
                    $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
                    if($val['stream']){
                        //如果传的是字符串图像流
                        $info = getimagesizefromstring($val['url']);
                        $function = 'imagecreatefromstring';
                    }
                    $res = $function($val['url']);
                    $resWidth = $info[0];
                    $resHeight = $info[1];
                    //建立画板 ，缩放图片至指定尺寸
                    $canvas=imagecreatetruecolor($val['width'], $val['height']);
                    imagefill($canvas, 0, 0, $color);
                    //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                    imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                    $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                    $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                    //放置图像
                    if($val['center'] == '1'){

                        $x         = ceil((750 - $val['width']) / 2); //计算文字的水平位置

                        imagecopymerge($imageRes,$canvas, $x,$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);
                    }else{
                        imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);
                    }
                }
            }
            //处理文字
            if(!empty($config['text'])){
                foreach ($config['text'] as $key => $val) {
                    $val = array_merge($textDefault,$val);
                    list($R,$G,$B) = explode(',', $val['fontColor']);
                    $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                    $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                    $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
                    $text = autowrap($val['fontSize'],0,$val['fontPath'],$val['text'],710); // 自动转行
                    if($val['center'] == '1'){
                        $fontWidth = imagefontwidth($val['fontSize']);
                        $textWidth = $fontWidth * mb_strlen($val['text']);
                        $x         = ceil((750 - $textWidth) / 2); //计算文字的水平位置
                        imagettftext($imageRes,$val['fontSize'],$val['angle'],$x,$val['top'],$fontColor,$val['fontPath'],$text);
                    }else{
                        imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$text);
                    }
                }
            }
            //生成图片
            if(!empty($filename)){
                $res = imagejpeg ($imageRes,$filename,90); //保存到本地
                imagedestroy($imageRes);
                if(!$res) return false;
                return $filename;
            }else{
                imagejpeg ($imageRes);//在浏览器上显示
                imagedestroy($imageRes);
            }
        }
    }

    if (!function_exists('autowrap')) {
        function autowrap($fontsize, $angle, $fontface, $string, $width) {
            // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
            $content = "";
            $letter = array();
            // 将字符串拆分成一个个单字 保存到数组 letter 中
            for ($i=0;$i<mb_strlen($string);$i++) {
                $letter[] = mb_substr($string, $i, 1);
            }
            foreach ($letter as $l) {
                $teststr = $content." ".$l;
                $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
                // 判断拼接后的字符串是否超过预设的宽度
                if (($testbox[2] > $width) && ($content !== "")) {
                    $content .= "\n";
                }
                $content .= $l;
            }
            return $content;
        }
    }
    function rands($len)
    {
        $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string='1';
        for(;$len>=1;$len--)
        {
            $position=rand()%strlen($chars);
            $position2=rand()%strlen($string);
            $string=substr_replace($string,substr($chars,$position,1),$position2,0);
        }
        return $string;
    }

    function getRequest($url){
        // var_dump($url);die;
        $ch = curl_init();
        //设置请求的路径
        curl_setopt($ch,CURLOPT_URL,$url);
        //不需要验证ssl证书
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置获取的信息以文件流的形式返回,不在页面中输出任何结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $str = curl_exec($ch);
        curl_close($ch);

        return $str;
    }
}