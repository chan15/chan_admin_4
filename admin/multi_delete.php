<?php

include dirname(__DIR__) . '/bootstrap.php';

include 'login-policy.php';
$app->checkSourceUrl();

$id = $_POST['id'];
$tableField = $_POST['tableField'];
$ids = explode(',', $id);
$db = new Database;

foreach ($ids as $id) {
    $db->table = $tableField;
	$db->pk = 'id';
	$db->pkValue = $id;

	// Delete file if needed
	switch($tableField) {
        case 'table':
			$path = '../uploads/test/';
			$db->fileDeleteArray[] = $db->getFileName('image');
			$db->dataFileDelete($path);
            break;
    }

	$db->delete();

    // Delete detail data if needed
    switch ($tableField) {
        case 'table':
            $sqlDetail = "SELECT `id`, `image` FROM `detail_table` WHERE `fk` = ?";
            $db->addValue($id, 'int');
            $rowDetail = $db->myRow($sqlDetail);

            if ($rowDetail !== null) {
                foreach ($rowDetail as $detail) {
                    $db->table = 'detail_table';
                    $db->pk = 'id';
                    $db->pkValue = $detail['id'];
                    $db->fileDeleteArray[] = $detail['image'];
                    $db->dataFileDelete($path);
                    $db->delete();
                }
            }

            break;
    }
}
