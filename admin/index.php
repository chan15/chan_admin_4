<?php

include dirname(__DIR__) . '/bootstrap.php';

if (isset($_POST['login']) === true) {
    $app->checkSourceUrl();

    $rules = array(
        'username' => 'required',
        'password' => 'required',
    );

    $validate = new Validation;

    if ($validate->check($_POST, $rules) === false) {
        die($validate->first());
    }

    $db = new Database;
    $username = $_POST['username'];
    $password = $_POST['password'];
    $status = '';
    $message = '';

    $sql = "SELECT * FROM `admins` WHERE `username` = ? AND `password` = ?";
    $db->addValue($username);
    $db->addValue($password);
    $row = $db->myOneRow($sql);

    if ($row === null) {
        $status = 'fail';
        $message = '查無資料';
    } else {
        $status = 'ok';
        $_SESSION['admin'] = true;
        $_SESSION['adminId'] = $row['id'];
    }

    echo json_encode(array('status' => $status, 'message' => $message));
    exit;
}

$smarty->display('admin/index.tpl');
