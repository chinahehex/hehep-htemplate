<?php
namespace htemplate\tags;

use htemplate\base\BaseTag;
use htemplate\base\TagExpression;
use htemplate\nodes\BaseNode;

class HtmlTag extends BaseTag
{
    protected $tags = [
        ['name'=>"js", 'close'=>false,'onTag'=>true],
        ['name'=>"css", 'close'=>false,'onTag'=>true],
        ['name'=>"img", 'close'=>false,'onTag'=>true],
        ['name'=>"select", 'close'=>false,'onTag'=>true],
        ['name'=>"text", 'close'=>false,'onTag'=>true],
        ['name'=>"radio", 'close'=>false,'onTag'=>true],
        ['name'=>"checkbox", 'close'=>false,'onTag'=>true],
    ];

    public function js_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        $src = $attrs['src'];//js文件名
        $src = '<?php echo $this->getStaticUrl('. $this->buildFuncVar($src) . ');?>';
        $attrs['src'] = $src;
        $html = $this->buildHtmlTag('script',$attrs,true);

        $mainNode->writeCode($html);
    }

    public function css_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $href = $attrs['href'];//js文件名
        $href = '<?php echo $this->getStaticUrl('. $this->buildFuncVar($href) . ');?>';
        $attrs['href'] = $href;

        $html = $this->buildHtmlTag('link',$attrs,false);

        $mainNode->writeCode($html);
    }

    public function img_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $src = $attrs['src'];//js文件名
        $src = '<?php echo $this->getStaticUrl('. $this->buildFuncVar($src) . ');?>';
        $attrs['src'] = $src;

        $html = $this->buildHtmlTag('img',$attrs,false);

        $mainNode->writeCode($html);
    }

    public function select_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        list($html_attrs,$he_attrs) = $this->spiltAttrs($attrs);

        $he_name = $he_attrs['he-name'];
        $he_text = isset($he_attrs['he-text']) ? $he_attrs['he-text'] : '';
        $he_selected = isset($he_attrs['he-selected']) ? $he_attrs['he-selected'] : '';
        if (empty($he_selected)) {
            $he_ctx = '$_request';
            if (!empty($he_attrs['he-ctx'])) {
                $he_ctx = $he_ctx['he-ctx'];
            }

            $var_name = $he_ctx . '.' . $html_attrs['name'];
            $he_selected = $this->buildVarName($var_name);
        }

        $html_attrs = $this->attrHtml($html_attrs);

        $html = '<select {attrs}>
               <option value="">{text}</option>
               <?php foreach({options} as $_key=>$_name):?>
                   <?php if ({selected} != "" && $_key == {selected}):?>
                        <option selected="selected" value="{option_key}">{option_name}</option>
                   <?php else:?>
                        <option value="{option_key}">{option_name}</option>
                    <?php endif;?>
               <?php endforeach; ?>
               </select>';

        $html = $this->formatHtml($html,[
            'attrs'=>$html_attrs,
            'text'=>$he_text,
            'options'=>$he_name,
            'selected'=>$he_selected,
            'option_key'=>'<?php echo $_key;?>',
            'option_name'=>'<?php echo $_name;?>'
        ]);

        $mainNode->writeCode($html);
    }

    public function text_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        list($html_attrs,$he_attrs) = $this->spiltAttrs($attrs);

        if (!isset($he_attrs['he-value'])) {
            $he_ctx = '$_request';
            if (!empty($he_attrs['he-ctx'])) {
                $he_ctx = $he_ctx['he-ctx'];
            }

            $var_name = $he_ctx . '.' . $html_attrs['name'];
            $var_name = $this->buildVarName($var_name);
            if (isset($html_attrs['value'])) {
                $value = $this->buildFuncVar($html_attrs['value']);
            } else {
                $value = '""';
            }

            $html_attrs['value'] = sprintf('<?php echo !empty(%s) ? %s : %s;?>',$var_name,$var_name,$value);
        } else  {
            $html_attrs['value'] = '<?php echo ' . $this->buildFuncVar($he_attrs['he-value']) .';?>'; ;
        }


        $html = $this->buildHtmlTag('input',$html_attrs,false);

        $mainNode->writeCode($html);
    }

    //
    public function radio_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        list($html_attrs,$he_attrs) = $this->spiltAttrs($attrs);

        if (isset($html_attrs['value'])) {
            $value = $this->buildFuncVar($html_attrs['value']);
        } else {
            $value = '""';
        }


        if (!isset($he_attrs['he-checked'])) {
            $he_ctx = '$_request';
            if (!empty($he_attrs['he-ctx'])) {
                $he_ctx = $he_ctx['he-ctx'];
            }

            $var_name = $he_ctx . '.' . $html_attrs['name'];
            $var_name = $this->buildVarName($var_name);

            $html_attrs[] = sprintf('<?php if ( %s !="" && %s == %s){ echo "checked=\"checked\""; } ?>',$var_name,$var_name,$value);
        } else {
            $hechecked = $this->buildFuncVar($he_attrs['he-checked']);
            $html_attrs[] = '<?php if ('.$hechecked.' == '.$value.'){ echo "checked=\"checked\""; } ?>';
        }

        $html = $this->buildHtmlTag('input',$html_attrs,false);

        $mainNode->writeCode($html);
    }

    public function checkbox_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        list($html_attrs,$he_attrs) = $this->spiltAttrs($attrs);


        if (isset($html_attrs['value'])) {
            $value = $this->buildFuncVar($html_attrs['value']);
        } else {
            $value = '""';
        }

        if (!isset($he_attrs['he-checked'])) {
            $he_ctx = '$_request';
            if (!empty($he_attrs['he-ctx'])) {
                $he_ctx = $he_ctx['he-ctx'];
            }

            $var_name = $he_ctx . '.' . $html_attrs['name'];
            $var_name = $this->buildVarName($var_name);

            $html_attrs[] = sprintf('<?php if ( %s !="" && %s == %s){ echo "checked=\"checked\""; } ?>',$var_name,$var_name,$value);
        } else {
            $hechecked = $this->buildFuncVar($he_attrs['he-checked']);
            $html_attrs[] = '<?php if ('.$hechecked.' == '.$value.'){ echo "checked=\"checked\""; } ?>';
        }

        $html = $this->buildHtmlTag('input',$html_attrs,false);

        $mainNode->writeCode($html);
    }


    protected function addJs(BaseNode $mainNode,$src,$attrs = [])
    {
        if (preg_match('/^http(s?):\/\//i',$src) === 0) {
            $src = '<?php echo $this->getStaticUrl('. $this->buildFuncVar($src) . ');?>';
            $attrs['src'] = $src;
        } else {
            $attrs['src'] = $src;
        }

        $html = $this->buildHtmlTag('script',$attrs,true);

        $mainNode->templateCompiler->addStaticBlock(md5($attrs['src']),$html);
    }

    protected function addCss(BaseNode $mainNode,$href,$attrs = [])
    {
        if (preg_match('/^http(s?):\/\//i',$src) === 0) {
            $href = '<?php echo $this->getStaticUrl('. $this->buildFuncVar($href) . ');?>';
            $attrs['href'] = $href;
        } else {
            $attrs['href'] = $src;
        }

        $html = $this->buildHtmlTag('link',$attrs,false);

        $mainNode->templateCompiler->addStaticBlock(md5($attrs['href']),$html);
    }

















}