<?php
namespace htemplate\base;
use htemplate\TemplateManager;

/**
 * 模板基类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class BaseTemplate
{

    /**
     * 模板配置对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TplConfig
     */
    protected $config;

    /**
     * 模板数据
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    protected $data = [];

    protected $layout = [];

    protected $blocks = [];


    /**
     * 模板管理器对象
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var TemplateManager
     */
    protected $templateManager = null;

    public $templateCompiler = null;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * 注册模板上下文
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    protected function registerContext():void
    {
        $this->data = array_merge($this->templateManager->getContext(),$this->data);
    }

    /**
     * 注册模板上下文
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $name 变量名
     * @param mixed $value 变量值
     */
    public function assign($name,$value):void
    {
        $this->data[$name] = $value;
    }

    /**
     * 加载模板并返回执行模板结果
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $tpl 模板路径
     * @param mixed $data 变量值
     * @return string
     */
    public function fetch($tpl,$data = [])
    {
        $templateCode = $this->getTemplateCode($tpl);
        // 注入上下文数据
        $this->registerContext();
        // 获取并清空缓存
        $content = $this->execCode($templateCode,$data);

        // 替换
        if (!empty($this->layout)) {
            $layout_replace_name = '__' . strtoupper($this->layout['name']) . '__';
            if (strpos($this->layout['content'], $layout_replace_name) !== false) {
                $content = str_replace($layout_replace_name,$content,$this->layout['content']);
            } else {
                $content = $this->layout['content'] . $content;
            }
        }

        foreach ($this->blocks as $name) {
            $pattern = '/' . sprintf($this->templateManager->config->blockStart . '(.*)' . $this->templateManager->config->blockEnd,$name,$name) . '/is';
            $block_content = '';
            $content = preg_replace_callback($pattern,function($matches) use (&$block_content,$name) {
                $block_content = $matches[1];
                return '';
            },$content);

            $content = str_replace("__" . strtoupper($name) ."__",$block_content,$content);
        }

        return $content;
    }

    /**
     * 加载模板并返回执行模板结果
     *<B>说明：</B>
     *<pre>
     *  内部调用,目前用于小物件
     *</pre>
     * @param string $tpl 模板路径
     * @param mixed $data 变量值
     * @return string
     */
    public function render($tpl,$data = [])
    {
        $templateCode = $this->getTemplateCode($tpl);

        return $this->execCode($templateCode,$data);
    }

    /**
     * 加载模板内容并返回执行模板结果
     *<B>说明：</B>
     *<pre>
     *  内部调用,目前用于小物件
     *</pre>
     * @param string $tpl 模板路径
     * @param mixed $data 变量值
     * @return string
     */
    public function renderContent($tplFileContent,$data)
    {

        $templateCompiler = $this->getTemplateCompiler();
        $tplCode = $templateCompiler->compiler($tplFileContent);

        return $this->execCode($tplCode,$data);
    }

    protected function execCode(&$code,&$data = [])
    {
        ob_start();
        ob_implicit_flush(0);
        extract($this->getData($data), EXTR_OVERWRITE);

        eval('?>' . $code);
        // 获取并清空缓存
        $content = ob_get_clean();
        return $content;
    }

    protected function getData(&$data)
    {
        return array_merge($this->data,$data);
    }

    protected function layout($tpl,$name = 'content')
    {
        $layoutTempl = clone $this;
        $layoutTempl->templateCompiler = null;
        $data = [];
        $this->layout['content'] = $layoutTempl->fetch($tpl,$this->getData($data));
        $this->layout['name'] = $name;
    }

    protected function block($name)
    {
        $this->blocks[$name] = $name;
    }

    protected function addTag($tagName)
    {
        $templateCompiler = $this->getTemplateCompiler();
        $templateCompiler->addTags($tagName);
    }

    /**
     * 加载,执行模板模板
     *<B>说明：</B>
     *<pre>
     *  内部调动,目前用于include,layout 标签,
     *</pre>
     * @param string $tpl 模板路径
     * @return string
     */
    protected function includeTemplate($tpl)
    {
        return $this->getCompilerCacheTemplate($tpl);
    }

    public function buildTemplateFilePath($templateFile)
    {
        // 如文件已经有后缀,不处理
        $ext = pathinfo(basename($templateFile),PATHINFO_EXTENSION);
        if (!empty($ext)) {
            return $this->config->tplPath . $templateFile;
        }

        if (empty($this->config->suffix)) {
            $tplFilePath = $this->config->tplPath . $templateFile;
        } else {
            $tplFilePath = $this->config->tplPath . $templateFile .'.' . $this->config->suffix;
        }

        return $tplFilePath;
    }

    public function buildCacheTemplateFile($templateFile = '',$is_mdir = true)
    {
        if (!empty($templateFile) && substr($templateFile,0,1) == '/') {
            $templateFile = substr($templateFile,1);
        }

        // 去掉扩展名
        if (($pos = strripos($templateFile,'.')) !== false) {
            $templateFile = substr($templateFile,0,$pos);
        }

        // 去掉模板文件与缓存路径重复部分
        $position = strspn($this->config->cachePath ^ $templateFile, "\0");

        if (!empty($position)) {
            $templateFile = substr($templateFile,$position);
        }

        if(strpos(PHP_OS,"WIN")!==false){
            // Windows操作系统
            $templateFile = str_replace(':','',$templateFile);
        }

        $cacheTemplateFile = $this->config->cachePath . $templateFile
            . '.' . $this->config->cacheSuffix;

        $dir = dirname($cacheTemplateFile);
        if ($is_mdir && !is_dir($dir)){
            mkdir($dir, 0777, true);
        }

        return $cacheTemplateFile;
    }

    /**
     * 检测模板缓存文件有效
     *<B>说明：</B>
     *<pre>
     * true: 表示模板缓存有效
     * false:表示模板缓存失效
     *</pre>
     * @param string $templateFile 模板文件路径
     * @return bool
     */
    protected function checkTemplateCacheFile($templateFile):bool
    {
        if (!$this->config->onCache) {
            return false;
        }


        $templateCacheFile = $this->buildCacheTemplateFile($templateFile,false);
        $tplFilePath = $this->buildTemplateFilePath($templateFile);

        if (!is_file($templateCacheFile)) {
            return false;
        }

        if (!is_file($tplFilePath)) {// 模板文件不存在
            return false;
        } else if (filemtime($tplFilePath) > filemtime($templateCacheFile)) {
            // 模板文件如果有更新则缓存需要更新
            return false;
        } else if ($this->config->timeout != 0
            && time() > filemtime($templateCacheFile) + $this->config->timeout) {
            // 缓存是否在有效期内
            return false;
        }

        // 缓存有效
        return true;
    }

    /**
     * 检测代码缓存是否过期
     *<B>说明：</B>
     *<pre>
     * true: 表示模板缓存有效
     * false:表示模板缓存失效
     *</pre>
     * @param string $templateFile 模板文件路径
     * @return bool
     */
    protected function checkTemplateCacheCode($templateFile):bool
    {
        $tplFilePath = $this->buildTemplateFilePath($templateFile);
        if (!is_file($tplFilePath)) {
            return false;
        }

        $cacheCode = $this->getTplCacheCode($templateFile);
        if (empty($cacheCode)) {
            return false;
        }

        $codetime = $cacheCode['ctime'];

        if (filemtime($tplFilePath) > $codetime) {
            // 模板文件如果有更新则缓存需要更新
            return false;
        } else if ($this->config->timeout != 0
            && time() > $codetime + $this->config->timeout) {
            // 缓存是否在有效期内
            return false;
        }

        return true;
    }

    /**
     * 获取模板的代码
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $templateFile 模板文件路径
     * @return array
     */
    protected function getTplCacheCode($templateFile):array
    {
        $key = $this->buildCacheKey($templateFile);

        return isset($this->caches[$key]) ? $this->caches[$key] : [];
    }

    protected function buildCacheKey($templateFile)
    {
        $tplFilePath = $this->buildTemplateFilePath($templateFile);

        return md5($tplFilePath);
    }


    /**
     * 编译模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $templateFile 模板文件路径
     * @return bool
     */
    protected function getTemplateCode($templateFile)
    {
        $tplCode = '';
        $tplFile = $this->buildTemplateFilePath($templateFile);

        // 是否开启缓存
        if ($this->config->onCache && $this->checkTemplateCacheFile($templateFile)) {
            $tplCacheFile = $this->buildCacheTemplateFile($templateFile,true);
            // 缓存有效,直接读取缓存文件代码
            $tplCode = file_get_contents($tplCacheFile);
        } else {
            // 开始编译
            $tplFileContent = file_get_contents($tplFile);
            $templateCompiler = $this->getTemplateCompiler();
            $tplCode = $templateCompiler->compiler($tplFileContent);
            // 代码存储至缓存文件
            if ($this->config->onCache) {
                $tplCacheFile = $this->buildCacheTemplateFile($templateFile,true);
                file_put_contents($tplCacheFile,$tplCode);
            }
        }

        return $tplCode;
    }

    protected function getCompilerCacheTemplate($templateFile)
    {
        $tplFile = $this->buildTemplateFilePath($templateFile);
        $tplCacheFile = $this->buildCacheTemplateFile($templateFile,true);

        // 是否开启缓存
        if ($this->config->onCache && $this->checkTemplateCacheFile($templateFile)) {
            // 缓存有效,直接读取缓存文件代码
            //$tplCode = file_get_contents($tplCacheFile);
        } else {
            // 开始编译
            $tplFileContent = file_get_contents($tplFile);
            $templateCompiler = $this->getTemplateCompiler();
            $tplCode = $templateCompiler->compiler($tplFileContent);
            // 代码存储至缓存文件
            file_put_contents($tplCacheFile,$tplCode);
        }

        return $tplCacheFile;
    }

    /**
     * 创建模板编译对象
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return TemplateCompiler
     */
    protected function getTemplateCompiler():TemplateCompiler
    {
        if (is_null($this->templateCompiler)) {
            $attrs = [
                'templateManager'=>$this->templateManager,
                'pageTags'=>array_merge($this->config->sysTags,$this->config->customTags),
            ];

            $this->templateCompiler = new TemplateCompiler($attrs);
        }

        return $this->templateCompiler;
    }

    protected function getUrl($url,$domain = 'static')
    {
        return $this->config->urls[$domain] . $url;
    }


    /**
     * 字典显示文本
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param array $dictList
     * @param string $keys 字典key列表
     * @param string $glue 分隔符
     * @param string $defualt 默认值
     * @return string
     */
    public  function dictFilter($dictList,$keys,$defualt = '',$glue = ',')
    {
        $dicIds = [];
        if (!is_array($keys)) {
            $dicIds = explode(',',$keys);
        } else {
            $dicIds = $keys;
        }

        if (count($dicIds) == 1) {
            if (isset($dictList[$dicIds[0]])) {
                $dicnames[] = $dictList[$dicIds[0]];
            }
        } else {
            $dicnames = [];
            foreach ($dictList as $id=>$name) {
                if (in_array($id,$dicIds)) {
                    $dicnames[] = $name;
                }
            }
        }

        if (empty($dicnames)) {
            return $defualt;
        }

        return implode($glue,$dicnames);
    }

    protected function argAppend($data = [])
    {
        $data['template'] = $this;

        return $data;
    }
}
