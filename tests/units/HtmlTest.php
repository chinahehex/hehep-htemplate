<?php
namespace htemplate\tests\units;
use htemplate\tests\TestCase;

class HtmlTest extends TestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->htemplate->addCustomTags(['html']);
        $this->htemplate->addUrls(['static'=>'http://www.baidu.com/','res'=>'http://www.baidu2.com/']);
    }

    public function testjs()
    {

        $this->assertRegExp('/http:\/\/www.baidu.com\/js\/index.js/',$this->htemplate->fetch('html-js_tag.html',[
            'msg'=>'hello world'
        ]));

        $this->assertRegExp('/http:\/\/www.baidu.com\/js\/indexok.js/',$this->htemplate->fetch('html-js_tag.html',[
            'js_url'=>'js/indexok.js'
        ]));

        $this->assertRegExp('/http:\/\/www.baidu2.com\/js\/index.js/',$this->htemplate->fetch('html-js_tag.html',[

        ]));

    }

    public function testcss()
    {

        $this->assertRegExp('/http:\/\/www.baidu.com\/css\/index.css/',$this->htemplate->fetch('html-css_tag.html',[
            'msg'=>'hello world'
        ]));

        $this->assertRegExp('/http:\/\/www.baidu.com\/css\/indexok.css/',$this->htemplate->fetch('html-css_tag.html',[
            'css_url'=>'css/indexok.css'
        ]));

    }

    public function testimg()
    {

        $this->assertRegExp('/http:\/\/www.baidu.com\/images\/logo.png/',$this->htemplate->fetch('html-img_tag.html',[
            'msg'=>'hello world'
        ]));

        $this->assertRegExp('/http:\/\/www.baidu.com\/images\/logook.png/',$this->htemplate->fetch('html-img_tag.html',[
            'img_url'=>'images/logook.png'
        ]));

    }

    public function testform()
    {
        $this->assertRegExp('/<option selected="selected" value="1">北京<\/option>/',$this->htemplate->fetch('html-form_tag.html',[
            'city_dict'=>['1'=>'北京','2'=>'上海',],
            'cityId'=>1
        ]));

        $this->assertRegExp('/<option selected="selected" value="2">上海<\/option>/',$this->htemplate->fetch('html-form_tag.html',[
            'city_dict'=>['1'=>'北京','2'=>'上海',],
            'cityId'=>2
        ]));

        $this->assertRegExp('/input type="text" name="knd"/',$this->htemplate->fetch('html-form_tag.html',[
            'city_dict'=>['1'=>'北京','2'=>'上海',],
            'cityId'=>2,
        ]));

        $this->assertRegExp('/input type="checkbox" name="yescheckbox"/',$this->htemplate->fetch('html-form_tag.html',[
            'city_dict'=>['1'=>'北京','2'=>'上海',],
            'cityId'=>2,
        ]));





    }



}
