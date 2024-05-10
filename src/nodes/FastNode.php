<?php
namespace htemplate\nodes;

use htemplate\base\TagExpression;

/**
 * 快速节点类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class FastNode extends BaseNode
{

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

    protected $find_start_pos = 0;

    protected $out_start_pos = 0;

    protected $out_end_pos = 0;

    /**
     * 所有的匹配结果
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $bodyMatchResult = [];


    /**
     * 查找匹配结果序号
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var int
     */
    protected $find_index = 0;


    protected function writeBeforeBody($all = false)
    {
        if ($all) {
            $this->nodes[] = substr($this->body,$this->out_start_pos);
        } else {
            $this->nodes[] = substr($this->body,$this->out_start_pos,$this->out_end_pos - $this->out_start_pos - 1);
        }
    }

    public function getContent()
    {
        return implode("",$this->nodes);
    }

    public function refresh()
    {
        $this->expReg = $this->templateCompiler->getFastNodeExpPattern();
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
        // 标签表达式正则
        $this->refresh();
        $tagStart = $this->templateManager->conf->tagStart;
        $tagStartLen = strlen($this->templateManager->conf->tagStart);

        var_dump($this->expReg);

        preg_match_all($this->expReg,$this->body,$bodyMatchResult,PREG_SET_ORDER|PREG_OFFSET_CAPTURE,0);

        if (empty($bodyMatchResult)) {
            $this->writeBeforeBody(true);
            return ;
        }

        $this->bodyMatchResult = $bodyMatchResult;


        print_r($this->bodyMatchResult);


        exit;

        $max_index = count($this->bodyMatchResult);

        while ($this->find_index < $max_index) {
            $matchResult = $this->bodyMatchResult[$this->find_index];
            $onTag = false;
            $expPos = [
                $matchResult[0][1] + 1,
                $matchResult[0][1] + strlen($matchResult[0][0]),
            ];


            // 判断是否标签
            if (substr($matchResult[0][0],0,$tagStartLen) == $tagStart) {
                $onTag = true;
            }

            if ($onTag) {
                // 标签
                $tagName = $matchResult[1][0];
                $exp_attrs = $matchResult[2][0];
            } else {

                // 分离出标签名
                $tagname_reg = '/^(\w+(:\w+)?)\s*(.*)/is';
                preg_match($tagname_reg,$matchResult[3][0],$tagmatchResult,PREG_OFFSET_CAPTURE,0);
                if (!empty($tagmatchResult)) {
                    $tagName = $tagmatchResult[1][0];
                    $exp_attrs = $tagmatchResult[3][0];
                } else {
                    $tagName = 'exp';
                    $exp_attrs = $matchResult[3][0];
                }
            }

            $tagExp = $this->templateCompiler->getTagExpression($tagName);
            // 表达式
            if (is_null($tagExp)) {
                $tagExp = $this->templateCompiler->getTagExpression("exp");
                $exp_attrs = $tagName;
            }

            // 解析表达式
            $this->parseExpression($exp_attrs,$expPos,$tagExp,$onTag);
        }
    }


    /**
     * 解析表达式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $expAttrs 表达式原始串
     * @param array $expPos 表达式位置
     * @param TagExpression $tagExp 表达式对象
     * @param bool $onTag 是否标签
     */
    protected function parseExpression($expAttrs,$expPos,$tagExp,$onTag = false)
    {

        $this->out_end_pos = $expPos[0];
        // 先输出标签之前的字符串
        if ($tagExp->getOutbefore()) {
            $this->writeBeforeBody();
        }

        $handler = $tagExp->getHandler();

        if ($tagExp->hasEnd()) {
            /** 闭合标签 **/
            // 查找标签的结束位置
            $expEndPos = $this->findEndTag($tagExp,$expPos,$onTag);

            // 截取起始标签与结束标签的内容
            $expBodyStartPos = $expPos[1];
            $expBodyEndPos = $expEndPos[0];
            $this->find_start_pos = $expEndPos[1];
            $this->out_start_pos = $expEndPos[1];
            $expBody = substr($this->body,$expBodyStartPos,$expBodyEndPos-$expBodyStartPos);
            // 为标签内容创建子节点,用于解析子节点内容
            $subNode = $this->makeNode($expBody);
            // 执行标签事件
            $result = call_user_func_array($handler,[$tagExp,$expAttrs,$this,$subNode]);
        } else {
            /** 非闭合标签 **/
            $this->out_start_pos = $expPos[1];
            $this->find_start_pos = $expPos[1];

            $this->find_index++;

            // 执行标签事件
            $result = call_user_func_array($handler,[$tagExp,$expAttrs,$this]);
        }
    }

    /**
     * 查找标签的结束位置
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param TagExpression $expression
     * @param array $expPos 标签的起始位置信息
     * @param bool $onTag 是否标签
     * @return array
     */
    protected function findEndTag($expression,$expPos = [],$onTag = false)
    {

        if ($onTag) {
            $endReg = $expression->getTagStartEndReg();
            $endMarkReg = $expression->getTagEndReg();
        } else {
            $endReg = $expression->getExpEndReg();
            $endMarkReg = $expression->getExpEndReg();
        }

        $find_max_count = 30;

        // 结束标签位置信息
        $endExpPos = [];

        $matchNum = 1;
        $this->find_index++;
        $find_count = 0;

        while (true) {

            if ($find_count > $find_max_count) {
                break;
            }

            if ($matchNum <= 0) {
                break;
            }

            $matchResult = $this->bodyMatchResult[$this->find_index];
            echo "-------";
            var_dump($matchResult);

            exit;
            $result = preg_match($endReg,$matchResult[0][0]);

            if (empty($result)) {
                // 缺少标签,格式错误,直接中断查找
                $this->find_index++;
                $find_count++;
                continue;
            }

            $find_count++;

            // 判断是否结束标识
            if (preg_match($endMarkReg,$matchResult[0][0])) {
                $matchNum = $matchNum - 1;
                $endExpPos = [$matchResult[0][1],$matchResult[0][1] + strlen($matchResult[0][0])];
            } else {
                $matchNum = $matchNum + 1;
            }

            $this->find_index++;
        }

        return $endExpPos;
    }
}