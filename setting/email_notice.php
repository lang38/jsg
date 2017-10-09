<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename email_notice.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1246084104 478 $
 */



$config['email_notice']=array (
  'is_email' => '0',
  'at' =>
  array (
    'content' => '你的好友对你发了一条@信息。复制连接查看 http://您记事狗微博的地址/index.php?mod=at',
  ),
  'pm' =>
  array (
    'content' => '你的好友发了一条站内短信给你。复制连接查看 http://您记事狗微博的地址/index.php?mod=pm&code=list',
  ),
  'reply' =>
  array (
    'content' => '你的好友评论了你的微博。复制连接查看 http://您记事狗微博的地址/index.php?mod=comment&code=inbox',
  ),
);
?>