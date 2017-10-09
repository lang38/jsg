<?php
/**
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Filename shop.mod.php $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2014 809848101 5958 $
 */


if(!defined('IN_JISHIGOU')) {
    exit('invalid request');
}
switch(jget('code')){
	case 'getshop':
		Getshop();break;
	case 'delshop':
		Delshop();break;
	default:
		main();break;
}
function Getshop(){
	$urlstr = $_POST['url'];
	$shopdata = array();
	if($urlstr){
		preg_match_all('~(?:https?\:\/\/)(?:[A-Za-z0-9_\-]+\.)+[A-Za-z0-9]{2,4}(?:\/[\w\d\/=\?%\-\&_\~`@\[\]\:\+\#]*(?:[^<>\'\"\n\r\t\s])*)?~',$urlstr,$match);
		if($match[0]){
			if(false!==strpos($match[0][0],"taobao.com")){				if(false!==strpos($match[0][0],"item.taobao.com/item.htm")){
										$tbcontent = dfopen($match[0][0]);
					$tbcontent = array_iconv('gbk',$GLOBALS['_J']['config']['charset'],$tbcontent);
					$shopdata['goods'] = _cut($tbcontent,'<title>','</title>');
					$shopdata['price'] = _cut($tbcontent,'<strong id="J_StrPrice" >','</strong>');
					if(empty($shopdata['price'])){
						$shopdata['price'] = _cut($tbcontent,'<strong class="tb-price" id="J_SpanLimitProm">','</strong>');
					}elseif(false!==strpos($shopdata['price'],"tb-rmb-num")){
						$shopdata['price'] = _cut($shopdata['price'],'<em class="tb-rmb-num">','</em>');
					}
					$p = array('pic_url' => $url);
					$tbimage = _cut($tbcontent,'<img id="J_ImgBooth" src="','"  data-hasZoom="700"');
					if(empty($tbimage)){
						$tbimage = _cut($tbcontent,'<img id="J_ImgBooth" data-src="','"  data-hasZoom="700"');
					}
					$rets = jlogic('image')->upload(array('pic_url' => $tbimage));
					if($rets['code']<0 && $rets['error']) {
						$shopdata['imageid'] = 0;
						$shopdata['image'] = $shopdata['imaged'] = $tbimage;
					}else{
						$shopdata['imageid'] = $rets['id'];
						$shopdata['image'] = $rets['src'];
						$shopdata['imaged'] = str_replace('_s.jpg','_o.jpg',$shopdata['image']);
					}
					preg_match_all("/id=[0-9]+/",$match[0][0],$matchid);
					$shopdata['url'] = 'http:/'.'/item.taobao.com/item.htm?'.$matchid[0][0];
					$tbseller = _cut($tbcontent,'<a class="hCard fn" ','<span class="J_WangWang" data-nick=');
					$shopdata['surl'] = _cut($tbseller,' href="','">');
					$shopdata['seller'] = '〖淘宝网〗'._cut($tbseller,'title="','" href=');					
					unset($tbcontent, $tbimage, $tbseller, $rets);
				}else{
					echo '0|||<font color="red">地址错误，不是有效的商品地址！</font>';exit;
				}
			}elseif(false!==strpos($match[0][0],"tmall.com")){				if(false!==strpos($match[0][0],"detail.tmall.com/item.htm") || false!==strpos($match[0][0],"item.tmall.com/item.htm")){
										$tmcontent = dfopen($match[0][0]);
					$tmcontent = array_iconv('gbk',$GLOBALS['_J']['config']['charset'],$tmcontent);
					$shopdata['goods'] = _cut($tmcontent,'<title>','</title>');
					$shopdata['price'] = _cut($tmcontent,"'defaultItemPrice':'","',");
					if(empty($shopdata['price'])){
						$shopdata['price'] = _cut($tmcontent,'"defaultItemPrice":"','","');
					}
					$p = array('pic_url' => $url);
					$tmimage = _cut($tmcontent,'<span id="J_ImgBooth" src="','"></span>');
					if(empty($tmimage)){
						$tmimage = _cut($tmcontent,'<img id="J_ImgBooth" src="','"');
					}
					if(empty($tmimage)){
						$tmimage = _cut($tmcontent,'<span id="J_ZoomHook" src="','"></span>');
					}
					$rets = jlogic('image')->upload(array('pic_url' => $tmimage));
					if($rets['code']<0 && $rets['error']) {
						$shopdata['imageid'] = 0;
						$shopdata['image'] = $shopdata['imaged'] = $tmimage;
					}else{
						$shopdata['imageid'] = $rets['id'];
						$shopdata['image'] = $rets['src'];
						$shopdata['imaged'] = str_replace('_s.jpg','_o.jpg',$shopdata['image']);
					}
					preg_match_all("/id=[0-9]+/",$match[0][0],$matchid);
					$shopdata['url'] = 'http:/'.'/detail.tmall.com/item.htm?'.$matchid[0][0];
					$tmseller = _cut($tmcontent,'<div id="J_shopSearchData" ','</div>');
					$shopdata['surl'] = _cut($tmseller,"data-shopUrl='","' data-shopName");
					$shopdata['seller'] = '〖天猫〗'._cut($tmseller,"data-shopName='","'>");
					unset($tmcontent, $tmimage, $tmseller, $rets);
				}else{
					echo '0|||<font color="red">地址错误，不是有效的商品地址！</font>';exit;
				}
			}else{
								echo '0|||<font color="red">系统目前只支持淘宝与天猫，请重新输入！</font>';exit;
			}
			$data = array(
				'tid' => 0,
				'uid' => MEMBER_ID,
				'username' => MEMBER_NICKNAME,
				'goods' => $shopdata['goods'],
				'seller' => $shopdata['seller'],
				'imageid' => $shopdata['imageid'],
				'image' => $shopdata['imaged'],
				'url' => $shopdata['url'],
				'surl' => $shopdata['surl'],
				'price' => $shopdata['price'],
				'dateline' => time()
			);
			$shopid = DB::insert('topic_shop', $data, true);
			$str = '<table><tr><td colspan="2"><b>'.$data['goods'].'</b></td></tr><tr><td rowspan="3" width="140" height="140"><img src="'.$shopdata['image'].'" width="120" height="120"></td><td width="160"><b>价格</b>：￥'.$data['price'].'元</td></tr><tr><td><b>卖家</b>：'.$data['seller'].'</td></tr><tr><td align="center"><input type="button" name="del" value="删 除" class="u-btn" onclick="del_shop('.$shopid.');"/></td></tr></table>';
			echo $shopid.'|||'.$str;
		}else{
			echo '0|||<font color="red">商品地址输入错误，请重新输入！</font>';
		}
	}
}
function Delshop(){
	$shopid = jget('shopid');
	$imageid = DB::result_first("SELECT `imageid` FROM ".DB::table('topic_shop')." WHERE id = '$shopid'");
	if($imageid){
		DB::query("DELETE FROM ".DB::table('topic_image')." WHERE id = '$imageid'");	}
	DB::query("DELETE FROM ".DB::table('topic_shop')." WHERE id = '$shopid'");
}
function main(){
	response_text("正在建设中......");
}
function _cut($file,$from,$end){
	$message=explode($from,$file);
	$message=explode($end,$message[1]);
	return  $message[0];
}
?>