<?php

/**
 * 已兼容php7
 * 注：本邮件类都是经过我测试成功了的，如果大家发送邮件的时候遇到了失败的问题，请从以下几点排查：
 * 1. 用户名和密码是否正确；
 * 2. 检查邮箱设置是否启用了smtp服务；
 * 3. 是否是php环境的问题导致；
 * 4. 将26行的$smtp->debug = false改为true，可以显示错误信息，然后可以复制报错信息到网上搜一下错误的原因；
 * 5. 如果还是不能解决，可以访问：http://www.daixiaorui.com/read/16.html#viewpl 
 *    下面的评论中，可能有你要找的答案。
 *
 *
 * Last update time:2017/06
 * UPDATE:
 * 1、替换了高版本不支持的写法，如ereg、ereg_replace.
 * 2、将 var 改为 public/private等.
 * 3、使其兼容php7.
 * 
 */
/*
  记一发不成功原因
  https://blog.csdn.net/u011270458/article/details/80463328
  Cannot connenct to relay host smtp.163.com (php邮件发送失败)：原因是阿里云禁用了25端口
  网上全部都说是服务器php.ini 配置的问题
  检查方法如下：找到php.ini，查看两个参数，一个是allow_url_fopen，这个参数要设置成on；
  另一个是disable_functions，如果这个参数后面出现了fsock,fsockopen，则需要把这两个函数名去掉，完成后重启apache。

  不过这里不是这个原因导致的
  因为这里的错误是，Error: Connection timed out (110)，是连接超时，说明fsock方法是可用的，只是连不上邮件服务器；
  而前面说的服务器配置问题的错误会是Error: ()，空，说明fsock方法不可用。按前面说的检查方法检查后发现配置的没问题，说明不是这个原因导致的。

  解决办法：那连接超时是什么原因导致的呢，一般我们配置的smtp服务器端口都是25，不过有的服务器或空间提供商把25端口给禁用了，
  比如阿里云就给禁用了，这个可以找相应的提供商确认一下。如果真是禁用了25端口，可以采用465端口，这个端口很多主流的邮件服务商像网易邮箱、QQ邮箱、
  阿里云邮箱也都支持，采用了465端口，织梦后台需要如下这么配置，注意，smtp服务器地址前面一定要加上ssl://，否则还是不可用。
 */

require_once "Smtp.class.php";

class Email {

    private $server = "smtp.163.com"; //SMTP服务器  ssl://smtp.163.com
    private $port = 25; //SMTP服务器端口  465
    private $fromUser = "boydearea@163.com"; //SMTP服务器的用户邮箱
    private $account = "boydearea@163.com"; //SMTP服务器的用户帐号，注：部分邮箱只需@前面的用户名
    private $password = "586wbl"; //SMTP服务器的用户密码

    /**
     * 向指定邮箱发送email，注意，发送的内容可能引起发送失败
     * @param str $toUser 发送给谁
     * @param str $title 邮件标题
     * @param str $content 邮件内容
     * @param str $mailtype 邮件格式（HTML/TXT）
     */

    public function sendEmail($toUser, $title, $content, $mailtype = "HTML") {
        $smtp = new Smtp($this->server, $this->port, true, $this->account, $this->password); //这里面的一个true是表示使用身份验证,否则不使用身份验证.
        $smtp->debug = true; //是否显示发送的调试信息
        $state = $smtp->sendmail($toUser, $this->fromUser, $title, $content, $mailtype);
        return $state;
    }

}
