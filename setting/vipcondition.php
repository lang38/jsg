<?php 
 /* save by 1 127.0.0.1 2013-02-25 15:22:38 */
				
 $config['vipcondition'] = array (
  'email' => 
  array (
    'key' => 'email',
    'forward' => 'index.php?mod=settings&code=email_check',
    'enable' => 1,
    'message' => '本站需通过Email验证才能申请V认证。',
  ),
  'topic_num' => 
  array (
    'key' => 'topic_num',
    'forward' => 'index.php?mod=topic',
    'enable' => 100,
    'message' => '本站需发100条内容才能申请V认证。',
  ),
  'face' => 
  array (
    'key' => 'face',
    'forward' => 'index.php?mod=settings&code=face',
    'enable' => 1,
    'message' => '本站需上传头像才能申请V认证。',
  ),
  'fans_num' => 
  array (
    'key' => 'fans_num',
    'forward' => 'index.php?mod=profile&code=invite',
    'enable' => 10,
    'message' => '本站需10位粉丝才能申请V认证。',
  ),
  'city' => 
  array (
    'key' => 'city',
    'forward' => 'index.php?mod=settings',
    'enable' => 1,
    'message' => '本站需设置所在区域才能申请V认证。',
  ),
); 
?>