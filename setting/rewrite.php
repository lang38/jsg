<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename rewrite.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 180963590 515 $
 */


$config["rewrite"]=array (
  'abs_path' => '/',
  'arg_separator' => '/',
  'gateway' => '',
  'mode' => '',
  'prepend_var_list' => 
  array (
    0 => 'mod',
    1 => 'code',
  ),
  'value_replace_list' => 
  array (
    'mod' => 
    array (
      'topic' => 'miniblog',
      'tag' => 'channels',
      'profile' => 'profiles',
      'member' => 'members',
      'plugin' => 'packages',
    ),
  ),
  'var_replace_list' => 
  array (
    'mod' => 
    array (
    ),
  ),
  'var_separator' => '-',
);
?>