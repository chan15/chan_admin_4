<?php

error_reporting(E_ALL);
session_start();
header('Content-type: text/html; charset=utf-8');
include 'const.php';
include 'vendor/autoload.php';

$smarty = new Smarty;
$path = __DIR__;
$smarty->template_dir = $path . '/templates/';
$smarty->compile_dir  = $path . '/templates_c/';
$smarty->config_dir   = $path . '/configs/';
$smarty->cache_dir    = $path . '/cache/';

$aliases = array(
    'App'        => 'Chan\App',
    'Database'   => 'Chan\Database',
    'File'       => 'Chan\File',
    'Image'      => 'Chan\Image',
    'Migration'  => 'Chan\Migration',
    'Validation' => 'Chan\Validation',
);

foreach ($aliases as $key => $value) {
    class_alias($value, $key);
}

$app = new App;
