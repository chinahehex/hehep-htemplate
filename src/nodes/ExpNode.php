<?php
namespace htemplate\nodes;

use htemplate\base\TagExpression;

/**
 * 表达式节点类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class ExpNode extends BaseNode
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
        $tagStart = $this->templateManager->config->tagStart;
        $tagStartLen = strlen($this->templateManager->config->tagStart);
        while (!$this->stopSearch) {
            // 命中标签格式
            $onTag = false;
            $expresult = preg_match($this->expReg,$this->body,$matchResult,PREG_OFFSET_CAPTURE,$this->find_start_pos);
            if (empty($expresult)) {
                // 匹配不到结果,输出所有字符
                $this->writeBeforeBody(true);
                break;
            }

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
                preg_match($tagname_reg,$matchResult[4][0],$tagmatchResult,PREG_OFFSET_CAPTURE,0);

                if (!empty($tagmatchResult)) {
                    $tagName = $tagmatchResult[1][0];
                    $exp_attrs = $tagmatchResult[3][0];
                } else {
                    $tagName = 'exp';
                    $exp_attrs = $matchResult[4][0];
                }
            }

            $tagName = trim($tagName);
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
            // 判断下一个字符是否为换行符
            if (substr($this->body,$this->find_start_pos+1,1) == PHP_EOL) {
                $tagExp->setIsLineChar(true);
            }
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

        // 结束标签位置信息
        $endExpPos = [];
        $findpos = $expPos[1];
        $matchNum = 1;
        $max = 1;

        while (true) {

            if ($matchNum <= 0) {
                break;
            }

            $max++;

            if ($max > 10) {
                break;
            }
            $result = preg_match($endReg,$this->body,$matchResult,PREG_OFFSET_CAPTURE,$findpos);
            if (empty($result)) {
                // 缺少标签,格式错误,直接中断查找
                break;
            }

            // 判断是否结束标识
            if (preg_match($endMarkReg,$matchResult[0][0])) {
                $matchNum = $matchNum - 1;
                $endExpPos = [$matchResult[0][1],$matchResult[0][1] + strlen($matchResult[0][0])];
                $findpos = $matchResult[0][1] + strlen($matchResult[0][0]);
            } else {
                $findpos = $matchResult[0][1] + strlen($matchResult[0][0]);
                $matchNum = $matchNum + 1;
            }
        }


        return $endExpPos;
    }
}
