<?php
namespace htemplate\nodes;

use htemplate\base\TagExpression;
use htemplate\base\TemplateCompiler;
use htemplate\TemplateManager;

/**
 * 节点基类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class BaseNode
{
    /**
     * 模板对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TemplateCompiler
     */
    public $templateCompiler = null;

    /**
     * 模板管理器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TemplateManager
     */
    public $templateManager = null;

    protected $body = '';

    protected $nodes = [];

    /**
     * 停止搜索状态
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var bool
     */
    protected $stopSearch = false;

    protected $expReg = null;


    public function __construct(&$body,$attrs = [])
    {
        $this->body = $body;

        if (!empty($attrs)) {
            foreach ($attrs as $attr => $value) {
                $this->$attr = $value;
            }
        }
    }

    public function addTag($tagName,$tagAlias)
    {
        $this->templateCompiler->addTags($tagName,$tagAlias);
    }

    public function writeNode(BaseNode $node)
    {
        $this->nodes[] = $node->getContent();
    }

    public function writeLine($code)
    {
        $this->nodes[] = "\n".$code;
    }

    public function writeCode($code)
    {
        $this->nodes[] = $code;
    }

    public function start()
    {
        $this->find();
    }

    public function getContent()
    {
        return '';
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getCode()
    {
        $this->find();

        return $this->getContent();
    }

    public function refresh()
    {
        $this->expReg = $this->templateCompiler->getNodeExpPattern();
    }

    protected function makeNode($body):ExpNode
    {
        return new ExpNode($body,[
            'templateCompiler'=>$this->templateCompiler,
            'templateManager'=>$this->templateManager,
        ]);
    }

    /**
     * 解析
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function find()
    {

    }




}