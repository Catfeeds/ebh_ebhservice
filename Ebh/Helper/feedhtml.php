<?php
/**
 *处理动态feeds格式化后的html
 * @author echo
 */

/**
 * 图片排版
 * 组装图片 生成html
 */
if(!function_exists('getimagehtml')){
	function getimagehtml($images,$size,$showpath){
		$html = '<div class="image_box"> ';
		$style = '';
		foreach($images as $image){
			$showimg = $showpath.''.$image['path'];
			list($width,$height) = explode("_", $size);
			if($width>0 && $height>0 && count($images) >1){
				$style = "style=width:{$width}px;height:{$height}px;text-align:center;vertical-align:middle;display:table-cell;background:#fff;border:0";
			}else{
				$style = "style=text-align:center;vertical-align:middle;display:table-cell;background:#fff;border:0";
			}
			if($size == '0_0'){
				//如果图片被禁用
				if($image['status'] == 1){
					$showimg = 'http://static.ebanhui.com/sns/images/jin_650_350.png';
				}
				$html.="<div style='float:left;margin-right:4px;margin-bottom:4px'><a href='javascript:;' $style><img layer-img='".$showimg."' data-original='".$showimg."' class='lazy' /></a></div>";
			}else{
				if($image['status'] == 1){
					$showimg = 'http://static.ebanhui.com/sns/images/jin_'.$size.'.png';
					$html.="<div style='float:left;margin-right:4px;margin-bottom:4px'><a href='javascript:;' $style><img layer-img='".$showimg."' data-original='".$showimg."' class='lazy' /></a></div>";
				}else{
					$html.="<div style='float:left;margin-right:4px;margin-bottom:4px'><a href='javascript:;' $style><img layer-img='".$showimg."' data-original='".getthumb($showimg,$size)."' class='lazy' /></a></div>";
				}
			}
		}
		return $html."</div>";
	}
}

/**
 * 获取imagebox的html
 */
if(!function_exists('getimageboxhtml')){
	function getimageboxhtml($feedmessage){
		$gidarr = !empty($feedmessage['images']) ? explode(",", $feedmessage['images']) : array();
		$imagecount = count($gidarr);
		$upconfig = Ebh()->config->get('upconfig');
		$showpath = $upconfig['snspic']['showpath'];
		$imgmodel = getmodel('SnsImage');
		$images = $imgmodel->getimgs($gidarr);
		$imgboxhtml = '';
		if(empty($images)){
			return $imgboxhtml;
		}
		if($imagecount==1){
			//没有650_350尺寸则采用原图
			$imgboxhtml = strpos($images[0]['sizes'], '886_477') !== false ? getimagehtml($images,'886_477',$showpath) : getimagehtml($images,'0_0',$showpath);
		}elseif($imagecount==2||$imagecount==4||$imagecount==8){
		    //兼容老写法
            $imgboxhtml = strpos($images[0]['sizes'], '435_230') !== false ? getimagehtml($images,'435_230',$showpath) : getimagehtml($images,'320_170',$showpath);

		}else{
            //兼容老写法
            $imgboxhtml = strpos($images[0]['sizes'], '280_146') !== false ? getimagehtml($images,'280_146',$showpath) : getimagehtml($images,'210_110',$showpath);
		}
		return $imgboxhtml;
	}
}


/**
 * 获取评论的li
 */
if(!function_exists("getreplylihtml")){
	function getreplylihtml($reply,$uid,$style=""){
		$style = 'style="'.$style.'"';
		$html = '';
		$html.='
				<li class="jgrety"'.$style.' data-cid="'.$reply['cid'].'" data-fromuid="'.$reply['fromuid'].'">
				<div class="gkejrg">
				<!--<a href="'.geturl("/sns/feeds/".$reply['message']['fromuser']['uid'].".html").'"  class="regewgr"><img  src="'.getavater($reply['message']['fromuser'],'40_40').'" /></a>-->
				<div class="fldot">
				<p class="dfegtr">';
		if(($reply['fromuid']!=$reply['touid'])&&($reply['pcid']>0) ){
			$html.='<a href="'.geturl("/sns/feeds/".$reply['message']['fromuser']['uid'].".html").'" >
					<span style="color:#ed8563;">'.$reply['message']['fromuser']['realname'].'</span>
					</a> 
					回复 
					<a href="'.geturl("/sns/feeds/".$reply['message']['touser']['uid'].".html").'" >
					<span style="color:#ed8563;">'.$reply['message']['touser']['realname'].'</span>
					</a>：';
		}else{
			$html.='<a href="'.geturl("/sns/feeds/".$reply['message']['fromuser']['uid'].".html").'">
					<span style="color:#ed8563;">'.$reply['message']['fromuser']['realname'].'</span>
					</a> ：'	;
		 }
							
		 $html.=emotionreplace($reply['message']['content']).'
				</p>
				<p class="ryasc">'.gettimestr($reply['dateline']).'</p>
				</div>
				</div>';
		if($reply['fromuid']==$uid){
			$html.='<a href="javascript:;" class="sfahnt replydel">删除</a>';
		}


		$html.='<a href="javascript:;" class="hustst reply">回复</a>
				</li>';

		return $html;
	}
}

/**
 * 获取一个feedli
 */
if(!function_exists('getfeedlihtml')){
	function getfeedlihtml($feed,$uid){
		$datadel = !empty($feed['refer_top_delete'])?1:0;
		$nickname = !empty($feed['realname'])?$feed['realname']:$feed['username'];
		
		if(isset($feed['message']['refer'])){
			$title = empty($feed['message']['refer']['title'])?"":$feed['message']['refer']['title'];
			$tutor = empty($feed['message']['refer']['tutor'])?"":$feed['message']['refer']['tutor'];
		}else{
			$title = empty($feed['message']['title'])?"":$feed['message']['title'];
			$tutor = empty($feed['message']['tutor'])?"":$feed['message']['tutor'];
		}
		
		$html = '';
		$html.='<li class="liert" data-fid="'.$feed['fid'].'" data-uid="'.$feed['fromuid'].'" data-del="'.$datadel.'">
				<div class="niutrd">';
		if($feed['fromuid']==$uid){
			$html.='<button title="删除" class="kstgvd closefeedbtn" style="display:none">
					<span class="none">╳</span>
					</button>';
		}
		$html.='<a href="'.geturl("/sns/feeds/".$feed['fromuid'].".html").'" class="regewgr">
				<img style="" src="'.getavater($feed,"50_50").'" /></a>
				<div class="fldot">
				<h2 class="guewt"><a href="'.geturl("/sns/feeds/".$feed['fromuid'].".html").'">'.$nickname.'</a></h2>
				<p class="ryasc">'.gettimestr($feed['dateline']).'</p>
				</div>
				</div>';
		if($feed['category']==1){//心情
			$html.='<div class="ksneit">
					<h2 class="lertsd">'.emotionreplace($feed['message']['content']).'</h2>'
							.getimageboxhtml($feed['message'])
					.'</div>';
			
			//附加额外信息
			if(isset($feed['message']['extmsg'])){
				$fromuid = $feed['message']['extmsg']['fromuid'];
				$typestr = $feed['message']['extmsg']['typestr'];
				$name = $feed['message']['extmsg']['name'];
				
				$html .= '<div class="ksneit">
							  <span>转自<a href="'.geturl("/sns/feeds/".$fromuid.'.html').'">'.$name.'</a>的'.$typestr.'</span>
							  <h2 style="font-weight:bold;" class="lertsd">'.$feed['message']['extmsg']['title'].'</h2>';
				$html .= '<p style="margin-top:10px;font-size:12px">'.$feed['message']['extmsg']['contents'].'</p></div>';
			}
		}elseif($feed['category']==2){//日志
			if(isset($feed['message']['referuser'])){
				$bl_name = $feed['message']['referuser']['realname'];
				$bl_uid = $feed['message']['referuser']['uid'];
			}else{
				$bl_name = !empty($feed['realname'])?$feed['realname']:$feed['username'];
				$bl_uid = $feed['fromuid'];
			}
			$bl_bid = $feed['toid'];
			$bl_content = isset($feed['message']['content']) ? $feed['message']['content'] : '';
			
			$html.='<div class="ksneit">
					<h2 style="" class="lertsd">'.emotionreplace($bl_content).'</h2>
					<h2 class="lertsd"><span>
					<a  class="kdtjdd" href="'.geturl("/sns/feeds/".$bl_uid.'.html').'">'.$bl_name.'</a></span>
					的日志 <a href="'.geturl('sns/blog/detail').'?bid='.$bl_bid.'" style="color:#2696f0">'.$title.'</a></h2>
					<p class="kgregd">'.$tutor.'</p>
					'.getimageboxhtml($feed['message'])
					.'</div>';
		}elseif($feed['category']==4){//转载的日志
			if(isset($feed['message']['refer'])){
				$zh_uid = $feed['message']['refer']['referuser']['uid'];
				$zh_bid = $feed['message']['refer']['referuser']['bid'];
				$zh_msg = $feed['message']['refer'];
				$zh_realname = $feed['message']['refer']['referuser']['realname'];
				$zh_content = $feed['message']['content'];
			}else{
				$zh_uid = $feed['message']['referuser']['uid'];
				$zh_bid = $feed['message']['referuser']['bid'];
				$zh_msg = $feed['message'];
				$zh_realname = $feed['message']['referuser']['realname'];
				$zh_content = isset($feed['message']['content']) ? $feed['message']['content'] : '';
			}
			$html.='<div class="ksneit">
						<h2 style="" class="lertsd">'.emotionreplace($zh_content).'</h2>
						<h2 class="lertsd"><span>
						<a  class="kdtjdd" href="'.geturl("/sns/feeds/".$zh_uid.'.html').'">'.$zh_realname.'</a></span>
						 的日志 <a href="'.geturl('sns/blog/detail').'?bid='.$zh_bid.'"  style="color:#2696f0">'.$title.'</a></h2>
						<p class="kgregd">'.$tutor.'</p>'
											.getimageboxhtml($zh_msg)
											.'</div>';
		}
		
			$html.='	
				<div class="qrtuirth">
					<a class="item reply replynum" href="javascript:;"><i class="ui-icons comment"></i>评论（'.$feed['cmcount'].'）</a>		
					<a class="item transfer" href="javascript:;"><i class="ui-icons forward"></i>转发（'.$feed['zhcount'].'）</a>
					<a class="item upclick" href="javascript:;"><i class="ui-icons praise"></i>赞（'.$feed['upcount'].'）</a>
				</div>';
			
			$html.='<div class="piagne" style="display:block">
						<ol class="reply_list">';
			
			if(!empty($feed['replys'])){
				foreach($feed['replys'] as $key=> $reply){
					if($key == 0){
						$html.=getreplylihtml($reply,$uid,"");
					}else{
						$html.=getreplylihtml($reply,$uid);
					}
				}
			}
			$html.='</ol>';
			if(!empty($feed['replys']) && $feed['replycount']>10){
				$html.='<div class="weidgjr" style="text-align:left">
						<a href="javascript:;" class="getreplymore" style="margin-left:17px;color:#ed8563">查看更多评论</a>
						</div>';
			}		
			$html.='</div></li>';				
		//echo $html;
		//exit;
		return $html;
	}
}



/**
 * 获取一个转发的feedli
 */
if(!function_exists('gettransferfeedlihtml')){
	function gettransferfeedlihtml($feed,$uid){
		$nickname = !empty($feed['realname'])?$feed['realname']:$feed['username'];
		$upcount = !empty($feed)?$feed['upcount']:$feed['upcount'];
		$cmcount = !empty($feed)?$feed['cmcount']:$feed['cmcount'];
		$zhcount = !empty($feed)?$feed['zhcount']:$feed['zhcount'];
		$datadel = !empty($feed['refer_top_delete'])?1:0;
		
		$html = '';
		$html.='<li class="liert" data-fid="'.$feed['fid'].'" data-uid="'.$feed['fromuid'].'" data-del="'.$datadel.'">
				<div class="niutrd">';
				
		if($feed['fromuid']==$uid){
			$html.='<button title="关闭" class="kstgvd closefeedbtn" style="display:none">
					<span class="none">╳</span>
					</button>';
		}
		$html.='<a href="'.geturl("/sns/feeds/".$feed['fromuid'].".html").'"  class="regewgr">
				<img style="" src="'.getavater($feed,"50_50").'" /></a>
				<div class="fldot">
				<h2 class="guewt"><a href="'.geturl("/sns/feeds/".$feed['fromuid'].".html").'">'.$nickname.'</a></h2>
				<p class="ryasc">'.gettimestr($feed['dateline']).'</p>
				</div>
				</div>';
		//转发引用顶级没有被删除 
		if($feed['refer_top_delete']==false){
				if($feed['category']==1){//心情
					$html.='<div class="ksneit">
						<h2 style="border-bottom:solid 1px #eee;padding-bottom:5px;" class="lertsd">'.emotionreplace($feed['message']['content']).'</h2>
						<h2 class="lertsd"><span class="kdtjdd"><a href="'.geturl("/sns/feeds/".$feed['message']['referuser']['uid'].'.html').'">'.$feed['message']['referuser']['realname'].'</a></span> : <span class="">'
											.emotionreplace($feed['message']['refer']['content']).'</span></h2>'
													.getimageboxhtml($feed['message']['refer'])
													.'</div>';
					
					//附加额外信息
					if(isset($feed['message']['refer']['extmsg'])){
						$fromuid = $feed['message']['refer']['extmsg']['fromuid'];
						$typestr = $feed['message']['refer']['extmsg']['typestr'];
						$name = $feed['message']['refer']['extmsg']['name'];
						$html .= '<div class="ksneit">
							  <span>转自<a href="'.geturl("/sns/feeds/".$fromuid.'.html').'" class="kdtjdd">'.$name.'</a>的'.$typestr.'</span>
							  <h2 style="font-weight:bold;" class="lertsd">'.$feed['message']['refer']['extmsg']['title'].'</h2>';
						$html .= '<p style="margin-top:10px;font-size:12px">'.$feed['message']['refer']['extmsg']['contents'].'</p></div>';
					}
				}elseif($feed['category']==2){//日志
					$html.='<div class="ksneit">
						<h2 style="border-bottom:solid 1px #eee;padding-bottom:5px;" class="lertsd">'.emotionreplace($feed['message']['content']).'</h2>
						<h2 class="lertsd"><span >
						<a  class="kdtjdd" href="'.geturl("/sns/feeds/".$feed['message']['referuser']['uid'].'.html').'">'.$feed['message']['referuser']['realname'].'</a></span>
						 的日志 <a href="'.geturl('sns/blog/detail').'?bid='.$feed['toid'].'" style="color:#2696f0">'.$feed['message']['refer']['title'].'</a></h2>
						<p class="kgregd">'.$feed['message']['refer']['tutor'].'</p>'
											.getimageboxhtml($feed['message']['refer'])
											.'</div>';
				}elseif($feed['category'] ==4){//转发的日志	
					$html.='<div class="ksneit">
						<h2 style="border-bottom:solid 1px #eee;padding-bottom:5px;" class="lertsd">'.emotionreplace($feed['message']['content']).'</h2>
						<h2 class="lertsd"><span >
						<a  class="kdtjdd" href="'.geturl("/sns/feeds/".$feed['message']['refer']['referuser']['uid'].'.html').'">'.$feed['message']['refer']['referuser']['realname'].'</a></span>
						 的日志 <a href="'.geturl('sns/blog/detail').'?bid='.$feed['message']['refer']['referuser']['bid'].'"  style="color:#2696f0">'.$feed['message']['refer']['title'].'</a></h2>
						<p class="kgregd">'.$feed['message']['refer']['tutor'].'</p>'
											.getimageboxhtml($feed['message']['refer'])
											.'</div>';
				}
		}else{
			//转发引用顶级被删除
			$html.='<div class="ksneit">
					<h2 class="lertsd">'.emotionreplace($feed['message']['content']).'</h2>
					<div class="lsidts">
					<div class="ryhdfds">
					<p class="laiwstn">抱歉，此动态已被作者删除。</p>
					</div>
					</div>
					</div>';
		}
		

		
		$html.='<div class="qrtuirth">
					<a class="item reply replynum" href="javascript:;"><i class="ui-icons comment"></i>评论（'.$cmcount.'）</a>		
					<a class="item transfer" href="javascript:;"><i class="ui-icons forward"></i>转发（'.$zhcount.'）</a>
					<a class="item upclick" href="javascript:;"><i class="ui-icons praise"></i>赞（'.$upcount.'）</a>
				</div>';
		
		$html.='<div class="piagne" style="display:block">
						<ol class="reply_list">';
		if(!empty($feed['replys'])){
			foreach($feed['replys'] as $key=> $reply){
				if($key == 0){
					$html.=getreplylihtml($reply,$uid,"border-top:solid 1px #e3e3e3");
				}else{
					$html.=getreplylihtml($reply,$uid);
				}
			}
		}
		$html.='</ol>';
		if(!empty($feed['replys']) && $feed['replycount']>10){
				$html.='<div class="weidgjr" style="text-align:left">
						<a href="javascript:;" class="getreplymore" style="margin-left:17px;color:#ed8563">查看更多评论</a>
						</div>';
		}
		
		$html.='</div></li>';
		//echo $html;
		//exit;
		return $html;
	} 
}

/**
 * 获取一个通知html li
 */
if(!function_exists('getnoticeslihtml')){
	function getnoticeslihtml($notice){
		$message = json_decode($notice['message'],1);
		if($notice['category'] == 2 || $notice['category'] == 4){
			$detailurl = "/sns/blog/detail.html?bid=".$notice['toid'];
		}else{
			$detailurl = geturl('feeds/view').'?fid='.$message['fid'];
		}
		$html = '';
		$html .= '<li class="kewtef" data="'.$notice['nid'].'">
				<div class="kdretu">
				<a href="'.geturl("/sns/feeds/"."$notice[fromuid].html").'"><img class="jwtrer" src="'.$notice['fromuserface'].'"/></a>
				<span class="klsegrt">'.$notice['fromusername'].'</span><span class="fejrts">'.emotionreplace($message['comment']).'</span>';
				if($message['topic'] != ''){
					$html .= '<a href="'.$detailurl.'" class="klsegrt">'.emotionreplace($message['topic']).'</a>';
				}else{
					$html .= '<a href="'.$detailurl.'" class="klsegrt">查看</a>';	
				}
				$html .= '<span class="fejrts">'.date('Y-m-d H:m',$notice['dateline']).'</span></div></li>';
		
		return $html;
	}
}

/**
 * 照片墙排版
 * 组装图片 生成html
 */
if(!function_exists('getphotohtml')){
	function getphotohtml($inum = 0,$data){
		$html = '<li class="even">';
		$_UP = Ebh()->config->get('upconfig');
		$showpath = $_UP['snspic']['showpath'];
		$imgstyle = "style=margin-left:wwwpx;margin-top:hhhpx";
		if($inum == 0){
			//获取原尺寸
			$isizes = explode(',', $data[0]['sizes']);
			$osizestr = $isizes[0];
			if(!empty($osizestr)){
				$tsize = explode('_', $osizestr);
				$_w = $tsize[0];
				$_h = $tsize[1];
				if($_w>=412){
					$nw = floor((412 - $_w)/2);
					$imgstyle_0 = str_replace('www', $nw, $imgstyle);
				}else{
					$imgstyle_0 = str_replace('www', 0, $imgstyle);
				}
				if($_h>=285){
					$nh = floor((285 - $_h)/2);
					$imgstyle_0 = str_replace('hhh', $nh, $imgstyle_0);
				}else{
					$imgstyle_0 = str_replace('hhh', 0, $imgstyle_0);
				}	
			}else{
				$imgstyle_0 = '';
			}
			$html .= '<span class="imgView2 pic_max">
					<a class="clk-1 pic_lk" href="javascript:;">
					<p><img class="photo-img-large lazy" data-original="'.$showpath.$data[0]['path'].'" '.$imgstyle_0.'>';
			$html .= '</p></a></span>';
			$html .= '<div class="lc_pics_pic4">';
			for($i = 1; $i<9; $i++){
				if(isset($data[$i])){
					//获取原尺寸
					$isizes = explode(',', $data[$i]['sizes']);
					$osizestr = $isizes[0];
					if(!empty($osizestr)){
						$tsize = explode('_', $osizestr);
						$_w = $tsize[0];
						$_h = $tsize[1];
						if($_w>=131){
							$nw = floor((131 - $_w)/2);
							$imgstyle_s = str_replace('www', $nw, $imgstyle);
						}else{
							$imgstyle_s = str_replace('www', 0, $imgstyle);
						}
						if($_h>=131){
							$nh = floor((131 - $_h)/2);
							$imgstyle_s = str_replace('hhh', $nh, $imgstyle_s);
						}else{
							$imgstyle_s = str_replace('hhh', 0, $imgstyle_s);
						}
					}else{
						$imgstyle_s = '';
					}
					$html .= '<span class="imgView2 pic_min">
						<a class="clk-1 pic_lk" href="javascript:;">
						<p><img class="photo-img-small lazy" data-original="'.$showpath.$data[$i]['path'].'" '.$imgstyle_s.'>
						</p></a>
						</span>';
				}
			}
			$html .= '</div>';
		}else{
			$html .= '<div class="lc_pics_pic4">';
			for($i = 0; $i<8; $i++){
				if(isset($data[$i])){
					//获取原尺寸
					$isizes = explode(',', $data[$i]['sizes']);
					$osizestr = $isizes[0];
					if(!empty($osizestr)){
						$tsize = explode('_', $osizestr);
						$_w = $tsize[0];
						$_h = $tsize[1];
						if($_w>=131){
							$nw = floor((131 - $_w)/2);
							$imgstyle_s = str_replace('www', $nw, $imgstyle);
						}else{
							$imgstyle_s = str_replace('www', 0, $imgstyle);
						}
						if($_h>=131){
							$nh = floor((131 - $_h)/2);
							$imgstyle_s = str_replace('hhh', $nh, $imgstyle_s);
						}else{
							$imgstyle_s = str_replace('hhh', 0, $imgstyle_s);
						}
					}else{
						$imgstyle_s = '';
					}
					
					$html .= '<span class="imgView2 pic_min">
						<a class="clk-1 pic_lk" href="javascript:;">
						<p><img class="photo-img-small lazy" data-original="'.$showpath.$data[$i]['path'].'"'.$imgstyle_s.'>
						</p></a>
						</span>';
				}
			}
			$html .= '</div>';
			if(isset($data[8])){
				//获取原尺寸
				$isizes = explode(',', $data[6]['sizes']);
				$osizestr = $isizes[0];
				if(!empty($osizestr)){
					$tsize = explode('_', $osizestr);
					$_w = $tsize[0];
					$_h = $tsize[1];
					if($_w>=412){
						$nw = floor((412 - $_w)/2);
						$imgstyle_0 = str_replace('www', $nw, $imgstyle);
					}else{
						$imgstyle_0 = str_replace('www', 0, $imgstyle);
					}
					if($_h>=285){
						$nh = floor((285 - $_h)/2);
						$imgstyle_0 = str_replace('hhh', $nh, $imgstyle_0);
					}else{
						$imgstyle_0 = str_replace('hhh', 0, $imgstyle_0);
					}
				}else{
					$imgstyle_0 = '';
				}
				$html .= '<span class="imgView2 pic_max">
						<a class="clk-1 pic_lk" href="javascript:;">
						<p><img class="photo-img-large lazy" data-original="'.$showpath.$data[8]['path'].'"'.$imgstyle_0.'>
						</p></a>
						</span>';
			}
		}
		$html .= '</li>';
		return $html;
	}
}

/**
 * 获取一个文章列表html,用于ajax返回
 */
if(!function_exists('getbloghtml')){
	function getbloghtml($blog){
		$blogurl = geturl("iframe/blog/view")."?bid=".$blog['bid'];
		if($blog['permission'] == 0){
			$permission = '所有人可见';
		}else if($blog['permission'] == 4){
			$permission = '仅自己可见';
		}
		$html = '<li class="fketgd" data="'.$blog['bid'].'">';
		$html .= '<div class="dtejef">';
		if($blog['iszhuan']){
			$html .= '<span style="color:#9b9b9b; font-size: 14px;font-family: punctuation,微软雅黑,Tohoma;"> [转] </span>';
		}
		$html .= '<a href="'.$blogurl.'" class="ewtkjre">'.shortstr($blog['title'],80).'</a>';
		$html .= '<p class="ksetsd">'.$permission.' '.date('Y-m-d H:i:s',$blog['dateline']).' 分类：'.$blog['catename'].'</p>';
		$html .= '<p class="ryfbdd">'.$blog['tutor'].'</p><div class="qrtuirth" style="margin:10px 0 0 0;">';
		$html .= '<a class="item replys" href="'.$blogurl."&reply=1".'" target="_blank"><i class="ui-icons comment "></i>评论（'.$blog['cmcount'].'）</a>';
		$html .= '<a class="item transfers" data="'.$blog['bid'].'" href="javascript:;"><i class="ui-icons forward"></i>转载（'.$blog['zhcount'].'）</a>';
		$html .= '<a class="item upclicks" data="'.$blog['bid'].'" href="javascript:;"><i class="ui-icons praise"></i>赞（'.$blog['upcount'].'）</a></div>';
		$html .= '<div class="kejtev"><a href="/iframe/blog/edit.html?bid='.$blog['bid'].'">编辑</a>|<a href="javascript:delblog('.$blog['bid'].');">删除</a></div>';
		$html .= '</div></li>';
		return $html;
	}
}

/**
 * 获取model
 */
if(!function_exists('getmodel')){
	function getmodel($modelname){
	    $model = ucfirst($modelname).'Model';
		return new $model();
	}
}



//获取时间格式化后串
function gettimestr($timestamp){
    if(!is_numeric($timestamp)){
        return false;
    }
    $rtime = date("m月d日 H:i",$timestamp);
    $htime = date("H:i",$timestamp);

    $yesterday = strtotime('-1 day');//昨天
    $beforeyesterday = strtotime('-2 day');//前天
    $today = strtotime(date('Y-m-d',time()));//今天

    $dftime = time() - $timestamp;

    if($timestamp>$today){//今天
        if($dftime < 60){
            $str = '刚刚';
        }elseif($dftime < 60 * 60){
            $min = floor($dftime/60);
            $str = $min.'分钟前';
        }else{
            $str = '今天  '.$htime;
        }
    }elseif($timestamp>$yesterday&&$timestamp<$today){//昨天
        $str = '昨天 '.$htime;
    }elseif($timestamp>$beforeyesterday&&$timestamp<$yesterday){//前天
        $str = '前天 '.$htime;
    }else{
        $str = $rtime;
    }

    return $str;
}

//表情替换为图片
function emotionreplace($content){
    $content   =   str_replace('&#091;','[',$content);
    $content   =   str_replace('&#093;',']',$content);

    $s = preg_replace_callback(
        "/\[(.*)\]/isU",
        function($matchs){
            $emotion = Ebh()->config->get('emotion');
            $ret = '';
            if(!empty($emotion[$matchs[1]])){
                $ret = "<img width=\"24\" height=\"24\" src=\"http://static.ebanhui.com/sns/images/qq/".$emotion[$matchs[1]]."\">";
            }
            return $ret;
        },
        $content
    );


    return $s;
}