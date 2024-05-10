<?php
namespace htemplate\tags;

use htemplate\base\BaseTag;
use htemplate\base\TagExpression;
use htemplate\nodes\BaseNode;

class SysTag extends BaseTag
{
    protected $systag = true;

    protected $tags = [

        ['name'=>"layout", 'close'=>false,'onTag'=>true],# 标签库
        ['name'=>"include", 'close'=>false,'onTag'=>true],# 标签库
        ['name'=>"taglib", 'close'=>false,'onTag'=>true],# 标签库
        ['name'=>"block", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"widget", 'close'=>false,'onTag'=>true],# 标签库


        ['name'=>"if", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"elseif", 'close'=>false,'onTag'=>true],# 标签库
        ['name'=>"elif", 'close'=>false,'onTag'=>true,'handler'=>'_elseif'],# 标签库
        ['name'=>"else", 'close'=>false,'onTag'=>true],# 标签库
        ['name'=>"foreach", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"diclist", 'close'=>false,'onTag'=>true],# 标签库

        ['name'=>"dict", 'close'=>false,'onTag'=>true],# 标签库

        ['name'=>"for", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"js", 'close'=>false,'onTag'=>true],# 标签库
        ['name'=>"css", 'close'=>false,'onTag'=>true],# 标签库

        ['name'=>"switch", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"case", 'close'=>true,'onTag'=>true,'outbefore'=>false],# 标签库
        ['name'=>"default", 'close'=>false,'onTag'=>true,'outbefore'=>false],# 标签库
        ['name'=>"php", 'close'=>true],# 标签库
        ['name'=>"empty", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"noempty", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"isset", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"compare", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"eq", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"neq", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"heq", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"nheq", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"gt", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"egt", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"lt", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库
        ['name'=>"elt", 'close'=>true,'onTag'=>true,'handler'=>'compare_tag'],# 标签库

        ['name'=>"range", 'close'=>true,'onTag'=>true],# 标签库
        ['name'=>"in", 'close'=>true,'onTag'=>true,'handler'=>'range_tag'],# 标签库
        ['name'=>"noin", 'close'=>true,'onTag'=>true,'handler'=>'range_tag'],# 标签库
        ['name'=>"between", 'close'=>true,'onTag'=>true,'handler'=>'range_tag'],# 标签库
        ['name'=>"nobetween", 'close'=>true,'onTag'=>true,'handler'=>'range_tag'],# 标签库
        ['name'=>"pass", 'close'=>true,'onTag'=>true],# 标签库
    ];

    public function layout_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $file = $attrs['file'];//js文件名
        $file = '<?php $this->layout('. $this->buildFuncVar($file) . ');?>';

        $mainNode->writeCode($file);
    }

    public function include_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $file = $attrs['file'];//js文件名
        $file = '<?php include $this->includeTemplate('. $this->buildFuncVar($file) . ');?>';

        $mainNode->writeCode($file);
    }

    public function taglib_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $tagName = $attrs['name'];//js文件名
        $tagAlias = isset($attrs['alias']) ? $attrs['alias'] : '';//js文件名
        $mainNode->addTag($tagName,$tagAlias);
        $mainNode->refresh();

        return false;
    }

    public function block_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $attrs['name'];//js文件名

        $mainNode->writeLine(sprintf('<?php $this->block("%s")?>',$name));
        $mainNode->writeLine(sprintf($this->templateManager->conf->blockStart,$name));
        $mainNode->writeCode($subNode->getCode());
        $mainNode->writeLine(sprintf($this->templateManager->conf->blockEnd,$name));
    }

    public function widget_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {

        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $attrs['name'];//js文件名
        $args = isset($attrs['args']) ? $attrs['args'] : '';
        $constructor = isset($attrs['constructor']) ? $attrs['constructor'] : 'true';;

        list($widget_class,$widget_method,$new_class_status) = $mainNode->templateManager->buildWidgetHandler($name);

        // 传递参数的方式
        if ($constructor == 'true') {
            $widget_class  = sprintf('new %s(%s)',$widget_class,$args);
            $handler_php = sprintf('[%s,\'%s\']',$widget_class,$widget_method);
            $mainNode->writeCode(sprintf('<?php echo call_user_func(%s) ?>',$handler_php));
        } else {
            if ($new_class_status) {
                $widget_class  = sprintf('new %s()',$widget_class);
                $handler_php = sprintf('[%s,\'%s\']',$widget_class,$widget_method);
            } else {
                $handler_php = sprintf('[\'%s\',\'%s\']',$widget_class,$widget_method);
            }

            $mainNode->writeCode(sprintf('<?php echo call_user_func_array(%s,%s) ?>',$handler_php,$args));
        }
    }

    public function pass_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $mainNode->writeCode($subNode->getBody());
    }

    public function if_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $condition = $this->buildCondition($attrs);
        $mainNode->writeCode('<?php if('.$condition.'): ?>');
        $mainNode->writeCode($subNode->getCode());
        $mainNode->writeCode('<?php endif; ?>');
    }

    public function elseif_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $condition = $this->buildCondition($attrs);
        $mainNode->writeCode('<?php elseif('. $condition .'): ?>');
    }

    public function else_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $mainNode->writeCode('<?php else: ?>');
    }

    public function foreach_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        $name =  $attrs['name'];
        $value = isset($attrs['value']) ? $attrs['value'] : 'vo';
        $key = !empty($attrs['key']) ? $this->buildVarName($attrs['key']):'';

        $index = '';
        if ( !empty($attrs['index'])) {
            $index = $attrs['index'];
        }

        $indexStart = $indexInr = '';

        if (!empty($index)) {
            $indexStart = $this->buildVarName($index). '=0;';
            $indexInr = $this->buildVarName($index). '++;';
        }

        $name = $this->buildVarName($name);
        $value = $this->buildVarName($value);

        if (!empty($key)) {
            $parseStr = '<?php if(is_array('.$name.')): ' . $indexStart . ' foreach('.$name.' as '.$key.'=>'.$value.'): ?>';
        } else {
            $parseStr = '<?php if(is_array('.$name.')): ' . $indexStart . ' foreach('.$name.' as '.$value.'): ?>';
        }

        $parseStr .= $subNode->getCode();;
        $parseStr .= '<?php ' . $indexInr .' endforeach; endif; ?>';

        $mainNode->writeCode($parseStr);
    }

    public function for_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        // name,start,end,step
        $name =  isset($attrs['name']) ? $attrs['name'] : 'index';
        $start = isset($attrs['start']) ? $attrs['start'] : 0;
        $step = isset($attrs['step']) ? $attrs['step'] : 1;
        $end = isset($attrs['end']) ? $attrs['end'] : 0;

        $name = $this->buildVarName($name);
        $parseStr = sprintf('<?php for(%s;%s;%s): ?>',$name . '='.$start,$name . '<' . $end,$name . '+=' . $step);
        $parseStr .= $subNode->getCode();;
        $parseStr .= '<?php endfor; ?>';

        $mainNode->writeCode($parseStr);
    }

    public function dict_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        // name,start,end,step
        $dictlist =  $attrs['name'];
        $key = $attrs['key'];
        $defualt = isset($attrs['defualt']) ? $attrs['defualt'] : '';

        $dictlist = $this->buildFuncVar($dictlist);
        $key = $this->buildFuncVar($key);
        $defualt = $this->buildFuncVar($defualt);

        $parseStr = sprintf('<?php echo hdict(%s,%s,%s); ?>',$dictlist,$key,$defualt);

        $mainNode->writeCode($parseStr);
    }

    public function diclist_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        $dictlist =  $attrs['name'];
        $keys = $attrs['keys'];
        $glue = isset($attrs['glue']) ? $attrs['glue'] : ',';
        $defualt = isset($attrs['defualt']) ? $attrs['defualt'] : '';

        $dictlist = $this->buildFuncVar($dictlist);
        $keys = $this->buildFuncVar($keys);
        $glue = $this->buildFuncVar($glue);
        $defualt = $this->buildFuncVar($defualt);

        $parseStr = sprintf('<?php echo hdicts(%s,%s,%s,%s); ?>',$dictlist,$keys,$glue,$defualt);

        $mainNode->writeCode($parseStr);
    }

    public function switch_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        $name = $this->buildVarName($attrs['name']);
        $parseStr = sprintf('<?php switch(%s): ?>',$name);
        $parseStr .= $subNode->getCode();;
        $parseStr .= '<?php endswitch; ?>';

        $mainNode->writeCode($parseStr);
    }

    public function case_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $value = $attrs['value'];
        $parseStr = '<?php case "' .$value . '": ?>' . $subNode->getCode();;

        $breakStatus  = isset($attrs['break']) ? $attrs['break'] : '';
        if ('' ==$breakStatus || $breakStatus) {
            $parseStr .= '<?php break;?>';
        }

        $mainNode->writeCode($parseStr);
    }

    public function default_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $parseStr = '<?php default: ?>';

        $mainNode->writeCode($parseStr);
    }

    public function php_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $parseStr = '<?php ' . $subNode->getCode() .' ?>';

        $mainNode->writeCode($parseStr);
    }

    public function empty_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $this->buildVarName($attrs['name']);
        $parseStr   =   '<?php if(empty('.$name.')): ?>'.$subNode->getCode().'<?php endif; ?>';

        $mainNode->writeCode($parseStr);
    }

    public function noempty_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {

        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $this->buildVarName($attrs['name']);
        $parseStr   =   '<?php if(!empty('.$name.')): ?>'.$subNode->getCode().'<?php endif; ?>';

        $mainNode->writeCode($parseStr);
    }

    public function isset_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {

        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $this->buildVarName($attrs['name']);
        $parseStr   =   '<?php if(isset('.$name.')): ?>'.$subNode->getCode().'<?php endif; ?>';

        $mainNode->writeCode($parseStr);
    }

    public function compare_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {

        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $this->buildVarName($attrs['name']);
        $value = $attrs['value'];

        if (isset($attrs['op'])) {
            $op = $attrs['op'];
        } else {
            $op = $tagExp->getName();
        }

        if ($op == in_array($op,['gt','>'])) {
            $cond = sprintf("%s > %s",$name,$value);
        } else if (in_array($op,['egt','>='])) {
            $cond = sprintf("%s >= %s",$name,$value);
        } else if (in_array($op,['lt','<'])) {
            $cond = sprintf("%s < %s",$name,$value);
        } else if (in_array($op,['elt','<='])) {
            $cond = sprintf("%s <= %s",$name,$value);
        } else if (in_array($op,['eq','=='])) {
            $cond = sprintf("%s == %s",$name,$value);
        } else if (in_array($op,['neq','!='])) {
            $cond = sprintf("%s != %s",$name,$value);
        } else if (in_array($op,['heq','==='])) {
            $cond = sprintf("%s === %s",$name,$value);
        } else if (in_array($op,['nheq','!=='])) {
            $cond = sprintf("%s !== %s",$name,$value);
        }

        $parseStr   =   '<?php if('.$cond.'): ?>'.$subNode->getCode().'<?php endif; ?>';

        $mainNode->writeCode($parseStr);
    }


    public function range_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {

        $attrs = $this->buildTagAttrs($node_attrs);
        $name = $this->buildVarName($attrs['name']);
        $value = $attrs['value'];

        if (isset($attrs['op'])) {
            $op = $attrs['op'];
        } else {
            $op = $tagExp->getName();
        }

        if ($op == 'in') {
            $cond = sprintf("in_array(%s,%s)",$value,$name);
        } else if ($op == 'notin') {
            $cond = sprintf("!in_array(%s,%s)",$value,$name);
        } else if ($op == 'between') {
            $cond = sprintf("%s >= %s && %s <= %s",$name,$value[0],$name,$value[1]);
        } else if ($op == 'notbetween') {
            $cond = sprintf("%s < %s && %s > %s",$name,$value[0],$name,$value[1]);
        }

        $parseStr   =   '<?php if('.$cond.'): ?>'.$subNode->getCode().'<?php endif; ?>';

        $mainNode->writeCode($parseStr);
    }

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












}