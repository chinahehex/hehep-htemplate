<?php
namespace htemplate\tests;


use htemplate\TemplateManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TemplateManager
     */
    protected $htemplate;

    protected function setUp():void
    {
        var_dump("ggg");
// 或只屏蔽未定义变量错误
        error_reporting(error_reporting() & ~E_STRICT & ~E_NOTICE);

        $this->htemplate = new TemplateManager([
            'tplPath'=>__DIR__ . '/resources/',
        ]);

        $this->htemplate->addFilters(['he_'=>'hehe']);
        //$this->htemplate->addCustomTags(['html']);
    }
}
