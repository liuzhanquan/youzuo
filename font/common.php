<?php
// 应用公共文件

use \app\common\model\AdminLog;
use \app\common\model\StaffLog;
use \app\backman\model\Agent;
use \think\DB;
use think\facade\App;
use app\common\services\QrcodeServer;


function AdminLog($uid,$action){
    $logInfo = [
        'user_id'=>$uid,
        'desc'=>$action,
        'action'=>request()->path(),
        'timestamp'=>date('Y-m-d H:i:s'),
    ];
    return AdminLog::insertGetId($logInfo);
}
function StaffLog($uid,$action){
    $logInfo = [
        'user_id'=>$uid,
        'desc'=>$action,
        'action'=>request()->path(),
        'timestamp'=>date('Y-m-d H:i:s'),
    ];
    return StaffLog::insertGetId($logInfo);
}

/**
 * 计算两个日期之间的月数
 * @param $start_m
 * @param $end_m
 */
function month_numbers($start_m,$end_m){ //日期格式为2018-8-28
    $date1 = explode('-',$start_m);
    $date2 = explode('-',$end_m);

    if($date1[1]<$date2[1]){ //判断月份大小，进行相应加或减
        $month_number= abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
    }else{
        $month_number= abs($date1[0] - $date2[0]) * 12 - abs($date1[1] - $date2[1]);
    }
    return $month_number;
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
                    $Rootpath = env('root_path') . 'public' .str_replace("\\", '/','uploads/');
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
    return substr(date('YmdHis'),2).substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 5);
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


    if(!function_exists('powerStatus')){
        /**
         * 判断是否在数组里面
         */
        function powerStatus($id = 0,$arr = []) {
            if( $id && $arr ){
                return in_array( $id, $arr );
            }
            return false;
        }
    }

    if(!function_exists('tablePreg')){
        /**
         * 表单正则
         */
        function tablePreg($table,$must) {
            if( !empty($table) ){
                $data = [];
                $num = 0;
                preg_match_all('/<label class="col\-sm\-2 control\-label" type\="(.*?)" name\="(.*?)">(.*?)<\/label>/',html_entity_decode($table),$title);
                preg_match_all('/<div class="col-sm-7">(.*?)<\/div>/',html_entity_decode($table),$inputlist);
                
                foreach( $title[1] as $key=>$item ){
                    $narr = [];
                    $narr['type'] = $item;
                    if( empty($title[2][$key]) ){
                        $narr['name'] = $item.'_'.mt_rand(1,1000000);
                    }else{
                        $narr['name'] = $title[2][$key];
                    }
                    
                    $narr['title'] = $title[3][$key];
                    if( $item != 'datetime' && $item != 'file' ){
                        $res = tabletype($item,$inputlist[1][$num]);
                        $narr =  array_merge($narr, $res );

                    }

                    $narr['must'] =  in_array($key, $must);

                    if ( $item != 'file' ) {
                        $num++;
                    }
                    
                    $data[] = $narr;

                }
            }
            
            return $data;
        }
    }

    if(!function_exists('tabletype')){
        /**
         * 表格字段内容返回  文本框提示  多选框内容
         */
        function tabletype($type,$text) {
            switch( $type ){
                case 'text':
                    preg_match_all('/placeholder="(.*?)"/',html_entity_decode($text),$list);
                    $res['placeholder'][] = $list[1][0];
                    break;
                case 'www':
                    preg_match_all('/placeholder="(.*?)"/',html_entity_decode($text),$list);
                    $res['placeholder'][] = $list[1][0];
                    break;
                    
                case 'select':
                    preg_match_all('/<option>(.*?)<\/option>/',html_entity_decode($text),$list);
                    foreach( $list[1] as $item ){
                        $res['placeholder'][] = $item;
                    }
                    break;

                case 'radio':
                    preg_match_all('/value="(.*?)"/',html_entity_decode($text),$list);
                    foreach( $list[1] as $item ){
                        $res['placeholder'][] = $item;
                    }
                    break;
                
                case 'checkbox':
                    preg_match_all('/name=".*?">(.*?)<\/label>/',html_entity_decode($text),$list);
                    
                    foreach( $list[1] as $item ){
                        $res['placeholder'][] = $item;
                    }
                    break;

                case 'textarea':
                    preg_match_all('/placeholder="(.*?)"/',html_entity_decode($text),$list);
                    $res['placeholder'][] = $list[1][0];
                    break;

                default:
                    $res = [];
                    break;
            }
            return $res;
        }
    }


    if(!function_exists('return_ajax')){
        /**
         * json返回方法
         * @param $message
         * @param $status
         * @param $data
         * @return array
         */
        function return_ajax( $status = 1, $message, $data=[] ){
            exit(json_encode(['status'=>$status,'message'=>$message,'data'=>$data]));
        }
    }

    if(!function_exists('userencode')){
        /**
         * 登录用户信息加密
         * @param $obj
         * @return array
         */
        function userencode($obj){
            $obj = json_encode($obj);
            return authcode($obj);
        }
    }

    if(!function_exists('authcode')){
        /**
         * discuz!金典的加密函数
         * @param string $string 明文 或 密文
         * @param string $operation DECODE表示解密,其它表示加密
         * @param string $key 密匙
         * @param int $expiry 密文有效期
         */
        function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
            // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
            $ckey_length = 4;
    
            // 密匙
            $key = md5($key ? $key : 'nLywa&123KlA+0*'); // AUTH_KEY 项目配置的密钥
    
            // 密匙a会参与加解密
            $keya = md5(substr($key, 0, 16));
            // 密匙b会用来做数据完整性验证
            $keyb = md5(substr($key, 16, 16));
            // 密匙c用于变化生成的密文
            $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
            // 参与运算的密匙
            $cryptkey = $keya.md5($keya.$keyc);
            $key_length = strlen($cryptkey);
            // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性
            // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
            $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
            $string_length = strlen($string);
            $result = '';
            $box = range(0, 255);
            $rndkey = array();
            // 产生密匙簿
            for($i = 0; $i <= 255; $i++) {
                $rndkey[$i] = ord($cryptkey[$i % $key_length]);
            }
            // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
            for($j = $i = 0; $i < 256; $i++) {
                $j = ($j + $box[$i] + $rndkey[$i]) % 256;
                $tmp = $box[$i];
                $box[$i] = $box[$j];
                $box[$j] = $tmp;
            }
            // 核心加解密部分
            for($a = $j = $i = 0; $i < $string_length; $i++) {
                $a = ($a + 1) % 256;
                $j = ($j + $box[$a]) % 256;
                $tmp = $box[$a];
                $box[$a] = $box[$j];
                $box[$j] = $tmp;
                // 从密匙簿得出密匙进行异或，再转成字符
                $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
            }
    
            if($operation == 'DECODE') {
                // substr($result, 0, 10) == 0 验证数据有效性
                // substr($result, 0, 10) - time() > 0 验证数据有效性
                // substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性
                // 验证数据有效性，请看未加密明文的格式
                if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                    return substr($result, 26);
                } else {
                    return '';
                }
            } else {
                // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
                // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
    
                return $keyc.str_replace('=', '', base64_encode($result));
            }
        }
    }


    if(!function_exists('userdecode')){
        /**
         * 登录用户信息解密
         * @param $obj
         * @return array
         */
        function userdecode($obj){
            $obj = str_replace(" ","+",$obj);
            return json_decode(authcode($obj,'DECODE'),true);
        }
    }

    if(!function_exists('phoneNum')){
        /**
        * 验证手机号是否正确
        * @param int $mobile
        */
        function phoneNum($mobile) {
            if (!is_numeric($mobile)) {
                return false;
            }
            return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
        }
    }

    if(!function_exists('json_html_decode')){
        /**
        * html转义字符串变数组
        * @param int $mobile
        */
        function json_html_decode($str) {
            return empty($str) ? '' : json_decode( html_entity_decode($str), true );
        }
    }

    if(!function_exists('photo_addpath')){
        /**
         * 图片路径添加网址
         * @param $photo
         * @return array
         */
        function photo_addpath($photo){
            if(!empty($photo)){
                $result = config('PHOTOPATH').$photo;
            }else{
                $result='';
            }
            return $result;
        }
    }

    if(!function_exists('photo_arr_path')){
        /**
         * 图片数组添加网页路径
         * @param $photo   图片数组
         * @param $info    分隔符号 
         * @return array
         */
        function photo_arr_path($photo){
            if(!empty($photo)){
                foreach( $photo as $key=>$item ){
                    $result[$key] = photo_addpath($item);
                }
                return $result;
            }
        }
    }

    if(!function_exists('contentphotopath')){
        /**
         * 文章内容图片路径添加网址
         * @param $content
         * @return array
         */
        function contentphotopath($content){
            $content = preg_replace("/(src=\")(.*?)(\"\/)/",'$1'.config('PHOTOPATH').'$2$3',$content);
            //$content = preg_replace("/(src=\&quot\;)(.*?)(\&quot\;)/",'$1'.config('PHOTOPATH').'$2$3',$content);
            $content = htmlspecialchars_decode($content);
            return $content;
        }
    }

    function create_qrcode($url,$order_sn,$time = '')
    {
        
        $config = [
            'title'         => true,
            // 'title_content' => $order_sn,
            'logo'          => true,
            'logo_url'      => App::getRootPath().'/public'.config('logopath'),
            'logo_size'     => 80,
        ];
        if( $time == '' ){
            $time = date('Ymd');
        }else{
            $time = date('Ymd',strtotime($time));
        }
        // 直接输出
        // $qr_url = 'http://www.baidu.com?id=' . rand(1000, 9999);

        // $qr_code = new QrcodeServer($config);
        // $qr_img = $qr_code->createServer($qr_url);
        // echo $qr_img;
        
        // 写入文件
        $path = config('qrcode_savepath');
        $nPath = str_replace("\\", '/', $path.'/'.$time.'/'.$order_sn);
        $file_name = \think\facade\Env::get('root_path').'public'.$nPath;  // 定义保存目录
        $result = [];
       
        foreach( config('qrcode_status') as $key=>$item ){
            // $serverUrl = $_SERVER['SERVER_NAME'];
            
            if(!file_exists($file_name.'/'.$order_sn.$item.'.png')){
                $config['file_name'] = $file_name;
                $config['generate']  = 'writefile';
                $config['title_content'] = $order_sn.config('qrcode_text')[$key];
                $qr_code = new QrcodeServer($config);
                
                $rs = $qr_code->createServer($url.config('qrcode_value')[$key]);
                
                $img = explode('/',$rs['data']['url']);
                if( !is_array($img) ){
                    $img = explode('\\',$rs['data']['url']);
                }
                qrcode_thumb( $img[count($img)-1], $nPath,$order_sn.$item );
                

                $res['url'] = photo_addpath($nPath.'/'.$order_sn.$item.'.png');
                $res['path'] = $file_name.'/'.$order_sn.'.png';
            }else{
                $res['url'] = photo_addpath($nPath.'/'.$order_sn.$item.'.png');
                
                $res['path'] = $file_name.'/'.$order_sn.$item.'.png';
            }
            $res['type'] = config('qrcode_text')[$key];
            $result[] = $res;
        }
        
        return $result; 
    }

    if (!function_exists('qrcode_thumb')) {
        //生成缩略图
        function qrcode_thumb($img,$path,$name = '', $w = 400,$h = 400){
            
            // $imgFile = \think\facade\Env::get('root_path').'public'.str_replace("\\", '/', $img);
            $imgFile = \think\facade\Env::get('root_path').'public'.str_replace("\\", '/', $path.'/'.$img);
            
            if(file_exists($imgFile)){
                $isImg = $imgFile;
                $image = \think\Image::open($isImg);
                if($name){
                    $newImgName = \think\facade\Env::get('root_path').'public'.str_replace("\\", '/', $path.'/'.$name.'.png');
                }else{
                    $newImgName = $imgFile; 
                }
                $res = $image->thumb($w, $h,\think\Image::THUMB_FIXED)->save($newImgName);

                $file= $newImgName;
                unlink($imgFile);
            }else{
                $file='uploadfile/nopic.gif';
            }
            return $file;
        }
    }



    function rawPost(){
        $res = file_get_contents('php://input');
        $res = json_decode($res,true);
        return $res;
    }


    if(!function_exists('orderStatusCheck')){
        /**
         * 查询订单状态
         * @param $oid   检测单id
         * @return array
         */
        function orderStatusCheck($oid,$modifyOrder = false){
            $info = DB::name('order')->where('id',$oid)->field('id,gsid,status')->find();
            $gsid = json_html_decode($info['gsid']);
            $status = 0;
            $num = 0;
            $text = '未开始';
            foreach( $gsid as $item ){
                $count = DB::name('order_s')->where(['oid'=>$oid,'dsid'=>$item])->count();
                if( $count ){
                    $num++;
                    $status = 1;
                    $text = '进行中';
                }
            }
            if( count($gsid) == $num ){
                $status = 2;
                $text = '已完成';
            }
            $result['status'] = $status;
            $result['text'] = $text;
            if( $modifyOrder && $status > 0 ){
                DB::name('order')->where('id',$oid)->update(['status'=>1]);
            }
            return $result;
        }
    }

    if(!function_exists('categorySelSon')){
        /**
         * 获取分类里的所有子类
         * @param $photo   图片数组
         * @param $info    分隔符号 
         * @return array
         */
        function categorySelSon($table,$id,$str = false){
            $info = DB::name("$table")->where('id',$id)->field('parent_id')->find();
            $narr = [];
            
            if( $info['parent_id'] == 0 ){
                $arr = DB::name("$table")->where('parent_id',$id)->field('id')->select();
                if($arr){
                    foreach( $arr as $item ){
                        $narr[] = $item['id'];
                    }
                }
            }
            $narr[] = $id;
            
            if( $str ){
                $res = implode(',',$narr);
            }else{
                $res = $narr;
            }
            
            return $res;
        }
    }


    if(!function_exists('unlinkPhoto')){
        /**
         * 删除图片
         * @param $path    图片路径
         * @return array
         */
        function unlinkPhoto($path){
            $npath = [];
            if( !is_array($path) ){
                
                $npath[] = $path; 

            }else{
                $npath = $path;
            }
            
            $root_path = \think\facade\Env::get('root_path').'public';
            
            foreach( $npath as $item ){
                if( file_exists($root_path.$item) ){
                     unlink($root_path.$item);
                }
            }

        }
    }

    if(!function_exists('spec_data_select')){
        /**
         * 子定义表格绑定数据字典查询字典信息
         * @param $data    图片路径
         * @return array
         */
        function spec_data_select($data,$d_son_sn){
            $nlist = [];
            $spec_data = DB::name('spec_data')->where('d_son_sn',$d_son_sn)->select();
            
            foreach( $data as $key=>$item ){
                $data[$key]['datacate'] = 0;
                $data[$key]['cid'] = 0;
                $data[$key]['types'] = 0;

                if( $item['type'] == 'radio' || $item['type'] == 'checkbox' ){
                    foreach($spec_data as $i){
                        if( $item['name'] == $i['class_name'] ){
                            $data[$key]['datacate'] = $i['did'];
                            $data[$key]['types'] = 'datacate';
                            $res = DB::name('data_category')->where('parent_id',$i['did'])->order('sort asc')->field('name')->select();
                            
                            foreach( $res as $k=>$r ){
                                $data[$key]['placeholder'][$k] = $r['name'];
                            }
                        }
                    }
                }
                
                if( $item['type'] == 'select' ){
                    foreach($spec_data as $i){
                        if( $item['name'] == $i['class_name'] ){
                            $data[$key]['datacate'] = $i['did'];
                            $data[$key]['types'] = $i['table_name'];
                            $res = DB::name($i['table_name'])->where('parent_id',$i['did'])->order('sort asc')->field('name')->select();
                            
                            foreach( $res as $k=>$r ){
                                $data[$key]['placeholder'][$k] = $r['name'];
                            }
                        }
                    }
                }



            }

            
            return $data;


        }
    }








}