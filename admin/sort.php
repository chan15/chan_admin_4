<?php

include dirname(__DIR__) . '/bootstrap.php';

$app->checkSourceUrl();

$tableField = @$_POST['tableField'];
$idSerial   = @$_POST['idSerial'];
$sortSerial = @$_POST['sortSerial'];

if ($idSerial !== '' && $sortSerial !== '') {
    $db = new Database;
    $ids   = explode(',', $idSerial);
    $sorts = explode(',', $sortSerial);

    if (count($ids) != count($sorts)) {
        die('參數錯誤');
    }

    // start update sort
    $db->table = $tableField;

    foreach ($ids as $index => $id) {
        $db->pk = 'id';
        $db->pkValue = $id;
        $db->addField('sort', $sorts[$index], 'int');
        $db->save();
    }
}

