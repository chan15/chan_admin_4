<?php

include dirname(__DIR__) . '/bootstrap.php';

$loginAuth = 0;
include 'login-policy.php';
$pageItemName = '';
$subItemName = '';
$tableName = '';
$fileName = '';
include 'nav.php';
$options = include 'options.php';
$smarty->assign('options', $options);
$path = 'uploads/' . $tableName . '/';
$smarty->assign('path', $path);
$fileFields = array();
$isAdd = (isset($_POST['id']) === true) ? false : true;
Image::$imageUploadRatio = 600;
$db = new Database;

// Ajax modify
if (isset($_POST['modify']) === true) {
	$app->checkSourceUrl();
	$db->table = $tableName;

    $rules = array(
        'name' => 'required',
        'on'   => 'required',
    );

    if ($isAdd === true) {
        $rules['image'] = 'file';
    }

    $translates = array(
        'name'  => '名稱',
        'on'    => '上架',
        'image' => '圖片',
    );

    $validate = new Validation;

	if ($validate->check($_POST, $rules, $translates) === false) {
        die($validate->first());
	} else {
        if (count($fileFields) > 0) {
            foreach ($fileFields as $field => $param) {
                if ($_FILES[$param]['name'] !== '') {
                    $upload = Image::upload(dirname(__DIR__) . '/' . $path, $param);

                    if ($upload['err'] !== '') {
                        die($upload['err']);
                    } else {
                        $db->addField($field, $upload['img']);
                    }
                }
            }
        }

		$db->addField('name', $_POST['name']);
		$db->addField('on', $_POST['on'], 'int');

        if (true === $isAdd) {
            $db->addField('sort', $db->maxSort(), 'int');
            $db->addField('created_at', $app->retNow(), 'date');
        } else {
            $db->pk = 'id';
            $db->pkValue = $_POST['id'];
        }

        if ($db->save() === false) {
            die($db->sqlErrorMessage);
        }

	}

	exit;
}

$data = null;

if (isset($_GET['id']) === true) {
	$sql = sprintf("SELECT * FROM `%s` WHERE `id` = ?",
		$tableName);
    $db->addValue($_GET['id'], 'int');
	$row = $db->myOneRow($sql);
    $data = $row;
}

$smarty->assign('data', $data);

$smarty->display('admin/' . $fileName . '-modify.tpl');
