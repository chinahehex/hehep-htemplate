<?php
namespace htemplate\tests\common;

use htemplate\Template;

class FooterWidget
{
    public $data;
    public $template;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr => $value) {
                $this->$attr = $value;
            }
        }
    }

    public function handle($data,Template $template)
    {
        return $template->fetch("footer-widget.html",$data);
    }

    public function ok($data,Template $template)
    {
        return $template->fetch("footer-widget.html",$data);
    }

    public static function yes($data,Template $template)
    {
        return $template->fetch("footer-widget.html",$data);
    }

    public function no()
    {
        return $this->template->fetch("footer-widget.html",$this->data);
    }


}
