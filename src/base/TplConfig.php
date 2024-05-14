<?php
namespace htemplate\base;
use htemplate\contexts\Phpweb;
use htemplate\contexts\PhpwebContext;
use htemplate\nodes\ExpNode;
use htemplate\TemplateManager;

/**
 * 模板配置类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class TplConfig
{
    // 上下文对象
    public $context = [];

    // 过滤器
    public $filters = [];

    // 模板文件扩展名
    public $suffix = 'html';

    // 缓存文件扩展名
    public $cacheSuffix = 'php';

    // 模板文件根路径
    public $tplPath = '';

    // 是否开启模板缓存,开启后,会自动缓存代码,加快模板解析的速度
    public $onCache = false;

    // 模板缓存文件目录,模板编译的python代码缓存在文件中,缓存文件存储此目录中
    public $cachePath = '';

    // 资源地址,比如js,css,img,默认提供static(静态资源路径),res(外部资源,比如上传的文件) 字典key
    public $urls = [];

    // 模板缓存有效期,单位秒,0 表示无有效期
    public $timeout = 0;

    // 表达式起始符
    public $expStart = '{{';

    public $expStartReg = '';

    // 表达式结束符
    public $expEnd = '}}';

    public $expEndReg = '';

    // 结束表达式的结束符比如/ 则完整表达式为{/for} 或end,{endfor}
    public $expEndEof = '/';

    public $expEndEofReg = '/';

    // 是否启用标签规则,开启后,匹配表达式 <import name="eduhome.name.yong" />
    public $onTag = true;

    // 标签起始符
    public $tagStart = '<';

    public $tagStartReg = '<';

    // 标签结束符
    public $tagEnd = '>';

    public $tagEndReg = '>';

    // 结束标签结束符号
    public $tagEndEof = '/';

    public $tagEndEofReg = '/';

    public $blockStart = '<!--blockstart_%s-->';
    public $blockEnd = '<!--blockend_%s-->';

    // html block 替换标识
    public $htmlBlock = '__HTMLBLOCK__';

    // 系统标签,默认自动加载sys 表达式,默认标签,书写时无需写入前缀,比如<css href="xxx" />
    public $sysTags = [];

    // 自定义标签,书写时必须写入前缀,比如 标签名称html,则css 标签的书写规则为: <html:css href="xxx" />
    public $customTags = [];

    // 解析节点类
    public $node = '';

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

        $this->expStartReg = preg_quote($this->expStart, '/');
        $this->expEndReg =  preg_quote($this->expEnd, '/');//$this->stripPreg($this->expEnd);
        $this->expEndEofReg = preg_quote($this->expEndEof, '/');//$this->stripPreg($this->expEndEof);
        $this->tagStartReg = preg_quote($this->tagStart, '/');// $this->stripPreg($this->tagStart);
        $this->tagEndReg = preg_quote($this->tagEnd, '/');//$this->stripPreg($this->tagEnd);
        $this->tagEndEofReg = preg_quote($this->tagEndEof, '/');//$this->stripPreg($this->tagEndEof);

        if (empty($this->node)) {
            $this->node = ExpNode::class;
        } else {
            $this->node = $this->templateManager->buildNode($this->node);
        }

        if (array_search('sys',$this->sysTags) === false) {
            array_unshift($this->sysTags,'sys');
        }
    }
}
