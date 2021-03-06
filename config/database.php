<?php

return array(
    'default' => array(
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'database' => 'test',
        'username' => 'root',
        'password' => '123456',
    ),
    'double' => array(
        'driver'   => 'mysql',
        'host'     => array(
            'read'  => 'localhost',
            'write' => 'localhost'
        ),
        'database' => 'test',
        'username' => 'root',
        'password' => 123456,
    ),
    'test' => array(
        'driver'   => 'sqlite',
        'host'     => 'localhost',
        'database' => dirname(__DIR__) . '/tests/tmp/test.sqlite',
    ),
);
