<?php
/**
 * Created by Another user.
 * User: ???
 * Date: 12/13/2017
 * Time: 3:27 PM
 */
require_once '../curl.php';
function getFs_csrf($page)
{
    preg_match('#<meta name="csrf-token" content="(.*?)">#', $page, $get_fs_csrf);
    $fs_csrf = $get_fs_csrf[1];
    return $fs_csrf;
}

function getIdFshareUrl($url)
{
    preg_match('/https:\/\/www\.fshare\.vn\/file\/(.[a-zA-Z0-9]+)?/', $url, $idFile);
    $id = $idFile[1];
    return $id;
}

function login($fs_csrf)
{
    $acc_user = 'tmpfshare9@gmail.com';
    global $curl;
    $curl      = new cURL();
    $acc_pass  = 'Vip.taimienphi.vn9';
    $login_url = "https://www.fshare.vn/site/login";
    $dataLogin = "_csrf-app=" . urlencode($fs_csrf) . "&LoginForm%5Bemail%5D=" . urlencode($acc_user) . "&LoginForm%5Bpassword%5D=" . urlencode($acc_pass) . "&LoginForm%5BrememberMe%5D=0";
    $html      = $curl->post($login_url, $dataLogin);
    ;
}

function getLink($fs_csrf, $fslink)
{
    global $curl;
    $idURL           = getIdFshareUrl($fslink);
    $dataDownload    = '_csrf-app=' . urlencode($fs_csrf) . '&linkcode=' . $idURL . '&withFcode5=0&fcode5=';
    $getLinkDownload = $curl->post('https://www.fshare.vn/download/get', $dataDownload);
    if (strpos($getLinkDownload, '{"url"')) {
        $linkDownload    = substr($getLinkDownload, strpos($getLinkDownload, '{'));
        $arrLinkDownload = json_decode($linkDownload);
        $url             = $arrLinkDownload->url;
        $nameFile        = substr($url, strrpos($url, '/') + 1);
        $nameFile        = urldecode($nameFile);
        $result          = array(
            'url' => $url,
            'name' => $nameFile
        );
        return $result;
    } else {
        return false;
    }
}

if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) {
    echo 'Bác là hacker ah? Đừng làm thế mà! >.<';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['g-recaptcha-response'])) {
        
        // Build POST request:
        $recaptcha_url      = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret   = '6LcSJ5MUAAAAABsFNkkgsLI0gjzhMEFE99fCGMQ1';
        $recaptcha_response = $_POST['g-recaptcha-response'];
        
        // Make and decode POST request:
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $recaptcha = json_decode($recaptcha);
        
        // Take action based on the score returned:
        if ($recaptcha->success) {
            $fslink  = $_POST['link'];
            $curl    = new cURL();
            $page    = $curl->get($fslink);
            $fs_csrf = getFs_csrf($page);
            login($fs_csrf);
            $file = getlink($fs_csrf, $fslink);
            echo $file['url'];
        } else {
            echo 'Sai captcha rồi! Bác là robot hả?';
        }
    } else {
        echo 'Nhập captcha đi bác! Không có chơi vậy được đâu nha';
    }
    
}

?>