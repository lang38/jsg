<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename member.logic.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2013 901785772 2164 $
 */




class MemberLogic
{

	function MemberLogic()
	{

	}

		function getMemberList($param)
	{
				$member_list = array();

		$where_sql = " 1 ";

				$order_sql = " regdate DESC ";

		$max_id = intval($param['max_id']);

		$limit = intval($param['limit']);
		if (empty($limit)) {
			$limit = 20;
		}
		$nickname = trim($param['nickname']);
		if (!empty($nickname)) {
			$nickname= get_safe_code($nickname);
						$where_sql .= " AND ".build_like_query("nickname", $nickname)." ";
		}

		$sql = "select count(*) from `".TABLE_PREFIX."members`  WHERE {$where_sql}";
		$total_record = DB::result_first($sql);
		if ($total_record > 0) {
			if ($max_id > 0) {
				$where_sql .= " AND uid < {$max_id} ";
			}
			$sql = "select `uid`,`ucuid`,`username`,`nickname`,`face_url`,`face`,`fans_count`,`topic_count`,`province`,`city`,`validate`
					from `".TABLE_PREFIX."members`
					WHERE {$where_sql}
					ORDER BY {$order_sql}
					LIMIT {$limit} ";
			$query = DB::query($sql);
			$uids = array();
			while ($row = DB::fetch($query)) {
				$row['face'] = face_get($row);
				$member_list[] = $row;
				$uids[$row['uid']] = $row['uid'];
			}

			if($uids && MEMBER_ID>0) {
												$friendships = array(-1=>1, 0=>0, 1=>2, 2=>0, 3=>4);
				$_tmp_arr = buddy_follow_html($member_list);
				foreach($_tmp_arr as $k=>$row) {
					$member_list[$k]['friendship'] = $friendships[$row['is_follow_relation']];
				}
				unset($_tmp_arr);
			}
			$member_list = array_values($member_list);
			$tmp_ary = $member_list;
			$tmp = array_pop($tmp_ary);
			$max_id = $tmp['uid'];
			$ret = array(
				'member_list' => $member_list,
				'total_record' => $total_record,
				'list_count' => count($member_list),
				'max_id' => $max_id,
			);
			return $ret;
		}
		return 400;
	}
}


?>