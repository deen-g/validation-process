<?php
require "../bootstrap.php";

use Src\Controllers\ContactController;
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
//header("Access-Control-Allow-Headers: Accept,Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//print_r($_SERVER['QUERY_STRING']);
//$query = explode('?q=', $_SERVER['REQUEST_URI'])[1];
$uri = explode('/', $uri);

if ($uri[1] !== 'api') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the user id is, of course, optional and must be a number:
$contactId = null;

if (isset($_SERVER['QUERY_STRING'])) {
    $query = urldecode($_SERVER['QUERY_STRING']);
    $query = str_replace('q=', '', $query);
    $contactId = $query;
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        //using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}
$requestMethod = $_SERVER["REQUEST_METHOD"];
// pass the request method and user ID to the PersonController and process the HTTP request:
$controller = new ContactController($requestMethod, $contactId);
$controller->processRequest();
