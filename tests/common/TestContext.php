<?php
namespace htemplate\tests\common;

class TestContext
{
    public function handle()
    {
        return [
            '_request'=>['name'=>'ok'],
            '_get'=>['id'=>'123'],
        ];
    }
}
