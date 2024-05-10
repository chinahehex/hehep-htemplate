<?php
namespace htemplate\base;

/**
 * 标签表达式类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class TagExpression
{
    /**
     * 标签起始符号
     *<B>说明：</B>
     *<pre>
     *  一般为正则表达式
     *</pre>
     * @var string
     */
    protected $expStart = '';

    /**
     * 标签结束符号
     *<B>说明：</B>
     *<pre>
     *  一般为正则表达式
     *</pre>
     * @var string
     */
    protected $expEnd = '';


    /**
     * 标签结束符号
     *<B>说明：</B>
     *<pre>
     *  一般为正则表达式
     *</pre>
     * @var string
     */
    protected $tagStart = '';

    /**
     * 标签结束符号
     *<B>说明：</B>
     *<pre>
     *  一般为正则表达式
     *</pre>
     * @var string
     */
    protected $tagEnd = '';

    /**
     * 标签名称
     *<B>说明：</B>
     *<pre>
     *  表达式名称,比如for,if list
     *</pre>
     * @var string
     */
    protected $name = '';

    /**
     * 是否换行符号
     * @var bool
     */
    protected $isLineChar = false;

    /**
     * 标签对应的处理方法
     *<B>说明：</B>
     *<pre>
     *  表达式名称,比如for,if list
     *</pre>
     * @var string
     */
    protected $handler = null;

    /**
     * 是否标签格式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var bool
     */
    protected $onTag = false;

    protected $onMatch = true;

    /**
     * 是否输出状态
     *<B>说明：</B>
     *<pre>
     *  是否输出之前的字符串
     *</pre>
     * @var bool
     */
    protected $outbefore = true;

    protected $params = [];

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr => $value) {
                $this->$attr = $value;
            }
        }
    }

    public function getTagStatus()
    {
        return $this->onTag;
    }

    public function getMatchStatus()
    {
        return $this->onMatch;
    }

    public function getExpStart()
    {
        return $this->expStart;
    }

    public function hasEnd()
    {
        if (!empty($this->expEnd)) {
            return true;
        } else {
            return false;
        }
    }

    public function getExpEnd()
    {
        return $this->expEnd;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getOutbefore()
    {
        return $this->outbefore;
    }

    public function getExpStartEndReg()
    {
        return sprintf('/%s|%s/',$this->expStart,$this->expEnd);
    }

    public function getTagStartEndReg()
    {

        return sprintf('/%s|%s/',$this->tagStart,$this->tagEnd);
    }

    public function getExpEndReg()
    {

        return '/' . $this->expEnd . '/';
    }

    public function getTagEndReg()
    {
        return '/' . $this->tagEnd . '/';
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setIsLineChar($isLineChar = true)
    {
        $this->isLineChar = $isLineChar;
    }

    public function getIsLineChar()
    {
        return $this->isLineChar;
    }


}