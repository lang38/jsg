<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename misc.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 580142184 436 $
 */




class MiscLogic
{
	var $Config;
	var $DatabaseHandler;
	var $OtherLogic;

	function MiscLogic()
	{
		$this->Config = jconf::get();
		$this->DatabaseHandler = &Obj::registry('DatabaseHandler');
		Load::logic('other');
		$this->OtherLogic = new OtherLogic();
	}

	function getSignTag()
	{
		$tags = $this->OtherLogic->getSignTag();
		return $tags;
	}
}
?>
