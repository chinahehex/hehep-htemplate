<?php
namespace htemplate\base;

use htemplate\nodes\BaseNode;
use htemplate\nodes\ExpNode;
use htemplate\TemplateManager;

/**
 * 模板编译器
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class TemplateCompiler
{

    /**
     * 模板对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var BaseTemplate
     */
    protected $template;

    /**
     * 页面标签列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $pageTags = [];

    /**
     * 标签表达式对象列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TagExpression[]
     */
    protected $_expressions = [];

    /**
     * 模板管理器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TemplateManager
     */
    protected $templateManager = null;

    /**
     * 模板html 块内容
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $html_blocks = [];

    protected $static_blocks = [];

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr => $value) {
                $this->$attr = $value;
            }
        }
    }

    public function compiler(&$content)
    {

        $node = $this->templateManager->conf->node;
        /** @var  BaseNode $expNode**/

        $expNode = new $node($content,[
            'templateManager'=>$this->templateManager,
            'templateCompiler'=>$this,
        ]);

        $expNode->start();
        $content = $expNode->getContent();
        $this->replaceBlock($content);

        return $content;
    }

    public function addStaticBlock($key,$res)
    {
        $this->static_blocks[$key] = $res;
    }

    protected function replaceBlock(&$content)
    {
        if (empty($this->static_blocks) && empty($this->html_blocks)) {
            return $content;
        }

        $blocks_str = implode("\r\n",$this->static_blocks);
        $blocks_str .= "\r\n" . implode("\r\n\r\n",$this->html_blocks);
        $htmlBlockTag = $this->templateManager->conf->htmlBlock;
        if (strpos($htmlBlockTag,$content) !== false) {
            $content = str_replace($htmlBlockTag,$blocks_str,$content);
        } else {
            $content = $content . "\r\n\r\n" . $blocks_str;
        }

        return $content;
    }

    public function addHtmlBlock($block_content)
    {
        $this->html_blocks[] = $block_content;
    }

    /**
     * 添加标签
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $tagName
     * @param string $tagAlias
     */
    public function addTags($tagName,$tagAlias = ''):void
    {
        if (!empty($tagAlias)) {
            $this->pageTags[$tagAlias] = $tagName;
        } else {
            $this->pageTags[] = $tagName;
        }

        $this->_expressions = [];

        $this->buildPageTagExpression();
    }

    /**
     * 构建当前页面所有的标签表达式对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function buildPageTagExpression():void
    {
        foreach ($this->pageTags as $tagAlias=>$tagName) {
            $expressions = $this->templateManager->getTagExpression($tagName,$tagAlias);
            $this->_expressions = array_merge($this->_expressions,$expressions);
        }

        $this->_expressions['exp'] = new TagExpression([
            'name'=>'exp','outbefore'=>true,'onMatch'=>false,'handler'=>[$this->templateManager->getTag("sys"),'_exp'],'expStart'=>sprintf('%s(.+?)%s',
                $this->templateManager->conf->expStartReg,$this->templateManager->conf->expEndReg)
        ]);
    }


    public function getAllExpression()
    {
        if (empty($this->_expressions)) {
            $this->buildPageTagExpression();
        }

        return $this->_expressions;
    }

    public function getTagExpression($name)
    {
        if (isset($this->_expressions[$name])) {
            return $this->_expressions[$name];
        } else {
            return null;
        }
    }

    public function getNodeExpPattern()
    {
        $this->getAllExpression();

        $expNames = [];
        foreach ($this->_expressions as $expression) {
            if ($expression->getMatchStatus() && $expression->getTagStatus()) {
                $expNames[] = $expression->getName();
            }
        }

        $exp = '[^' . $this->templateManager->conf->expEndReg .']+';
        $expreg = sprintf('(?:%s)(%s)(?:%s)',
            $this->templateManager->conf->expStartReg,
            $exp,
            $this->templateManager->conf->expEndReg
        );
        
        $tagreg = '';
        if (!empty($expNames)) {
            $tagreg = sprintf('%s(?:(%s)\b)(([^%s])*)%s',
                $this->templateManager->conf->tagStart,
                implode('|',$expNames),
                $this->templateManager->conf->tagEnd,
                $this->templateManager->conf->tagEnd
            );
        }

        $reg = sprintf('/%s|%s/i',$tagreg,$expreg);

        return $reg;
    }

}