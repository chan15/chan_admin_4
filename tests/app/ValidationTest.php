<?php

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    public $validate;
    public $lang;
    public $path;
    public $txt;

    public function setUp()
    {
        $this->validate = new Validation('en');
        $this->lang = include dirname(dirname(__DIR__)) . '/lang/en/validation.php';
        $this->path = dirname(__DIR__) . '/tmp/';
        $this->txt = $this->path . 'test.txt';
    }

    public function testFile()
    {
        $_FILES = array(
            'test' =>  array(
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => $this->txt,
                'error' => 0,
                'size' => filesize($this->txt),
            )
        );
        $rules = array(
            'test' => 'file'
        );

        $this->assertTrue($this->validate->check(null, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testRequired($inputs)
    {
        $rules = array(
            'username' => 'required'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testLength($inputs)
    {
        $rules = array(
            'username' => 'length:4'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testMaxLength($inputs)
    {
        $rules = array(
            'username' => 'maxLength:4'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testMinLength($inputs)
    {
        $rules = array(
            'username' => 'minLength:4'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testInteger($inputs)
    {
        $rules = array(
            'password' => 'integer'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testBool($inputs)
    {
        $rules = array(
            'active' => 'bool'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testUrl($inputs)
    {
        $rules = array(
            'url' => 'url'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testMax($inputs)
    {
        $rules = array(
            'number' => 'max:10'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testMin($inputs)
    {
        $rules = array(
            'number' => 'min:8'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testUnique($inputs)
    {
        $db = new Database('test');
        $db->table = 'tests';
        $db->addField('name', 'test');
        $db->save();

        $rules = array(
            'name' => 'unique:test.tests.name'
        );

        $this->assertFalse($this->validate->check($inputs, $rules));

        $sql = "DELETE FROM `tests`";
        $db->sqlExecute($sql);
    }

    /**
     * @dataProvider inputsProvider
     */
    public function testEmail($inputs)
    {
        $rules = array(
            'email' => 'email'
        );

        $this->assertTrue($this->validate->check($inputs, $rules));
    }

    public function testTranslate()
    {
        $translate = array(
            'username' => 'user'
        );

        $this->validate->translate = $translate;
        $this->assertEquals($this->validate->translate('username'), 'user');
    }

    public function testFirst()
    {
        $rules = array(
            'username' => 'required'
        );
        $validate = $this->validate->check(null, $rules);

        $this->assertEquals($this->validate->first(), 'username' . $this->lang['required']);
    }

    public function testAll()
    {
        $rules = array(
            'username' => 'required'
        );
        $validate = $this->validate->check(null, $rules);

        $this->assertCount(1, $this->validate->all());
    }

    function inputsProvider()
    {
        return array(
            array(
                array(
                    'active'   => true,
                    'email'    => 'test@gmail.com',
                    'name'     => 'test',
                    'number'   => 10,
                    'password' => 123456,
                    'url'      => 'http://www.google.com',
                    'username' => 'user',
                )
            )
        );
    }
}
