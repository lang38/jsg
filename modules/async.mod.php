<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename async.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013-11-11 1427543368 1474 $
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
		switch ($this->Code){
			case 'wqueue':
				$this->wqueue();
				break;		
		}
	}
	function wqueue()
	{
		$do = $this->Get['do'];
		if ($do == 'run')
		{
			if ($this->Config['wqueue_enabled'])
			{
				$url = 'http:/'.'/'.$this->Config['wqueue']['host'].'/?name='.$this->Config['wqueue']['name'].'&opt=get&auth='.$this->Config['wqueue']['auth'];
				while (true)
				{
					$r = dfopen($url);
					if (!in_array($r, array('HTTPSQS_GET_END', 'HTTPSQS_ERROR')))
					{
						$data = unserialize(base64_decode($r));
						$data['datas']['content'] = base64_decode($data['datas']['content']);
						$r = jlogic('topic')->Add($data['datas'], $data['totid'], $data['imageid'], $data['attachid'], $data['from'], $data['type'], $data['uid'], $data['item'], $data['item_id'], true);
						if (DEBUG)
						{
							if (is_array($r) && $r['tid'])
							{
								echo 'publish success'."\n";
							}
							else
							{
								echo var_export($r, true)."\n";
							}
							ob_flush();
						}
					}
					else
					{
						sleep(1);
					}
				}
			}
			else
			{
				exit('weibo.queue.close');
			}
		}
		else
		{
			exit('weibo.action.no');
		}
	}
}

?>