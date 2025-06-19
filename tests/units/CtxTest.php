<?php
namespace htemplate\tests\units;
use htemplate\tests\TestCase;

class CtxTest extends TestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->htemplate->addContext('htemplate\tests\common\TestContext');
    }

    public function testrun()
    {

        $this->assertRegExp('/ok/',$this->htemplate->fetch('ctx_tag.html',[
        ]));

        $this->assertRegExp('/123/',$this->htemplate->fetch('ctx_tag.html',[
        ]));

    }
}
