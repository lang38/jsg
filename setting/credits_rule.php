<?php 
 /* save by 1 127.0.0.1 2013-12-03 17:32:13 */
				
 $config['credits_rule'] = array (
  'topic' => 
  array (
    'rid' => '1',
    'rulename' => '发布原创微博',
    'action' => 'topic',
    'cycletype' => '1',
    'extcredits2' => '2',
    'extcredits3' => '999',
  ),
  'reply' => 
  array (
    'rid' => '2',
    'rulename' => '评论或转发微博',
    'action' => 'reply',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '1',
  ),
  'buddy' => 
  array (
    'rid' => '3',
    'rulename' => '关注好友',
    'action' => 'buddy',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '1',
  ),
  'register' => 
  array (
    'rid' => '4',
    'rulename' => '邀请注册',
    'action' => 'register',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '10',
  ),
  'login' => 
  array (
    'rid' => '6',
    'rulename' => '每天登录',
    'action' => 'login',
    'cycletype' => '1',
    'rewardnum' => '1',
    'extcredits2' => '2',
  ),
  'pm' => 
  array (
    'rid' => '7',
    'rulename' => '发送短消息',
    'action' => 'pm',
    'cycletype' => '1',
    'rewardnum' => '1',
    'extcredits2' => '1',
  ),
  'face' => 
  array (
    'rid' => '8',
    'rulename' => '设置头像',
    'action' => 'face',
    'rewardnum' => '1',
    'extcredits2' => '10',
  ),
  'vip' => 
  array (
    'rid' => '9',
    'rulename' => 'VIP认证',
    'action' => 'vip',
    'rewardnum' => '1',
    'extcredits2' => '20',
  ),
  '_T84202031' => 
  array (
    'rid' => '10',
    'rulename' => '发布指定话题',
    'action' => '_T84202031',
    'cycletype' => '1',
    'rewardnum' => '2',
    'extcredits2' => '5',
    'related' => '新人报到',
  ),
  '_U-2012344970' => 
  array (
    'rid' => '11',
    'rulename' => '关注指定用户',
    'action' => '_U-2012344970',
    'rewardnum' => '1',
    'extcredits2' => '5',
    'related' => 'admin',
  ),
  'topic_del' => 
  array (
    'rid' => '12',
    'rulename' => '删除微博',
    'action' => 'topic_del',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
  'buddy_del' => 
  array (
    'rid' => '13',
    'rulename' => '取消关注好友',
    'action' => 'buddy_del',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
  'vote_add' => 
  array (
    'rid' => '17',
    'rulename' => '发起投票',
    'action' => 'vote_add',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '2',
  ),
  'vote_del' => 
  array (
    'rid' => '18',
    'rulename' => '删除投票',
    'action' => 'vote_del',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
  'attach_down' => 
  array (
    'rid' => '21',
    'rulename' => '下载附件',
    'action' => 'attach_down',
    'cycletype' => '4',
    'extcredits2' => '1',
  ),
  'down_my_attach' => 
  array (
    'rid' => '22',
    'rulename' => '附件被下载',
    'action' => 'down_my_attach',
    'cycletype' => '4',
    'extcredits2' => '1',
  ),
  'topic_dig' => 
  array (
    'rid' => '23',
    'rulename' => '赞微博',
    'action' => 'topic_dig',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '1',
  ),
  'my_dig' => 
  array (
    'rid' => '24',
    'rulename' => '微博被赞',
    'action' => 'my_dig',
    'cycletype' => '4',
    'extcredits2' => '1',
  ),
  'recommend' => 
  array (
    'rid' => '25',
    'rulename' => '微博被推荐',
    'action' => 'recommend',
    'cycletype' => '4',
    'extcredits2' => '1',
  ),
  '_D1685985038' => 
  array (
    'rid' => '43',
    'rulename' => '删除频道微博',
    'action' => '_D1685985038',
    'cycletype' => '1',
    'extcredits2' => '-20',
    'related' => '建议中心',
  ),
  '_C1685985038' => 
  array (
    'rid' => '44',
    'rulename' => '发到指定频道',
    'action' => '_C1685985038',
    'cycletype' => '4',
    'extcredits2' => '10',
    'extcredits4' => '5',
    'related' => '建议中心',
  ),
  '_C450215437' => 
  array (
    'rid' => '45',
    'rulename' => '发到指定频道',
    'action' => '_C450215437',
    'cycletype' => '1',
    'extcredits2' => '10',
    'related' => '提问中心',
  ),
  'vote_vote' => 
  array (
    'rid' => '46',
    'rulename' => '参与投票',
    'action' => 'vote_vote',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '1',
  ),
  'event_app' => 
  array (
    'rid' => '49',
    'rulename' => '参与活动',
    'action' => 'event_app',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '2',
  ),
  'event_cancel' => 
  array (
    'rid' => '50',
    'rulename' => '退出活动',
    'action' => 'event_cancel',
    'cycletype' => '4',
    'extcredits2' => '-5',
  ),
  'convert' => 
  array (
    'rid' => '53',
    'rulename' => '兑换商品',
    'action' => 'convert',
    'cycletype' => '4',
    'extcredits2' => '1',
  ),
  'unconvert' => 
  array (
    'rid' => '54',
    'rulename' => '取消商品兑换',
    'action' => 'unconvert',
    'cycletype' => '4',
    'extcredits2' => '1',
  ),
  '_T-1007322239' => 
  array (
    'rid' => '56',
    'rulename' => '发布指定话题',
    'action' => '_T-1007322239',
    'cycletype' => '1',
    'rewardnum' => '10',
    'extcredits2' => '5',
    'extcredits4' => '5',
    'related' => '抓虫',
  ),
); 
?>