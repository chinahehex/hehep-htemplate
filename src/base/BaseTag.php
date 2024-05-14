<?php
namespace htemplate\base;

use htemplate\nodes\ExpNode;
use htemplate\TemplateManager;

/**
 * 标签基类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class BaseTag
{
    /**
     * 是否系统标签
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $systag = false;

    /**
     * 标签名称
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $name = '';

    /**
     * 标签别名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $alias = '';

    /**
     * 标签库定义
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $tags = [];

    /**
     * 模板管理器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TemplateManager
     */
    protected $templateManager = null;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr => $value) {
                $this->$attr = $value;
            }
        }
    }

    public function _exp(TagExpression $tagExp,$node_attrs,ExpNode $mainNode)
    {
        $expStr = $this->parseVar($tagExp,$node_attrs);
        $mainNode->writeCode($expStr);
    }

    /**
     * 获取标签正则表达式
     *<B>说明：</B>
     *<pre>
     *  每10标签为一组
     *</pre>
     * @return array
     */
    public function getTagExpression():array
    {
        $tagExpList = [];

        foreach ($this->tags as $tag) {
            $tagName = $tag['name'];
            $expAttrs = $tag;

            if (!$this->systag) {
                if (!empty($this->alias)) {
                    $expAttrs['name'] = sprintf('%s:%s',$this->alias,$tag['name']);
                } else {
                    $expAttrs['name'] = sprintf('%s:%s',$this->name,$tag['name']);
                }
            }

            if (empty($expAttrs['handler'])) {
                $expAttrs['handler'] = [$this,$tagName . '_tag'];
            } else {
                $expAttrs['handler'] = [$this,$expAttrs['handler']];
            }

            if (empty($expAttrs['expStart'])) {
                $expAttrs['expStart'] = sprintf('%s%s(.*?)%s',
                    $this->templateManager->config->expStartReg,$expAttrs['name'],
                    $this->templateManager->config->expEndReg);
            }

            //　结束符号
            if (empty($expAttrs['expEnd']) && !empty($tag['close'])) {
                $eofName = isset($tag['eof']) ? $tag['eof'] :  $this->templateManager->config->expEndEofReg;
                $expAttrs['expEnd'] = sprintf('%s%s%s%s',
                    $this->templateManager->config->expStartReg,$eofName,$expAttrs['name'],
                    $this->templateManager->config->expEndReg);
            }

            if (!empty($tag['onTag'])) {
                // 标签表达式
                $expAttrs['tagStart'] = sprintf('%s%s(.*?)%s',
                    $this->templateManager->config->tagStartReg,$expAttrs['name'],
                    $this->templateManager->config->tagEndReg);

                if (!empty($tag['close'])) {
                    $eofName = isset($tag['eof']) ? $tag['eof'] : $this->templateManager->config->tagEndEofReg;
                    $expAttrs['tagEnd'] = sprintf('%s%s%s%s',
                        $this->templateManager->config->tagStartReg,$eofName,$expAttrs['name'],
                        $this->templateManager->config->tagEndReg);
                }
            }

            $tagExpList[$expAttrs['name']] = new TagExpression($expAttrs);
        }

        return $tagExpList;
    }

    /**
     * 解析变量
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $varBody 变量原始串
     * @return string
     */
    protected function parseVar(TagExpression $tagExp,$varBody)
    {
        if (empty($varBody)) {
            return $varBody;
        }

        $result = '';
        $line_char = '';
        if ($tagExp->getIsLineChar()) {
            $line_char = PHP_EOL;
        }

        // 读取首字母
        $firstFlag = substr($varBody, 0, 1);
        switch ($firstFlag) {
            case '$':// 安全模式下变量输出
                $result = '<?php echo ' . $this->buildVarName(substr($varBody, 1)) . "; ?>" . $line_char;
                break;
            case ':':// 方法输出
                $result = '<?php echo ' . $this->buildFunc(substr($varBody, 1)) . ';?>' . $line_char;
                break;
            case '#':// 非安全模式下的变量输出
                $result = '<?php echo hsafe(' . $this->buildVarName(substr($varBody, 1)) . ');?>' . $line_char;
                break;

            case " ":// 原样
                $result = $varBody;
                break;
            default:
                // 非安全模式下的变量输出
                $result = '<?php echo ' . $this->buildVarName($varBody) . ';?>' . $line_char;
        }

        return $result;
    }

    /**
     * 解析html 属性变量
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $attrValue 变量原始串
     * @return string
     */
    protected function parseAttrVar($attrValue)
    {
        if (empty($attrValue)) {
            return $attrValue;
        }

        $result = '';

        // 读取首字母
        $firstFlag = substr($attrValue, 0, 1);
        switch ($firstFlag) {
            case '$':// 非安全模式下变量输出
                $result = '<?php echo ' . $this->buildVarName(substr($attrValue, 1)) . ';?>' ;
                break;
            case ':':// 方法输出
                $result = '<?php echo ' . $this->buildFunc(substr($attrValue, 1)) . ';?>';
                break;
            default:
                $result = $attrValue ;
        }

        return $result;
    }

    /**
     * 解析标签属性变量
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $attrValue 变量原始串
     * @return string
     */
    protected function parseTagVar($attrValue)
    {
        if (empty($attrValue)) {
            return $attrValue;
        }

        $result = '';

        // 读取首字母
        $firstFlag = substr($attrValue, 0, 1);
        switch ($firstFlag) {
            case '$':// 非安全模式下变量输出
                $result = $this->buildVarName(substr($attrValue, 1)) ;
                break;
            case ':':// 方法输出
                $result = $this->buildFunc(substr($attrValue, 1));
                break;
            default:
                $result = '\'' . $attrValue .  '\''; ;
        }

        return $result;
    }

    /**
     * 构建变量名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $varname 变量原始串
     * @return string
     */
    protected function buildVarName($varname)
    {
        $firstFlag = substr($varname, 0, 1);
        // . 表示引用对象,[] 表示数组
        if ($firstFlag !== "$") {
            $varname = '$' . $varname;
        }

        if (strpos($varname,'.') !== false) {
            /** 数组引用 **/
            $arrNames = explode('.',$varname);
            $firstName = array_shift($arrNames);
            $varname = $firstName . '[\'' . implode('\'][\'', $arrNames) . '\']';
        } else if (strpos($varname,':') !== false) {
            /** 对象引用 **/
            $arrNames = explode(':',$varname);
            $varname = implode('->', $arrNames);
        }


        return $varname;
    }

    protected function buildFuncVar($varname)
    {
        $firstFlag = substr($varname, 0, 1);
        if ($firstFlag == "$") {
            if (strpos($varname,'.') !== false) {
                /** 数组引用 **/
                $arrNames = explode('.',$varname);
                $firstName = array_shift($arrNames);
                $varname = $firstName . '[\'' . implode('\'][\'', $arrNames) . '\']';
            } else if (strpos($varname,':') !== false) {
                /** 对象引用 **/
                $arrNames = explode(':',$varname);
                $varname = implode('->', $arrNames);
            }

        } else {
            $varname = '\'' . $varname .  '\'';
        }

        return $varname;
    }

    /**
     * 构建方法名
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $funcname 方法名原始串
     * @return string
     */
    protected function buildFunc($funcname)
    {
        // 判断方法的类型
        $funcList = explode("|",$funcname);

        $pos = strpos($funcList[0],'(');
        if ($pos === false) {
            $value = array_shift($funcList);
        } else {
            $value = '';
        }

        foreach ($funcList as $func) {
            list($funcName,$funcParams) = $this->formatFunc($func);
            $value = $this->buildFuncCall($funcName,$funcParams,$value);
        }

        return $value;
    }

    /**
     * 解析单个方法名称
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $func 标签属性原始字符串
     * @return array
     */
    protected function formatFunc($func)
    {
        $startPos = strpos($func,'(');

        if ($startPos === false) {
            // 变量方法格式:$name|date=###,3
            $paramsPos = strpos($func,'=');
            if ($paramsPos == false) {
                $funcName = $func;
                $funcParams = '###';
            } else {
                $funcName = substr($func,0,$paramsPos);
                $funcParams = substr($func,$paramsPos+1);
                if (strpos($func,'###') == false ){
                    $funcParams = sprintf('###,%s',$funcParams);
                }
            }

        } else {
            // 通用方法格式:U($name,1)
            $endPos = strrpos($func,')');
            $funcName = substr($func,0,$startPos);
            $funcParams =  substr($func,$startPos + 1,$endPos - $startPos - 1);
        }

        $funcParams = str_replace("###","%s",$funcParams);

        return [$funcName,$funcParams];
    }

    protected function buildFuncCall($funcName,$funcParams,$value)
    {

        if (!empty($value)) {
            return sprintf("%s(%s)",$funcName,sprintf($funcParams,$value));
        } else {
            return sprintf("%s(%s)",$funcName,$funcParams);
        }
    }

    /**
     * 解析标签属性
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $rawAttrs 标签属性原始字符串
     * @return array
     */
    protected function buildTagAttrs($rawAttrs)
    {
        $reg = '/\s*([\-a-zA-Z]+)\s*=\s*"([^"]+)"/';
        $result = preg_match_all($reg,$rawAttrs,$matchResult);

        if (empty($result)) {
            return [];
        }

        $attributes = [];
        foreach ($matchResult[1] as $index=>$attr) {
            $attributes[$attr] = $matchResult[2][$index];
        }

        return $attributes;
    }

    /**
     * 构建if 条件
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $attrs 条件属性
     * @return array
     */
    protected function buildCondition($attrs)
    {
        if (isset($attrs['cond'])) {
            $condition = $attrs['cond'];
        } else if ($attrs['condition']){
            $condition = $attrs['condition'];
        } else {
            $condition = '';
        }

        return $condition;
    }

    /**
     * 生成标签的属性html
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     *<B>示例：</B>
     *<pre>
     *  示例：
     *  <js href="css/bootstrap/js/bootstrap.min.js" id="name" attr="xxx"  />
     *  获取的标签属性为
     *  $attr = array("id"=>"name","attr"=>"xxxx");
     *  "<script ".$this->attrHtml($attr)."></script>" ;
     *  结果
     *  <script href="css/bootstrap/js/bootstrap.min.js" id="name" attr="xxx" ></script>
     *</pre>
     * @param array $attrs 标签属性
     * @param array $filter 需要过滤的标签属性
     * @return string
     */
    public function attrHtml($attrs,$filter = [])
    {
        foreach ($filter as $key) {
            unset($attrs[$key]);
        }

        $html = [];
        foreach ($attrs as $key=>$value) {
            $value = $this->parseAttrVar($value);

            if (is_numeric($key)) {
                $html[] = ' ' . $value . ' ';
            } else {
                $html[] = $key . '="' . $value . '"';
            }
        }

        return implode(' ', $html);
    }

    protected function buildHtmlTag($tagName,$attrs,$close = true,$filter = [])
    {
        if (is_array($attrs)) {
            $attrs = $this->attrHtml($attrs,$filter);
        }

        if ($close) {
            if (is_bool($close)) {
                $tagHtml = sprintf('<%s %s></%s>',$tagName,$attrs,$tagName);
            } else {
                $tagHtml = sprintf('<%s %s>%s</%s>',$tagName,$attrs,$close,$tagName);
            }

        } else {
            $tagHtml = sprintf('<%s %s />',$tagName,$attrs);
        }

        return $tagHtml;
    }

    public function formatHtml(string $html,array $data = [])
    {

        if (!empty($data)) {
            $find = array_map(function($value){
                return '{' . $value . '}';
            },array_keys($data));

            $replace = array_values($data);
            $html = str_replace($find,$replace,$html);
        }

        return $html;
    }

    /**
     * 变量数组转化成PHP数组字符串
     *<B>说明：</B>
     *<pre>
     *  $var = ['name'=>'username'];
     *  $phpVar = $this->varExport($var);
     *  $phpVar = "['name'=>'username']";
     *  <?php echo $this->compileTemplate(['name'=>'username']);?>
     *
     * 有安全问题@todo 换成json
     *</pre>
     * @param array $var 变量名称
     * @return string PHP 代码
     */
    protected function varExport($var = [])
    {
        if (is_array($var)) {
            $phpVar = array();
            foreach ($var as $index => $value) {
                if (is_numeric($index)) {
                    $phpVar[] = $this->varExport($value);
                } else {
                    $phpVar[] = var_export($index, true).'=>'.$this->varExport($value);
                }
            }

            return '['.implode(',', $phpVar).']';
        } else {
            if ('$' != substr($var,0,1)) {
                $var = '\''.$var .'\'';
            }

            return $var;
        }
    }


    /**
     * html 默认标签与he 标签分离
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param  $attrs array
     * @return array
     */
    public function spiltAttrs($attrs)
    {
        $htmlAttrs = $heAttrs = [];
        foreach ($attrs as $name=>$value) {
            if (substr($name,0,3) == 'he-') {
                $heAttrs[$name] = $value;
            } else {
                $htmlAttrs[$name] = $value;
            }
        }

        return [$htmlAttrs,$heAttrs];
    }
}
