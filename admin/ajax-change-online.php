<?php

include dirname(__DIR__) . '/bootstrap.php';

$app->checkSourceUrl();

$db = new Database;
$db->table = @$_POST['tableField'];
$db->pk = 'id';
$db->pkValue= @$_POST['id'];
$db->addField('on', @$_POST['action'], 'int');
$db->save();
