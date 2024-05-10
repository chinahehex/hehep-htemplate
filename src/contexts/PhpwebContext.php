<?php
namespace htemplate\contexts;

class PhpwebContext
{
    public function handle()
    {
        return [
            '_request'=>&$_REQUEST,
            '_get'=>&$_GET,
            '_post'=>&$_POST,
            '_server'=>&$_SERVER,
            '_cookie'=>&$_COOKIE,
            '_session'=>&$_SESSION,
            '_files'=>&$_FILES,
        ];
    }
}