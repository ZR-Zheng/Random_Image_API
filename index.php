<?php
// 设置允许跨域的来源，替换为你的可信域名
header('Access-Control-Allow-Origin: *');
// 仅允许 GET 和 POST 方法
header('Access-Control-Allow-Methods: GET, POST');
// 限制允许的请求头，这里只允许 Content-Type头
header('Access-Control-Allow-Headers: Content-Type,*');
// 增加 X-Content-Type-Options HTTP 头
header('X-Content-Type-Options: nosniff');

/**
 * 判断用户是否通过移动设备访问
 *
 * @return bool
 */
function isMobile(){
    $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
    $useragent_commentsblock = preg_match('|\(.*?\)|', $useragent, $matches) > 0 ? $matches[0] : "";

    /**
     * 检查子字符串是否存在于文本中
     *
     * @param array $substrs
     * @param string $text
     * @return bool
     */
    function CheckSubstrs($substrs, $text){
        foreach($substrs as $substr){
            if(false !== strpos($text, $substr)){
                return true;
            }
        }
        return false;
    }

    $mobile_os_list = array(
        'Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l',
        'armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone',
        'Go.Web','Palm','iPAQ'
    );
    $mobile_token_list = array(
        'Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320',
        '320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson',
        'Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront',
        'HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod'
    );

    return CheckSubstrs($mobile_os_list, $useragent_commentsblock) ||
        CheckSubstrs($mobile_token_list, $useragent);
}

// 获取并处理 'type' 参数
if (isset($_GET['type'])) {
    $type = strtolower(trim($_GET['type']));
} else {
    // 如果未指定 'type'，则自动检测设备类型
    $type = isMobile() ? 'mobile' : 'pc';
}

// 定义允许的类型及对应的文件
$allowed_types = [
    'pc' => 'pc_image_urls.txt',
    'mobile' => 'mobile_image_urls.txt'
];

// 检查 'type' 参数是否合法
if (array_key_exists($type, $allowed_types)) {
    $file_path = $allowed_types[$type];
} else {
    // 如果 'type' 参数不合法，可以选择返回错误或使用默认文件
    // 这里选择返回错误信息
    header('Content-Type: application/json');
    http_response_code(400); // 400 Bad Request
    echo json_encode(['error' => 'Invalid type parameter. Allowed values are "pc" and "mobile".']);
    exit;
}

// 检查文件是否存在并可读
if (!file_exists($file_path) || !is_readable($file_path)) {
    header('Content-Type: application/json');
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['error' => 'Unable to read the image URLs file.']);
    exit;
}

// 读取图片 URL 列表
$imageUrls = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// 检查是否成功读取到图片 URL
if (!$imageUrls || count($imageUrls) === 0) {
    header('Content-Type: application/json');
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['error' => 'No image URLs found in the file.']);
    exit;
}

// 随机选择一个图片 URL
$randomImageUrl = $imageUrls[array_rand($imageUrls)];

// 获取并处理 'm' 参数
$mode = isset($_GET['m']) ? str_replace("//", "/", $_GET['m']) : '';

// 根据模式处理响应
if (empty($mode)) {
    // 使用代理方式获取并返回图片
    proxyImage($randomImageUrl);
} elseif ($mode === 'json') {
    // 如果请求为 JSON 格式，返回图片 URL 信息
    header('Content-Type: application/json');
    echo json_encode(['image_url' => $randomImageUrl, 'mode' => 'json', 'type' => $type]);
} else {
    // 如果模式为其他情况，则返回图片
    proxyImage($randomImageUrl);
}
exit;

/**
 * 使用代理方式获取图片并返回给客户端
 *
 * @param string $imageUrl
 * @return void
 */
function proxyImage($imageUrl) {
    // 使用 cURL 获取图片内容
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 获取数据而不是输出
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // 如果有重定向，自动跟随

    // 获取图片数据
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // 获取响应状态码
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE); // 获取内容类型
    curl_close($ch);

    // 检查响应状态码是否为 200（成功）且获取到图片数据
    if ($httpCode === 200 && $imageData !== false) {
        // 设置正确的图片 MIME 类型（根据图片类型自动判断）
        header('Content-Type: ' . $contentType);
        echo $imageData;  // 输出图片数据
    } else {
        // 如果请求图片失败，返回错误信息
        header('Content-Type: application/json');
        http_response_code(500); // 500 Internal Server Error
        echo json_encode(['error' => 'Failed to fetch the image.']);
    }
}

