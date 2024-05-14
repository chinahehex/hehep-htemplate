<?php
namespace htemplate\tests\units;
use htemplate\tests\TestCase;

class TagsTest extends TestCase
{

    // 布局标签
    public function testlayout()
    {
        // 变量注入
        $this->assertRegExp('/布局layout内容/',$this->htemplate->fetch('tags.html',[
            'msg'=>'hello world'
        ]));

    }

    public function testinclude()
    {
        // 变量注入
        $this->assertRegExp('/凉拌炒鸡蛋/',$this->htemplate->fetch('include_tag.html',[
            'msg'=>'凉拌炒鸡蛋'
        ]));
    }

    public function testtaglib()
    {
        // 变量注入
        $this->assertRegExp('/script/',$this->htemplate->fetch('taglib_tag.html',[
            //'msg'=>'凉拌炒鸡蛋'
        ]));
    }

    public function testblock()
    {
        $this->assertRegExp('/布局block内容/',$this->htemplate->fetch('block_tag.html',[
            //'msg'=>'凉拌炒鸡蛋'
        ]));

        $this->assertRegExp('/头部block/',$this->htemplate->fetch('block_tag.html',[
            'header_msg'=>'头部block'
        ]));

        $this->assertRegExp('/中间block/',$this->htemplate->fetch('block_tag.html',[
            'center_msg'=>'中间block'
        ]));

        $this->assertRegExp('/尾部block/',$this->htemplate->fetch('block_tag.html',[
            'footer_msg'=>'尾部block'
        ]));
    }

    public function testwidget()
    {
        $this->assertRegExp('/hehe-ok/',$this->htemplate->fetch('widget_tag.html',[
            'msg'=>'hehe-ok'
        ]));

        $this->assertRegExp('/hehe@ok/',$this->htemplate->fetch('widget_tag.html',[
            'msg'=>'hehe@ok'
        ]));

        $this->assertRegExp('/hehe@@yes/',$this->htemplate->fetch('widget_tag.html',[
            'msg'=>'hehe@@yes'
        ]));

        $this->assertRegExp('/hehe@no/',$this->htemplate->fetch('widget_tag.html',[
            'msg'=>'hehe@no'
        ]));
    }

    public function testif()
    {
        $this->assertRegExp('/成年人/',$this->htemplate->fetch('if_tag.html',[
            'age'=>18,
            'msg'=>'成年人',
        ]));

        $this->assertRegExp('/未年人/',$this->htemplate->fetch('if_tag.html',[
            'age'=>15,
            'msg'=>'未年人',
        ]));

        $this->assertRegExp('/小孩/',$this->htemplate->fetch('if_tag.html',[
            'age'=>13,
            'msg'=>'小孩',
        ]));
    }

    public function testforeach()
    {
        $this->assertRegExp('/0-admin-15/',$this->htemplate->fetch('foreach_tag.html',[
            'users'=>[ ['name'=>'admin','age'=>15], ['name'=>'hehe','age'=>29],],
        ]));

        $this->assertRegExp('/1-hehe-29/',$this->htemplate->fetch('foreach_tag.html',[
            'users'=>[ ['name'=>'admin','age'=>15], ['name'=>'hehe','age'=>29],],
        ]));
    }

    public function testfor()
    {
        $this->assertRegExp("/序号:1/",$this->htemplate->fetch('for_tag.html',[
            'start'=>1,
            'end'=>5,
            'step'=>2
        ]));

        $this->assertRegExp("/序号:3/",$this->htemplate->fetch('for_tag.html',[
            'start'=>1,
            'end'=>5,
            'step'=>2
        ]));

        $this->assertRegExp("/序号:5/",$this->htemplate->fetch('for_tag.html',[
            'start'=>1,
            'end'=>6,
            'step'=>2
        ]));


    }

    public function testRes()
    {
        $this->htemplate->addUrls(['static'=>'http://www.baidu.com/']);
        $this->assertRegExp("/http:\/\/www.baidu.com\/css\/index.css/",$this->htemplate->fetch('res_tag.html',[

        ]));

        $this->assertRegExp("/http:\/\/www.baidu.com\/css\/indexok.css/",$this->htemplate->fetch('res_tag.html',[
            'css_url'=>'css/indexok.css'
        ]));

        $this->assertRegExp("/http:\/\/www.baidu.com\/js\/dist\/core.min.js/",$this->htemplate->fetch('res_tag.html',[

        ]));

        $this->assertRegExp("/http:\/\/www.baidu.com\/js\/indexok.js/",$this->htemplate->fetch('res_tag.html',[
            'js_url'=>'js/indexok.js'
        ]));


    }

    public function testswitch()
    {
        $this->assertRegExp("/年龄1/",$this->htemplate->fetch('switch_tag.html',[
            'age'=>1,
            'msg'=>'年龄1'
        ]));

        $this->assertRegExp("/年龄2/",$this->htemplate->fetch('switch_tag.html',[
            'age'=>2,
            'msg'=>'年龄2'
        ]));

        $this->assertRegExp("/年龄4/",$this->htemplate->fetch('switch_tag.html',[
            'age'=>4,
            'msg'=>'年龄4'
        ]));
    }

    public function testphp()
    {
        $this->assertRegExp("/年龄4/",$this->htemplate->fetch('php_tag.html',[
            'msg'=>'年龄4'
        ]));
    }

    public function testcondition()
    {
        $this->assertRegExp("/未空/",$this->htemplate->fetch('condition_tag.html',[
            'var_empty'=>'',
            'msg_empty'=>'未空'
        ]));

        $this->assertRegExp("/不为空/",$this->htemplate->fetch('condition_tag.html',[
            'var_empty'=>'不不不',
            'msg_empty'=>'不为空'
        ]));

        $this->assertRegExp("/不为空/",$this->htemplate->fetch('condition_tag.html',[
            'var_noempty'=>'不不不',
            'msg_noempty'=>'不为空'
        ]));

        $this->assertRegExp("/为空/",$this->htemplate->fetch('condition_tag.html',[
            'var_noempty'=>'',
            'msg_noempty'=>'为空'
        ]));

        $this->assertRegExp("/存在/",$this->htemplate->fetch('condition_tag.html',[
            'var'=>['name'=>'1'],
            'msg_isset'=>'存在'
        ]));

        $this->assertRegExp("/不存在/",$this->htemplate->fetch('condition_tag.html',[
            'var'=>['namej'=>'1'],
            'msg_isset'=>'不存在'
        ]));

    }

    public function testcompare()
    {
        $this->assertRegExp("/相等eq/",$this->htemplate->fetch('compare_tag.html',[
            'eq_status'=>1,
            'msg_eq'=>'相等eq'
        ]));

        $this->assertRegExp("/!相等eq/",$this->htemplate->fetch('compare_tag.html',[
            'eq_status'=>2,
            'msg_eq_else'=>'!相等eq'
        ]));

        $this->assertRegExp("/!相等neq/",$this->htemplate->fetch('compare_tag.html',[
            'neq_status'=>2,
            'msg_neq'=>'!相等neq'
        ]));

        $this->assertRegExp("/相等neq/",$this->htemplate->fetch('compare_tag.html',[
            'neq_status'=>1,
            'msg_neq_else'=>'相等neq'
        ]));

        $this->assertRegExp("/相等heq/",$this->htemplate->fetch('compare_tag.html',[
            'heq_status'=>1,
            'msg_heq'=>'相等heq'
        ]));

        $this->assertRegExp("/不相等heq/",$this->htemplate->fetch('compare_tag.html',[
            'heq_status'=>'1',
            'msg_heq_else'=>'不相等heq'
        ]));

        $this->assertRegExp("/不恒等nheq/",$this->htemplate->fetch('compare_tag.html',[
            'nheq_status'=>"1",
            'msg_nheq'=>'不恒等nheq'
        ]));

        $this->assertRegExp("/恒等nheq/",$this->htemplate->fetch('compare_tag.html',[
            'nheq_status'=>1,
            'msg_nheq_else'=>'恒等nheq'
        ]));


        $this->assertRegExp("/大于等于1/",$this->htemplate->fetch('compare_tag.html',[
            'egt_status'=>2,
            'msg_egt'=>'大于等于1'
        ]));

        $this->assertRegExp("/大于等于1/",$this->htemplate->fetch('compare_tag.html',[
            'egt_status'=>1,
            'msg_egt'=>'大于等于1'
        ]));

        $this->assertRegExp("/小于1/",$this->htemplate->fetch('compare_tag.html',[
            'egt_status'=>0,
            'msg_egt_else'=>'小于1'
        ]));


        $this->assertRegExp("/小于1/",$this->htemplate->fetch('compare_tag.html',[
            'elt_status'=>0,
            'msg_elt'=>'小于1'
        ]));

        $this->assertRegExp("/小于等于1/",$this->htemplate->fetch('compare_tag.html',[
            'elt_status'=>1,
            'msg_elt'=>'小于等于1'
        ]));

        $this->assertRegExp("/大于1/",$this->htemplate->fetch('compare_tag.html',[
            'elt_status'=>2,
            'msg_elt_else'=>'大于1'
        ]));

    }

    public function testrange()
    {
        $this->assertRegExp("/指定的状态值存在列表/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,2,3,4],
            'status'=>3,
            'msg_in'=>'指定的状态值存在列表'
        ]));

        $this->assertRegExp("/指定的状态值不存在列表/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,2,3,4],
            'status'=>5,
            'msg_in_else'=>'指定的状态值不存在列表'
        ]));


        $this->assertRegExp("/指定的状态值不存在列表notin/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,2,3,4],
            'status'=>5,
            'msg_notin'=>'指定的状态值不存在列表notin'
        ]));

        $this->assertRegExp("/指定的状态值存在列表notin/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,2,3,4],
            'status'=>3,
            'msg_notin_else'=>'指定的状态值存在列表notin'
        ]));


        $this->assertRegExp("/指定的状态值在列表范围内between/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,10],
            'status'=>3,
            'msg_between'=>'指定的状态值在列表范围内between'
        ]));

        $this->assertRegExp("/指定的状态值不在列表between/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,10],
            'status'=>11,
            'msg_between_else'=>'指定的状态值不在列表between'
        ]));

        $this->assertRegExp("/指定的状态值不在列表notbetween/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,10],
            'status'=>11,
            'msg_notbetween'=>'指定的状态值不在列表notbetween'
        ]));

        $this->assertRegExp("/指定的状态值在列表notbetween/",$this->htemplate->fetch('range_tag.html',[
            'in_arr'=>[1,10],
            'status'=>3,
            'msg_notbetween_else'=>'指定的状态值在列表notbetween'
        ]));


    }

    public function testpass()
    {
        $this->htemplate->addCustomTags(['html']);
        $this->assertRegExp('/{{include file="common\/footer"}}/',$this->htemplate->fetch('pass_tag.html',[
        ]));
    }

    public function testdict()
    {
        $this->assertRegExp('/超级管理员/',$this->htemplate->fetch('dict_tag.html',[
            'roles'=>[1=>'超级管理员',2=>'营销人员',3=>'客服',],
            'roleId'=>1
        ]));

        $this->assertRegExp('/超级管理员,营销人员/',$this->htemplate->fetch('dict_tag.html',[
            'roles'=>[1=>'超级管理员',2=>'营销人员',3=>'客服',],
            'roleId'=>[1,2]
        ]));

        $this->assertRegExp('/超级管理员,营销人员,客服/',$this->htemplate->fetch('dict_tag.html',[
            'roles'=>[1=>'超级管理员',2=>'营销人员',3=>'客服',],
            'roleId'=>[1,2,3],
        ]));

        $this->assertRegExp('/超级管理员-营销人员-客服/',$this->htemplate->fetch('dict_tag.html',[
            'roles2'=>[1=>'超级管理员',2=>'营销人员',3=>'客服',],
            'roleId2'=>[1,2,3],
        ]));
    }











}
