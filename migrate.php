<?php

include 'bootstrap.php';

$migration = new Migration;
$db = new Database('default');

$migration->checkMigrations();
$now = date('Y-m-d H:i:s');

$migration->migrationName = 'create_admins_table';
if ($migration->checkMigrations() === null) {
    $migration->table = 'admins';
    $migration->increments('id');
    $migration->string('username');
    $migration->string('password');
    $migration->boolean('on');
    $migration->timestamp = true;
    $migration->migrate();
}

$migration->migrationName = 'insert_admin_default';
if ($migration->checkMigrations() === null) {
    $db->table = 'admins';
    $db->addField('username', 'admin');
    $db->addField('password', 1234);
    $db->addField('on', 1, 'int');
    $db->addField('created_at', $now);

    if ($db->save() === true) {
        echo $migration->migrationName . ' done<br>';
    }

    $migration->migrate();
}

echo 'all migration finished';
