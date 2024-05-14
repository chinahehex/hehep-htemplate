# hehep-htemplate

## 任务列表
- 支持html属性变量　ok
- 支持标签属性变量
- 支持两种解析方式,加快编译速度
- 支持过滤器 ok
- 标签支持别名定义 ok
- 支持小物件 ok
- 定义常用过滤器(日期格式,截取字符串,数字格式,字典)
- 支持其他框架上下文
- 支持html 标签

## 介绍
- hehep-htemplate 是一个PHP 模板引擎组件
- 支持{{}},{},以及<> 标签混合使用
- 支持layout(模板继承) 标签
- 支持block 模块
- 支持include 标签
- 支持if 标签
- 支持for dict,list 标签
- 支持注释
- 支持php代码标签
- 支持调用方法
- 支持加载静态文件
- 定义变量
- 支持不解析标签
- html 标签
- 自定义过滤器
- 优化方法的调用方式,支持| 方法,支持定义参数
- 支持标签,empty  
- 支持导入自定义过滤器
- 支持导入自定义标签
- 支持模板文件缓存代码至内存
- 支持代码缓存过期时间
- 支持模板后缀自定义
- js,css,img 标签
- 提供小物件(widget)
  
## 安装
- 直接下载:
- **gitee下载**:
```
git clone git@gitee.com:chinahehex/hehep-htemplate.git
```

- **github下载**:
```
git clone git@github.com:chinahehex/hehep-htemplate.git
```

- 命令安装：
```
composer require hehep/htemplate
```

## 组件配置
- 基础配置
```php

$conf = [
    // 上下文对象
    'context'=>[],//phpweb
    'filters'=>[
        'h'=>'hehe'
    ],
    // 模板文件根路径
    'tplPath'=>'@app@/view/',
    // 系统标签,默认自动加载sys 表达式,默认标签,书写时无需写入前缀,比如<css href="xxx" />
    'sysTags'=>['sys'],
    // 自定义标签,书写时必须写入前缀,比如 标签名称html,则css 标签的书写规则为: <html:css href="xxx" />
    'customTags'=>['html'],
    // 模板文件扩展名
    'suffix' => 'html',
    // 缓存文件扩展名
    'cacheSuffix' => 'php',
    // 是否开启模板缓存,开启后,会自动缓存代码,加快模板解析的速度
    'onCache' => true,
    // 模板缓存文件目录,模板编译的python代码缓存在文件中,缓存文件存储此目录中
    'cachePath' => '/home/hehe/www/cache/htpl/',
    // 资源地址,比如js,css,img,默认提供static(静态资源路径),res(外部资源,比如上传的文件) 字典key
    'urls'=>[
        'static'=>'http://eduhome.xuewei.cn/'
    ],
    // 模板缓存有效期,单位秒,0 表示无有效期
    'timeout' => 5,
    // 表达式起始符
    'expStart' => '{{',
    // 表达式结束符
    'expEnd' => '}}',
    // 结束表达式的结束符比如/ 则完整表达式为{/for} 或end,{endfor}
    'expEndEof' => '/',
    // 是否启用标签规则,开启后,匹配表达式 <import name="eduhome.name.yong" />
    'onTag' => true,
    // 标签起始符
    'tagStart' => '<',
    // 标签结束符
    'tagEnd' => '>',
    // 结束标签结束符号
    'tagEndEof' => '/',
];

```

## 基本用法
- php 示例代码
```php
use htemplate\TemplateManager;

$config = [];
$htemplate = new TemplateManager($config);
// 加载(user/add)模板,返回模板内容
$data = [
    'msg'=>'hello world'
];
$html = $htemplate->fetch("user/index",$data);

```
- 模板html示例代码
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
{{msg}}
</body>
</html>

```

## 过滤器
```
在模板页面里使用的函数或方法称为过滤器,可自定义过滤器,比如系统默认过滤器hehe
```
### 定义过滤器

- 定义过滤器类
```php
namespace admin\htemplate\filters;

class HeheFilter
{
    public static function date_filter($format,$datetime)
    {
        return date($format,strtotime($datetime));
    }
}

```

### 加载过滤器
```php
$config = [
    'filters'=>[
        // ‘过滤器方法前缀’=>'过滤器类路径'
        'he_'=>'\admin\htemplate\filters\HeheFilter'
   ];

// 方式一
$htemplate = new htemplate\TemplateManager($config);

// 方式二
$htemplate = new htemplate\TemplateManager();
$htemplate->addFilters(['he_'=>'\admin\htemplate\filters\HeheFilter']);

```
###  模板页面使用过滤器
```html
<h1>
{{:he_date('Y-m-d','2010-02-05 05:20')}}
</h1>

```



## 上下文
```
在模板页面里默认可以使用的数据,比如$_REQUEST,$_SESSION,$_COOKIES,等等,
模板加载时,会自动注入对应的上下文数据至模板页面
目前只支持phpweb(传统的web php),后续可扩展swoole 上下文,或workerman上下文
```

### 定义上下文类
```php
namespace admin\htemplate\contexts;

class PhpwebContext
{
    public function handle()
    {
        return [
            '_request'=>&$_REQUEST,
            '_get'=>&$_GET,
            '_post'=>&$_POST,
            '_server'=>&$_SERVER,
            '_cookie'=>&$_COOKIE,
            '_session'=>&$_SESSION,
            '_files'=>&$_FILES,
        ];
    }
}

```

### 配置上下文
```php
$config = [
    // 上下文
    'context'=>['admin\htemplate\contexts\PhpwebContext'],
];

// 方式1
$htemplate = new htemplate\TemplateManager($config);

// 方式2
$htemplate->addContext('admin\htemplate\contexts\PhpwebContext');
```

###  模板页面使用上下文
```html
<h1>
{{$_get['id']}}
</h1>

```

## 标签
```
模板引擎里提供的所有功能都是通过标签形式提供,可通过自定义新的标签实现多更功能扩展
标签属性:
name:标签名称
close:是否闭合标签
onTag:是否支持html标签格式
handler:标签处理方法或函数
```

### 自定义标签

```php
namespace admin\htemplate\tags;

use htemplate\base\BaseTag;
use htemplate\base\TagExpression;
use htemplate\nodes\BaseNode;

class HtmlTag extends BaseTag
{
    // 定义标签
    protected $tags = [
        // 表单select
        ['name'=>"select", 'close'=>false,'onTag'=>true],
        // 不解析内容标签
        ['name'=>"pass", 'close'=>true,'onTag'=>true],# 标签库
    ];
    
    // 非闭合标签
    public function select_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode)
    {
        $attrs = $this->buildTagAttrs($node_attrs);

        list($html_attrs,$he_attrs) = $this->spiltAttrs($attrs);

        $he_name = $he_attrs['he-name'];
        $he_text = isset($he_attrs['he-text']) ? $he_attrs['he-text'] : '';
        $he_selected = isset($he_attrs['he-selected']) ? $he_attrs['he-selected'] : '';
        if (empty($he_selected)) {
            $he_ctx = '$_request';
            if (!empty($he_attrs['he-ctx'])) {
                $he_ctx = $he_ctx['he-ctx'];
            }

            $var_name = $he_ctx . '.' . $html_attrs['name'];
            $he_selected = $this->buildVarName($var_name);
        }

        $html_attrs = $this->attrHtml($html_attrs);

        $html = '<select {attrs}>
               <option value="">{text}</option>
               <?php foreach({options} as $_key=>$_name):?>
                   <?php if ($_key == {selected}):?>
                        <option selected="selected" value="{option_key}">{option_name}</option>
                   <?php else:?>
                        <option value="{option_key}">{option_name}</option>
                    <?php endif;?>
               <?php endforeach; ?>
               </select>';

        $html = $this->formatHtml($html,[
            'attrs'=>$html_attrs,
            'text'=>$he_text,
            'options'=>$he_name,
            'selected'=>$he_selected,
            'option_key'=>'<?php echo $_key;?>',
            'option_name'=>'<?php echo $_name;?>'
        ]);

        $mainNode->writeCode($html);
    }
    
    public function pass_tag(TagExpression $tagExp,$node_attrs,BaseNode $mainNode,BaseNode $subNode)
    {
        $mainNode->writeCode($subNode->getBody());
    }

}

```
### 加载标签类
```php
$config = [
    // 上下文
    'customTags'=>['html'=>'\admin\htemplate\tags\HtmlTag'],
];

// 方式1
$htemplate = new htemplate\TemplateManager($config);

// 方式2
$htemplate->addCustomTags(['html'=>'\admin\htemplate\tags\HtmlTag']);

// 标签作为系统标签
$htemplate->addSysTags(['\admin\htemplate\tags\HtmlTag']);

```

### 自定义标签模板中使用
```html
<html:select name="oknd" he-name="$selectlist" />

{{html:pass}}
<!-- 此部分内容原样输出 -->
{{include file="common/footer"}}
{{html:js src="js/dist/seajs.min.js" }}

{{/html:pass}}

```

## 系统默认标签

### layout 布局标签
```
标签属性:
file:布局文件路径
name:替换的关键词,默认content
```
- 布局文件 layout\home.html
```html
<!DOCTYPE html>
<html>
<head>
    <css href="css/core.min.css" rel="stylesheet" />
    <js src="js/dist/core.min.js?v=2.1.4" />
</head>
<body class="gray-bg">
<!-- 被替换子模板内容 -->
__CONTENT__
</body>
</html>
```

- 加载布局文件
```html
{{layout file="layout/home"}}
<h1>welcome you </h1>

```

### include 标签
```
标签属性:
file:模板路径
```
- 示例
```html
<!-- 加载html common\footer.html 模板文件 -->
{{include file="common/footer"}}

```

### taglib 标签
```
模板页面里动态加载模板标签,标签需在页面第一行加载
标签属性:
name：标签类路径
alias:标签别名,比如html
```
- 示例
```html
{{taglib name="html"}}
```

### block 区块标签
```
标签属性:
name:区块名称,对应布局文件的内容替换标识,比如__TITLE__,__CONTENT__
```
- 示例
```html
{{block name="title"}}

<!-- 区块内容 -->
name:{{$name}}

{{/block}}
```

### widget 小物件标签
```
通用功能模块的封装,一般会输出页面
标签属性:
name:小物件类路径,比如admin.service.Address@@ok
args:小物件参数
constructor:参数是否传入构造器,否则直接传入小物件对应的方法

小物件类路径格式:.,@,@@
- \表示默认调用Address 的handle 方法
admin.service.Address

- @ 表示创建Address 对象并调用close 方法
admin.service.Address@close

- @@ 表示调用Address 类的静态close 方法
admin.service.Address@@close
```
- 示例
```html
<widget name="htemplate.tests.common.FooterWidget" args="['msg'=>$msg]"/>

<widget name="htemplate.tests.common.FooterWidget@ok" args="['msg'=>$msg]"/>

<widget name="htemplate.tests.common.FooterWidget@@yes" args="['msg'=>$msg]"/>

<widget name="htemplate.tests.common.FooterWidget@no" args="['data'=>['msg'=>$msg]]" constructor="true"/>

```

### if 标签
```
标签属性:
cond 或 condition: 条件语句
```
- 示例
```html
<if cond="$age > 10 && $age < 20">
    {{$age}}
    <elseif cond="$age > 20 " />
    {{$age}}
    <else/>
    {{$age}}
</if>
```

### foreach遍历标签
```
标签属性:
name:遍历的数组
value:数组元素
key:数组键值
index:序号,从0开始累加
```
- 示例
```html
{{foreach name="arr" value="vo" key="k" index="i"}}

姓名:{{$vo['realName']}}
数组键:{{$k}}
序号:{{$i}}

{{/foreach}}



```

### for 遍历标签
```
标签属性:
name:当前数值变量
start:起始值,大于等于起始值
end:结束值,小于等于结束值
step:步长,每次累加的值
```
```html
<!--从数字1遍历至8,每次遍历增长2-->
{{for name="index" start="1" end="8" step="2"}}

输出:{{$index}};

{{/for}}
```

### js,css 静态资源标签
- 示例
```html

<css href="css/style.min.css?v=4.1.0" rel="stylesheet" />
<css href="$css_url" rel="stylesheet" />

<js src="js/dist/core.min.js?v=2.1.4" />
<js src="$js_css_url" />

```

### switch 标签
- 示例
```html
{{switch name="age"}}

{{case value="1"}} 1 {{/case}}
{{case value="2"}} 2 {{/case}}
{{default}}
    333

{{/switch}}

```

### PHP原生标签
- 示例
```html

{{php}} $name=2;{{/php}}

```

### 条件标签
- 示例
```html
<empty name="title">
    title 为空
</empty>

<noempty name="title">
    title 不为空
</noempty>

<isset name="stu.name">
    数组$stu['name'] 是否存在
</isset>
```

### 比较标签
```
比较标签有以下
eq:等于
neq:不等于
heq:恒等于
nheq:非恒等于
gt:大于
egt:大于等于
lt:小于
elt:小于等于
```
- 示例
```html
{{eq name="status" value="1"}}
   变量$status 等于1
{{/eq}}

{{gt name="status" value="1"}}
   变量$status 大于1
{{/gt}}

```

### 范围标签

```html
{{in name="invalue" value="1"}}
    1 存在$invalue 数组中
{{/in}}

{{noin name="invalue" value="1"}}
    1 不存在$invalue 数组中
{{/noin}}


{{between name="[1,10]" value="1"}}
    值在1-10 范围内
{{/between}}

```

### pass原样输出标签
- 示例
```html
{{pass}}

{{include file="common/footer"}}
{{html:js src="js/dist/seajs.min.js" }}

{{/pass}}
```

### 字典标签
```
标签属性:
name:字典集合
keys:目标字典值,多个键值逗号隔开
defualt:默认值
```
- 示例
```html
<!--输出$dictlist 数组 键值为1 的值,如果键值不存在,则输出"无"-->
<dict name="$dictlist" keys="1" glue="," defualt="无"/>

<dict name="$dictlist" keys="1,5" glue="," defualt="无"/>

```

## html 标签
```
为了区分html 标签属性与自定义标签的属性,则自定义标签的属性名称统一以he- 作为前缀,比如he-name,he-value
```

### 表单标签
- 示例
```html

<!--html select 标签,遍历$selectlist 数组,生成html select 标签需要的属性
he-name: 遍历的变量数组
he-text: 空option 的文本
he-selected:默认选中的option
-->
<html:select name="selectname" he-name="$selectlist" he-text="请选择城市" he-selected="$cityId"  />

<!--html text 输入框 标签
he-value: 输入框默认值
-->
<html:text  type="text" name="knd" he-value="$text"/>

<!--html 单选框,多选框
he-checked:选中值
-->
<html:radio  type="radio" name="name" value="1" he-checked="$radio_value" />

<html:checkbox  type="checkbox" name="name" value="1" he-checked="$radio_value" />

```

### 静态资源地址标签
- 示例
```html

<!-- 加载js  -->
<html:js src="js/dist/seajs.min.js" />
<!-- 加载css 样式  -->
<html:css href="css/core.min.css" rel="stylesheet" />

<!-- 加载img 图片 -->
<html:img src="images/logo.png"  />

```


##  系统默认过滤器
### xss 危险标签过滤
```html
<h1>
{{:he_xss($name)}}
</h1>
```

### 过滤所有危险字符
```html
<h1>
{{:he_safe($name)}}
</h1>
```

### 加载小物件
```html
<h1>
{{:he_widget('admin.service.Address@@ok',['page'=>$page])}}
</h1>
```

### 日期格式化
```html
<h1>
{{:he_date('Y-m-d',$ctime)}}
</h1>
```

### 截取字符串
```html
<h1>
{{:he_substr($name,0,10)}}
{{:he_substr($name,0,10,'...')}}
</h1>
```

### 显示指定字典文本
```html
<h1>
{{:he_dict($dicts,$dict_key)}}
{{:he_dict($dicts,$dict_key)}}
</h1>
```

### 显示多个字典文本
```html
<h1>
{{:he_dict($dicts,$dict_keys)}}
{{:he_dict($dicts,$dict_keys,'多个字典文本分隔符','默认值')}}
</h1>
```

