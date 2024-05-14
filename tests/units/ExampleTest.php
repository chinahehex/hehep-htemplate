<?php
namespace htemplate\tests\units;
use htemplate\tests\TestCase;

class ExampleTest extends TestCase
{

    /**
     * 验证常用
     */
    public function testCommon()
    {
        // 变量注入
        $this->assertRegExp('/hello world/',$this->htemplate->fetch('example.html',[
            'msg'=>'hello world'
        ]));

        $this->assertRegExp('/2010-02-05/',$this->htemplate->fetch('example.html',[
            'addtime'=>'2010-02-05 05:20'
        ]));

        $this->assertRegExp('/成年人/',$this->htemplate->fetch('example.html',[
            'age'=>15,
            'age_type_text1'=>'成年人',
            'age_type_text2'=>'未成年',
        ]));

        $this->assertRegExp('/未成年/',$this->htemplate->fetch('example.html',[
            'age'=>13,
            'age_type_text1'=>'成年人',
            'age_type_text2'=>'未成年',
        ]));

        $this->assertRegExp('/未成年/',$this->htemplate->fetch('example.html',[
            'age'=>14,
            'age_type_text1'=>'成年人',
            'age_type_text2'=>'未成年',
        ]));

        $this->assertRegExp('/hehep|thinkphp|yii2/',$this->htemplate->fetch('example.html',[
            'names'=>['hehep','thinkphp','yii2']
        ]));


    }
}
