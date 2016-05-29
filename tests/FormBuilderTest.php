<?php namespace Yaim\Mitter\Tests;
use Yaim\Mitter\FormBuilder;

class FormBuilderTest extends \TestCase
{
    /**
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        $this->formBuilder = new FormBuilder(null);
    }

    public function testText()
    {
        $input1 = $this->getInput('text', 'user', ['class' => 'test-class'], 'ahmad');
        $input2 = $this->getInput('text', 'firstName', [], 'ahmad');
        $input3 = $this->getInput('text', 'user', ['class' => 'test-class']);

        $inputTest1 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="ahmad" placeholder="" title=""/>';
        $inputTest2 = '<input type="text" name="firstName" id="firstName" class="row-border form-control " value="ahmad" placeholder="" title=""/>';
        $inputTest3 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="" placeholder="" title=""/>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testPassword()
    {
        $input1 = $this->getInput('password', 'user', ['class' => 'test-class'], 'ahmad');
        $input2 = $this->getInput('password', 'firstName', [], 'ahmad');
        $input3 = $this->getInput('password', 'user', ['class' => 'test-class']);

        $inputTest1 = '<input type="password" name="user" id="user" class="row-border form-control test-class" value="ahmad" placeholder="" title=""/>';
        $inputTest2 = '<input type="password" name="firstName" id="firstName" class="row-border form-control " value="ahmad" placeholder="" title=""/>';
        $inputTest3 = '<input type="password" name="user" id="user" class="row-border form-control test-class" value="" placeholder="" title=""/>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testDate()
    {
        $input1 = $this->getInput('date', 'user', ['class' => 'test-class'], '1372/10/12');
        $input2 = $this->getInput('date', 'firstName', [], '1372/10/12');
        $input3 = $this->getInput('date', 'user', ['class' => 'test-class']);

        $inputTest1 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="1372/10/12" placeholder="" title="" data-datePicker/>';
        $inputTest2 = '<input type="text" name="firstName" id="firstName" class="row-border form-control " value="1372/10/12" placeholder="" title="" data-datePicker/>';
        $inputTest3 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="" placeholder="" title="" data-datePicker/>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testTime()
    {
        $input1 = $this->getInput('time', 'user', ['class' => 'test-class'], '12:30');
        $input2 = $this->getInput('time', 'firstName', [], '12:30');
        $input3 = $this->getInput('time', 'user', ['class' => 'test-class']);

        $inputTest1 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="12:30" placeholder="" title="" data-timePicker/>';
        $inputTest2 = '<input type="text" name="firstName" id="firstName" class="row-border form-control " value="12:30" placeholder="" title="" data-timePicker/>';
        $inputTest3 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="" placeholder="" title="" data-timePicker/>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testDatetime()
    {
        $input1 = $this->getInput('datetime', 'user', ['class' => 'test-class'], '1372/10/12 12:30');
        $input2 = $this->getInput('datetime', 'firstName', [], '1372/10/12 12:30');
        $input3 = $this->getInput('datetime', 'user', ['class' => 'test-class']);

        $inputTest1 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="1372/10/12 12:30" placeholder="" title="" data-dateTimePicker/>';
        $inputTest2 = '<input type="text" name="firstName" id="firstName" class="row-border form-control " value="1372/10/12 12:30" placeholder="" title="" data-dateTimePicker/>';
        $inputTest3 = '<input type="text" name="user" id="user" class="row-border form-control test-class" value="" placeholder="" title="" data-dateTimePicker/>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testHidden()
    {
        $input1 = $this->getInput('hidden', 'user', ['class' => 'test-class'], '1372/10/12 12:30');
        $input2 = $this->getInput('hidden', 'firstName', [], '1372/10/12 12:30');
        $input3 = $this->getInput('hidden', 'user', ['class' => 'test-class']);

        $inputTest1 = '<input type="hidden" name="user" id="user" class="test-class" value="1372/10/12 12:30"/>';
        $inputTest2 = '<input type="hidden" name="firstName" id="firstName" class="" value="1372/10/12 12:30"/>';
        $inputTest3 = '<input type="hidden" name="user" id="user" class="test-class" value=""/>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testLink()
    {
        $input1 = $this->getInput('link', 'user', ['class' => 'test-class'], '/img/nice.png');
        $input2 = $this->getInput('link', 'firstName', [], '/img/nice.png');
        $input3 = $this->getInput('link', 'user', ['class' => 'test-class']);

        $inputTest1 = '<a class="btn btn-sm btn-info link-to-relation" target="_blank" href="/img/nice.png"><i class="fa fa-external-link"></i></a>';
        $inputTest2 = '<a class="btn btn-sm btn-info link-to-relation" target="_blank" href="/img/nice.png"><i class="fa fa-external-link"></i></a>';
        $inputTest3 = '<a class="btn btn-sm btn-info link-to-relation" target="_blank" href=""><i class="fa fa-external-link"></i></a>';

        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
        $this->assertEquals($input3, $inputTest3);
    }

    public function testLocked()
    {
        $input1 = $this->getInput('locked', 'user', ['class' => 'test-class'], '/img/nice.png');
        $input2 = $this->getInput('locked', 'firstName', [], '/img/nice.png');

        $inputTest1 = '<div class="">
    <input type="text" name="user" id="user" class="row-border form-control" value="/img/nice.png" placeholder="" title="" locked disabled/>
</div>
';
        $inputTest2 = '<div class="">
    <input type="text" name="firstName" id="firstName" class="row-border form-control" value="/img/nice.png" placeholder="" title="" locked disabled/>
</div>
';
        $this->assertEquals($input1, $inputTest1);
        $this->assertEquals($input2, $inputTest2);
    }

    private function getInput($type, $name, $options = [], $value = null)
    {
        return $this->formBuilder->{$type}($name, $value, $options)->render();
    }
}