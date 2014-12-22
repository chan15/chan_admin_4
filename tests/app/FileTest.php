<?php

class TestFile extends \PHPUnit_Framework_TestCase
{
    public $path;
    public $txt;

    public function setUp()
    {
        $this->path = dirname(__DIR__) . '/tmp/';
        $this->txt = $this->path . 'test.txt';
    }

    public function testDelete()
    {
        @copy($this->txt, $this->path . 'temp.txt');
        $this->assertTrue(File::delete($this->path . 'temp.txt'));
    }

    public function testDeleteDir()
    {
        $tempDir = $this->path . 'temp_dir/';
        @mkdir($tempDir);
        @copy($this->txt, $this->path . 'temp_dir/test.txt');
        $this->assertTrue(File::deleteDir($tempDir));
    }

    public function testCopy()
    {
        $copy = File::copy($this->txt, $this->path . 'copy/', true);
        $this->assertTrue(is_array($copy));
        File::deleteDir($this->path . 'copy/');
    }

    public function testMove()
    {
        $copy = File::copy($this->txt, $this->path . 'move/', true);
        $move = File::move($copy['target'], $this->path . 'move/sub/', true);
        $this->assertTrue(is_array($move));
        File::deleteDir($this->path . 'move/');
    }
}
