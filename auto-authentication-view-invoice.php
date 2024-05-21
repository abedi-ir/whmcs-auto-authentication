<?php

use WHMCS\Config\Setting;

define('CLIENTAREA', true);
require_once __DIR__.'/init.php';
$autoAuthLinksMaxLife = 0;
require __DIR__.'/configuration.php';

$url = Setting::getValue('SystemURL');

if (
    !isset($_GET['email']) or
    !isset($_GET['timestamp']) or
    !isset($_GET['signature']) or
    !isset($_GET['invoice_id']) or
    !is_numeric($_GET['invoice_id']) or
    $_GET['invoice_id'] <= 0
) {
    header("Location: {$url}/login");
    exit();
}

$result = select_query('tblclients', 'id,auto_auth_token', ['email' => $_GET['email'], 'status' => ['sqltype' => 'NEQ', 'value' => 'Closed']]);
$data = mysql_fetch_array($result);

if (
    !$data or
    $_GET['timestamp'] < time() - $autoAuthLinksMaxLife or
    time() < $timestamp or
    !password_verify($_GET['email'].$_GET['timestamp'].$data['auto_auth_token'], $_GET['signature'])
) {
    header("Location: {$url}/login");
    exit();
}

$response = localAPI('CreateSsoToken', [
    'client_id' => $data['id'],
    'destination' => 'sso:custom_redirect',
    'sso_redirect_path' => '/viewinvoice.php?id='.$_GET['invoice_id'],
]);

if (isset($response['result']) and 'error' == $response['result']) {
    throw new Exception($response['message'] ?? 'Internal Error');
} elseif (!isset($response['redirect_url'])) {
    throw new Exception('Unknown Error');
}

header('Location: '.$response['redirect_url']);
