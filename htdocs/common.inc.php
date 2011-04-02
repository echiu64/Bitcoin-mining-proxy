<?php

require_once('config.inc.php');

function db_connect() {
    global $BTC_PROXY;

    return new PDO($BTC_PROXY['db_connection_string'], $BTC_PROXY['db_user'], $BTC_PROXY['db_password']);
}

function auth_fail() {
    header('WWW-Authenticate: Basic realm="bitcoin-mining-proxy"');
    header('HTTP/1.0 401 Unauthorized');
    header('Content-Type: text/plain');

    echo "Sorry, I don't know you.";

    exit;
}

function request_fail() {
    header('HTTP/1.0 400 Bad Request');
    header('Content-Type: text/plain');

    echo "Sorry, I don't understand what you just said.";

    exit;
}

function json_error($message, $id) {
    header('Content-Type: application/json-rpc');

    $object = new stdClass();
    $object->error = $message;
    $object->result = null;
    $object->id = $id;

    echo json_encode($object);

    exit;
}

function json_success($result, $id) {
    header('Content-Type: application/json-rpc');

    $object = new stdClass();
    $object->error = null;
    $object->result = $result;
    $object->id = $id;

    echo json_encode($object);

    exit;
}

function place_json_call($object, $url, $username = '', $password = '') {
    $authHeader = "";

    if (strlen($username) != 0) {
        $authHeader = "Authorization: Basic " . base64_encode($username . ':' . $password) . "\r\n";
    }

    $context = stream_context_create(array(
        'http'  => array(
            'method'    => 'POST',
            'header'    => "Content-Type: application/json-rpc\r\n$authHeader",
            'content'   => json_encode($object)
        )
    ));

    return json_decode(file_get_contents($url, false, $context));
}

?>
