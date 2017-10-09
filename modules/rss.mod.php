<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename rss.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1681643744 2348 $
 */




if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}

class ModuleObject extends MasterObject
{
	function ModuleObject($config)
	{
		$this->MasterObject($config);
		$this->Execute();
	}

	function Execute()
	{
		$this->Picdisplay();
	}

	public function Pic()
	{
		$page = $_GET['page'] ? (int)$_GET['page'] : 0;
		if($page){$limit='40,1000';}else{$limit=40;}
        $r=jlogic('image')->get(array('where'=>'tid > 0','order'=>'id DESC','limit'=>$limit));
        foreach($r['list'] as $value){
            $value['pics'] = topic_image($value['id'], 'small', 0);
			$value['pico'] = topic_image($value['id'], 'default', 0);
			$value['link'] = $this->Config[site_url].'/index.php?mod=topic&amp;code='.$value['tid'];
			$topic_list[] = $value;
        }
		$rss = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
		$rss .= "<rss version=\"2.0\" xmlns:media=\"http:/"."/search.yahoo.com/mrss/\" xmlns:atom=\"http:/"."/www.w3.org/2005/Atom\">\r\n";
		$rss .= "<channel>\r\n";
		$rss .= "<title>photo</title>\r\n";
		$rss .= "<link>{$this->Config[site_url]}</link>\r\n";
		$rss .= "<description>photo</description>\r\n";
		$rss .= "<language>zh_CN</language>\r\n";
		$rss .= "<pubDate>".Date('Y-m-d H:i:s', time())."</pubDate>\r\n";
		$rss .= "<atom:link href=\"{$this->Config[site_url]}/index.php?mod=rss\" rel=\"self\" type=\"application/rss+xml\" />\r\n";
		$rss .= "<atom:link rel=\"next\" href=\"{$this->Config[site_url]}/index.php?mod=rss&amp;page=2\" />\r\n";
		foreach($topic_list as $val) {
			$val['name'] = array_iconv($GLOBALS['_J']['charset'],'utf-8',$val['name']);
			$rss .= "<item>\r\n";
			$rss .= "<title><![CDATA[{$val['name']}]]></title>\r\n";
			$rss .= "<link>{$val['link']}</link>\r\n";
			$rss .= "<media:thumbnail url=\"{$val['pics']}\"/>\r\n";
			$rss .= "<media:content url=\"{$val['pico']}\"/>\r\n";
			$rss .= "<guid isPermaLink=\"false\">{$val['link']}</guid>\r\n";
			$rss .= "</item>\r\n";
		}
		$rss .= "</channel>\r\n</rss>";
		return $rss;
	}

	public function Picdisplay()
	{
		header("Content-type: application/xml;charset=utf-8");
		echo $this->Pic();
		exit;
	}

}
?>
