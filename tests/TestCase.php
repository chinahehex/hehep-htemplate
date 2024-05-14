<?php
namespace htemplate\tests;


use htemplate\TemplateManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TemplateManager
     */
    protected $htemplate;

    protected function setUp()
    {
        $this->htemplate = new TemplateManager([
            'tplPath'=>__DIR__ . '/resources/',
        ]);

        $this->htemplate->addFilters(['he_'=>'hehe']);
        //$this->htemplate->addCustomTags(['html']);
    }
}
