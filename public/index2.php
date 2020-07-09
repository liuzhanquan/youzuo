<?php
header("Content-Type: text/html;charset=utf-8");


$destination = './upload/image/';
$file        = $_FILES['file']; // 获取上传的图片
$filename    = $file['name'];

$test   = move_uploaded_file($file['tmp_name'], $destination . iconv("UTF-8", "gb2312", $filename));

if ($test) {
	echo 'chenggong';
} else {
    echo '上传失败' . '<br>';
}

while ($row = $result->fetch_assoc()) {
    echo "<img src=" . $destination . $row['path'] . ">";
}
