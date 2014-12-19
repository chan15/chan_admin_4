<?php

include dirname(__DIR__) . '/bootstrap.php';

$loginAuth = 0;
include 'login-policy.php';
$pageItemName = '';
$subItemName = '';
$tableName = '';
$fileName = '';
include 'nav.php';
include 'options.php';
$options = include 'options.php';
$smarty->assign('options', $options);
$limit = (isset($_GET['limit']) === true) ? $_GET['limit'] : 20;
$smarty->assign('limit', $limit);
$db = new Database;

$name = ($app->hasValue($_GET['name']) === true) ? '%' . $_GET['name'] . '%' : '%%%';
$on = ($app->hasValue($_GET['on']) === true) ? '%' . $_GET['on'] . '%' : '%%%';

$sql = sprintf("SELECT *
    FROM `%s`
    WHERE `name` LIKE ? AND `on` LIKE ?
    ORDER BY `id` DESC",
    $tableName);
$db->addValue($name);
$db->addValue($on);
$row = $db->myRowList($sql, $limit);

$smarty->assign('tableField', $tableName); // table field
$smarty->assign('total', $db->totalRecordCount); // total record
$smarty->assign('pageNow', ($db->page + 1)); // current page
$smarty->assign('pageTotal', ($db->totalPages + 1)); // total page
$smarty->assign('bootstrapPager', $db->bootstrapPager()); // pager
$smarty->assign('datas', $row);
$smarty->assign('modifyPage', $fileName . '-modify.php');
$smarty->display('admin/' . $fileName . '-list.tpl');
