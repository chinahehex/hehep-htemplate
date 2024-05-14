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
    protected $_context = [];

    /**
     * 模板配置对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TplConfig
     */
    public $config = [];

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

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = $config;
        }

        $this->init();
    }

    protected function init()
    {

        if ($this->_intiStatus) {
            return ;
        }

        $this->_intiStatus = true;
        $config = $this->config;
        $config['templateManager'] = $this;

        $this->config = new TplConfig($config);

        // 加载系统标签标签库
        $this->addTags($this->config->sysTags,true);

        // 加载自定义标签库
        $this->addTags($this->config->customTags,false);

        // 加载上下文
        $this->loadContext($this->config->context);

        // 加载过滤器
        $filters = $this->buildFilters($this->config->filters);

        eval($filters);
    }

    public function addSysTags($sysTags = [])
    {
        $this->addTags($sysTags,true);
        $this->config->sysTags = array_merge($this->config->sysTags,$sysTags);

        return $this;
    }

    protected function addTags($tags = [],$isSysTag = false)
    {
        foreach ($tags as $alias=>$tagName) {
            if (is_string($alias)) {
                $this->getTag($tagName,$alias,$isSysTag);
            } else {
                $this->getTag($tagName,'',$isSysTag);
            }
        }
    }

    public function addCustomTags($customTags = [])
    {
        $this->addTags($customTags,false);
        $this->config->customTags = array_merge($this->config->customTags,$customTags);

        return $this;
    }

    public function addFilters($filters = [])
    {
        $filters = $this->buildFilters($filters);
        eval($filters);
    }

    public function addContext($context)
    {
        $this->loadContext([$context]);
    }

    protected function loadContext($contexts)
    {
        foreach ($contexts as $context) {
            $this->_context[] = $this->buildContextHandler($context);
        }
    }

    public function getTemplate():Template
    {
        $this->init();

        $attrs = [
            'templateManager'=>$this,
            'config'=>$this->config
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
     * @param string $tagAlias 标签别名
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

    protected function buildContextHandler($ctxHandler)
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
            $newClassStatus = true;
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
                $newClassStatus = true;
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

        $ctx_params = [];
        foreach ($this->_context as $ctx) {
            $ctx_params = array_merge($ctx_params,call_user_func_array($ctx,[]));
        }

        return $ctx_params;
    }

    /**
     * 构建过滤器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @return string
     */
    protected function buildFilters($filters)
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

        foreach ($filters as $alias=>$filter) {
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

                $filter_codes[] = $this->buildFilterFunc($code,[
                    'filter_method_name'=>$methodName,
                    'filter_class'=>'\'' . $filterClass . '\'',
                    'method_name'=>'\'' . $method . '\''
                ]);
            }
        }

        return implode("\r\n",$filter_codes);
    }

    protected function buildFilterFunc(string $func_code,array $data = [])
    {

        if (!empty($data)) {
            $find = array_map(function($value){
                return '{' . $value . '}';
            },array_keys($data));

            $replace = array_values($data);
            $func_code = str_replace($find,$replace,$func_code);
        }

        return $func_code;
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

    /**
     * 加载模板
     *<B>说明：</B>
     *<pre>
     *　略
     *</pre>
     * @param string $tpl 模板名称
     * @param array $data 模板变量
     * @return string
     */
    public function fetch($tpl ,$data = [])
    {
        $template = $this->getTemplate();

        return $template->fetch($tpl,$data);
    }

    public function addUrls($urls)
    {
        $this->config->urls = array_merge($this->config->urls,$urls);

        return $this;
    }

}
