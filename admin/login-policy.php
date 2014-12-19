<?php

if (isset($_SESSION['admin']) === false) {
    $app->reUrl('index.php');
}
