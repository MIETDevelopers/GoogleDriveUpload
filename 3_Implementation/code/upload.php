<?php
session_start();
$url_array = explode('?', 'http://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);                /*explode(delimiter, string_name, limit) */
$url = $url_array[0];

require_once 'google-api-php-client/src/Google_Client.php';                                         /*google_client is the class in the API*/
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';                           /*google_driveservice is the class in the API*/
$client = new Google_Client();                                                                      /*instanciation of google_client class*/
$client->setClientId('enter your client id');
$client->setClientSecret('enter your client secret');
$client->setRedirectUri($url);                                                                      /*OAuth2.0 send responses to*/
$client->setScopes(array('https://www.googleapis.com/auth/drive'));                                 /*scope that view and manage the files in Google Drive*/
if (isset($_GET['code'])) {
    $_SESSION['accessToken'] = $client->authenticate($_GET['code']);
    header('location:'.$url);exit;
} elseif (!isset($_SESSION['accessToken'])) {
    $client->authenticate();
}
$files= array();
$dir = dir('files');
while ($file = $dir->read()) {
    if ($file != '.' && $file != '..') {
        $files[] = $file;
    }
}
$dir->close();
if (!empty($_POST)) {
    $client->setAccessToken($_SESSION['accessToken']);
    $service = new Google_DriveService($client);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file = new Google_DriveFile();
    foreach ($files as $file_name) {
        $file_path = 'files/'.$file_name;
        $mime_type = finfo_file($finfo, $file_path);
        $file->setTitle($file_name);
        $file->setDescription('This is a '.$mime_type.' document');
        $file->setMimeType($mime_type);
        $service->files->insert(
            $file,
            array(
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type
            )
        );
    }
    finfo_close($finfo);
    header('location:'.$url);exit;
}
include 'index.php';
