<?php

include dirname(__DIR__) . '/bootstrap.php';
include 'login-policy.php';

$pageItemName = ''; // nav name
$subItemName = ''; // sidebar name
include 'nav.php';

$smarty->display('admin/welcome.tpl');
