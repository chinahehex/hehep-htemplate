<?php
namespace htemplate\tests\units;
use htemplate\tests\TestCase;

class FiltersTest extends TestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->htemplate->addFilters(['he_'=>'hehe']);
    }

    public function testsale()
    {

        $this->assertRegExp('/hello world/',$this->htemplate->fetch('filter_tag.html',[
            'name'=>'hello world'
        ]));

        $this->assertRegExp('/hello&amp;world/',$this->htemplate->fetch('filter_tag.html',[
            'name'=>'hello&world'
        ]));

        $this->assertRegExp('/hello&amp;world/',$this->htemplate->fetch('filter_tag.html',[
            'name_safe'=>'hello&world'
        ]));

        $this->assertRegExp('/2018年09月16日/',$this->htemplate->fetch('filter_tag.html',[
            'format'=>'Y年m月d日',
            'datetime'=>'2018-09-16 22:59:59'
        ]));

        $this->assertRegExp('/2018年09月16日/',$this->htemplate->fetch('filter_tag.html',[
            'format'=>'Y年m月d日',
            'datetime'=>strtotime('2018-09-16 22:59:59')
        ]));

        $this->assertRegExp('/hel!!!/',$this->htemplate->fetch('filter_tag.html',[
            'mystring'=>'hello',
            'len'=>3,
            'suffix'=>'!!!'
        ]));




    }
}
