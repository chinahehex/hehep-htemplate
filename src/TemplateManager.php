<?php
namespace htemplate;

use htemplate\base\BaseTag;
use htemplate\base\TplConfig;
use htemplate\contexts\PhpwebContext;

/**
 * 模板管理器
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class TemplateManager
{
    /**
     * 过滤器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $filters = [];

    /**
     * 上下文
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $context = [];

    protected $_context = [];

    /**
     * 模板配置对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TplConfig
     */
    public $conf = [];

    /**
     * 标签库
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $tags = [];

    protected $_tagExpressions = [];

    protected $_intiStatus = false;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $attr => $value) {
                $this->$attr = $value;
            }
        }
    }

    protected function init()
    {

        if ($this->_intiStatus) {
            return ;
        }

        $this->_intiStatus = true;
        $conf = $this->conf;
        $conf['templateManager'] = $this;

        $this->conf = new TplConfig($conf);

        // 加载系统标签标签库
        foreach ($this->conf->sysTags as $alias=>$tagName) {

            if (is_string($alias)) {
                $this->getTag($tagName,$alias,true);
            } else {
                $this->getTag($tagName,'',true);
            }
        }

        // 加载自定义标签库
        foreach ($this->conf->customTags as $alias=>$tagName) {
            if (is_string($alias)) {
                $this->getTag($tagName,$alias,false);
            } else {
                $this->getTag($tagName,'',false);
            }
        }

        // 加载上线文
        $this->_context = $this->buildContextHandler($this->context);
        // 加载过滤器
        $filters = $this->buildFilters();

        eval($filters);
    }

    public function getTemplate():Template
    {
        $this->init();

        $attrs = [
            'templateManager'=>$this,
            'config'=>$this->conf
        ];

        return new Template($attrs);
    }


    /**
     * 获取标签对应的表达式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $tagName 标签名
     * @return array
     */
    public function getTagExpression($tagName,$tagAlias):array
    {
        if (isset($this->_tagExpressions[$tagName])) {
            return $this->_tagExpressions[$tagName];
        }

        $tag = $this->getTag($tagName,$tagAlias);
        $tagExpList = $tag->getTagExpression();
        $this->_tagExpressions[$tagName] = $tagExpList;

        return $this->_tagExpressions[$tagName];
    }

    /**
     * 获取标签对应的表达式
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $tagName 标签名
     * @param bool $sysTag 是否系统标签
     * @return BaseTag
     */
    public function getTag($tagName,$alias = '',$sysTag = false)
    {
        if (isset($this->tags[$tagName])) {
            return $this->tags[$tagName];
        }

        if (strpos($tagName,'\\') !== false) {
            $tagClass =  $tagName;
        } else {
            $tagClass =  __NAMESPACE__ . '\\tags\\' .ucfirst($tagName). 'Tag';
        }

        $attrs = [
            'systag'=>$sysTag,
            'templateManager'=>$this,
            'name'=>$tagName,
            'alias'=>$alias
        ];

        /** @var BaseTag $tag**/
        $tag = new $tagClass($attrs);
        $this->tags[$tagName] = $tag;

        return $tag;
    }

    public function buildContextHandler($ctxHandler)
    {
        if (empty($ctxHandler)) {
            return [new PhpwebContext(),'handle'];;
        }

        return $this->buildHandler($ctxHandler,'contexts');
    }

    public function buildWidgetHandler($handler)
    {

        $handler = '\\' . str_replace(".","\\",$handler);

        $newClassStatus = false;
        if (strpos($handler,"@@") !== false) {
            list($handlerClass,$handlerMethod) = explode("@@",$handler);
        } else if (strpos($handler,"@") !== false) {
            list($handlerClass,$handlerMethod) = explode("@",$handler);
            $newClassStatus = true;
        } else {
            $handlerClass = $handler;
        }

        if (empty($handlerMethod)) {
            $handlerMethod = 'handle';
        }

        return [$handlerClass,$handlerMethod,$newClassStatus];
    }

    protected function buildHandler($handler,$namespaceName)
    {
        if (is_array($handler) || $handler instanceof \Closure) {
            return $handler;
        } else if (is_string($handler)) {
            $newClassStatus = false;
            if (strpos($handler,"@@") !== false) {
                list($handlerClass,$handlerMethod) = explode("@@",$handler);
                //return [$middlewareClass,$middlewareMethod];
            } else if (strpos($handler,"@") !== false) {
                list($handlerClass,$handlerMethod) = explode("@",$handler);
                $newClassStatus = true;
            } else {
                $handlerClass = $handler;
            }

            if (empty($handlerMethod)) {
                $handlerMethod = 'handle';
            }

            if (strpos($handlerClass,"\\") === false) {
                $handlerClass = __NAMESPACE__ . '\\' . $namespaceName . '\\'.$handlerClass;
            }

            if ($newClassStatus) {
                return [new $handlerClass(),$handlerMethod];
            } else {
                return [$handlerClass,$handlerMethod];
            }
        }
    }

    public function getContext()
    {
        if (is_null($this->_context)) {
            return [];
        }

        return call_user_func_array($this->_context,[]);
    }

    /**
     * 构建过滤器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return BaseTag
     */
    protected function buildFilters()
    {

        $suffix = '_filter';
        $suffix_len = strlen($suffix);
        $code = '
            if (!function_exists(\'{filter_method_name}\')) {
                function {filter_method_name}(){
                    $args = func_get_args();
                    return call_user_func_array([{filter_class},{method_name}],$args);
                }
            }
        ';

        $filter_codes = [];

        foreach ($this->filters as $alias=>$filter) {

            // 读取类的所有方法名称

            if (strpos($filter,'\\') !== false) {
                $filterClass =  $filter;
            } else {
                $filterClass =  '\\' . __NAMESPACE__ . '\\filters\\' .ucfirst($filter). 'Filter';
            }

            $methods = get_class_methods($filterClass);
            foreach ($methods as $method) {
                if (substr($method, 0-$suffix_len) != $suffix) {
                    continue;
                }

                $methodName = substr($method,0,strlen($method) - $suffix_len);
                if (is_string($alias)) {
                    $methodName = $alias . $methodName;
                }

                $filter_codes[] = $this->formatStr($code,[
                    'filter_method_name'=>$methodName,
                    'filter_class'=>'\'' . $filterClass . '\'',
                    'method_name'=>'\'' . $method . '\''
                ]);
            }
        }

        return implode("\r\n",$filter_codes);
    }

    protected function formatStr(string $html,array $data = [])
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


    public function buildNode($nodeName)
    {

        if (strpos($nodeName,"\\") === false) {
            $nodeClass = __NAMESPACE__ . '\\nodes\\'.ucfirst($nodeName) . 'Node';
        } else {
            $nodeClass = $nodeName;
        }

        return $nodeClass;
    }

}