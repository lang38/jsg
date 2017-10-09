<?php
 /* save by 19 127.0.0.1 2013-07-09 15:12:32 */

 $config['left_navigation'] = array (
  'app' =>
  array (
    0 =>
    array (
      'order' => '1',
      'name' => '有奖转发',
      'code' => 'reward',
      'url' => 'index.php?mod=reward',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/reward_icon.jpg',
    ),
    1 =>
    array (
      'order' => '1',
      'name' => '大屏幕',
      'code' => 'control',
      'url' => 'index.php?mod=wall&code=control',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/control_icon.jpg',
    ),
    2 =>
    array (
      'order' => '1',
      'name' => '附件文档',
      'code' => 'attach',
      'url' => 'index.php?mod=attach#myatt',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/attach_icon.jpg',
    ),
    3 =>
    array (
      'order' => '1',
      'name' => '图片墙',
      'code' => 'photo',
      'url' => 'index.php?mod=topic&code=photo',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/photo_icon.jpg',
    ),
    4 =>
    array (
      'order' => '1',
      'name' => '访谈',
      'code' => 'talk',
      'url' => 'index.php?mod=talk',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/talk_icon.jpg',
    ),
    5 =>
    array (
      'order' => '1',
      'name' => '投票',
      'code' => 'vote',
      'url' => 'index.php?mod=vote',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/vote_icon.jpg',
    ),
    6 =>
    array (
      'order' => '1',
      'name' => '活动',
      'code' => 'event',
      'url' => 'index.php?mod=event',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/event_icon.jpg',
    ),
    7 =>
    array (
      'order' => '1',
      'name' => '直播',
      'code' => 'live',
      'url' => 'index.php?mod=live',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/live_icon.jpg',
    ),
  ),
  'myapp' =>
  array (
    0 =>
    array (
      'order' => '1',
      'name' => 'CMS文章',
      'code' => 'cms',
      'url' => 'index.php?mod=cms',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => '',
    ),
    1 =>
    array (
      'order' => '1',
      'name' => '官方推荐',
      'code' => 'recd',
      'url' => 'index.php?mod=topic&code=recd',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/recd_icon.jpg',
    ),
    2 =>
    array (
      'code' => 'contest',
      'name' => '摄影比赛',
      'order' => '1',
      'url' => 'index.php?mod=contest',
      'avaliable' => 1,
    ),
    3 =>
    array (
      'order' => '1',
      'name' => '最新赞',
      'code' => 'newdigout',
      'url' => 'index.php?mod=topic&code=topicnew&orderby=dig',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/newdigout_icon.jpg',
    ),
    4 =>
    array (
      'order' => '1',
      'name' => '我的频道',
      'code' => 'mychannel',
      'url' => 'index.php?mod=topic&code=channel',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/mychannelb_icon.jpg',
    ),
    5 =>
    array (
      'order' => '1',
      'name' => '单位目录',
      'code' => 'listcompany',
      'url' => 'index.php?mod=company&code=list',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => '',
    ),
    6 =>
    array (
      'order' => '1',
      'name' => '我的单位',
      'code' => 'company',
      'url' => 'index.php?mod=company',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => '',
    ),
    7 =>
    array (
      'order' => '1',
      'name' => '我的微群',
      'code' => 'mygroup',
      'url' => 'index.php?mod=topic&code=qun',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/mygroup_icon.jpg',
    ),
    8 =>
    array (
      'order' => '1',
      'name' => '我的部门',
      'code' => 'department',
      'url' => 'index.php?mod=department',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => '',
    ),
  ),
  'mine' =>
  array (
    0 =>
    array (
      'order' => '1',
      'name' => '我的首页',
      'code' => 'myhome',
      'url' => 'index.php?mod=topic&code=myhome',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/myhome_icon.jpg',
    ),
    1 =>
    array (
      'order' => '1',
      'name' => '我的收藏',
      'code' => 'myfav',
      'url' => 'index.php?mod=topic_favorite',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/myfav_icon.jpg',
    ),
    2 =>
    array (
      'order' => '1',
      'name' => '我的私信',
      'code' => 'mypm',
      'url' => 'index.php?mod=pm&code=list',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/mypm_icon.jpg',
    ),
    3 =>
    array (
      'order' => '1',
      'name' => '我赞的',
      'code' => 'mydigout',
      'url' => 'index.php?mod=%uid%&type=mydigout',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/mydigout_icon.jpg',
    ),
    4 =>
    array (
      'order' => '1',
      'name' => '个人主页',
      'code' => 'myweibo',
      'url' => 'index.php?mod=%uid%',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => './images/lefticon/myweibo_icon.jpg',
    ),
    5 =>
    array (
      'order' => '1',
      'name' => '我的相册',
      'code' => 'album',
      'url' => 'index.php?mod=album',
      'target' => '_parent',
      'avaliable' => 1,
      'icon' => '',
    ),
  ),
);
?>