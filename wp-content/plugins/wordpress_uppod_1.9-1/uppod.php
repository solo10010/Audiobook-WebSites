<?php
/*
Plugin Name: Uppod
Plugin URI: http://uppod.ru/help/q=wordpress
Author: Uppod
Description: Медиаплеер Uppod
Author URI: http://uppod.ru
Version: 1.9
*/

// SETTINGS
$uppod_settings['uppod.swf']='http://audiobk.ru/uppod/uppod.swf';     // <-- Flash http://uppod.ru/player/download/
$uppod_settings['uppod.js']='http://audiobk.ru/uppod/audio210-504.js';      // <-- HTML5 http://uppod.ru/help/html5/

$uppod_settings['swfobject.js']='http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js';
$uppod_settings['adobe_update']='Для плеера требуется установить <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash Player</a>';
$uppod_settings['wmode']='';
$uppod_settings['bgcolor']='#ffffff';
$uppod_settings['uid']=0;

//VIDEO
$uppod['video']['style']='';
$uppod['video']['style_html5']='';
$uppod['video']['width']='500';
$uppod['video']['height']='375';

$uppod['video']['style2']='';
$uppod['video']['style2_html5']='';
$uppod['video']['width2']='400';
$uppod['video']['height2']='300';

//AUDIO
$uppod['audio']['style']='http://audiobk.ru/uppod/audio210-504.txt';
$uppod['audio']['style_html5']='';
$uppod['audio']['width']='100%';
$uppod['audio']['height']='455';

//PHOTO
$uppod['photo']['style']='';
$uppod['photo']['style_html5']='';
$uppod['photo']['width']='400';
$uppod['photo']['height']='300';

function Uppod($atts, $content = null){
    global $uppod;
	global $uppod_settings;
   	$o='';
    $fv='';
	$style='';
	$center=false;
	$html5=false;
	$flash=true;
    if($atts['video']){
		$m='video';
	}
	if($atts['audio']){
		$m='audio';
	}
	if($atts['photo']){
		$m='photo';
	}
	if($atts['align']){
		$atts['align']=='left'?$style='float:left;':'';
		$atts['align']=='right'?$style='float:right;':'';
		$atts['align']=='center'?$center=true:'';
	}
	
	$atts['type']?$t=$atts['type']:$t='';
	if($atts['margin']){
		$style.='margin:'.$atts['margin'].'px;';
	}
	if($uppod_settings['uppod.js']!='http://'&&$uppod_settings['uppod.js']!='http://'){
		$html5=true;
		$style.='width:'.$uppod[$m]['width'.$t].'px;height:'.$uppod[$m]['height'.$t].'px;';
	}
	foreach($atts as $k => $value) {
		$k!=$m&&$k!='align'&&$k!='margin'?$fv.=',"'.$k.'":"'.$value.'"':'';
	}
	
	$num=rand(0,1000);
    if(isset($m)){
    
    	if(strpos($atts[$m],',')===false){
    		strpos($atts[$m],'.txt')==strlen($atts[$m])-4||strpos($atts[$m],'youtube:')===0?$fv.=',"pl":"'.$atts[$m].'"':$fv.=',"file":"'.$atts[$m].'"';
    	}else{
    		$fv.=',"pl":"'.Uppod_Pl($atts[$m]).'"';
    	}
    	
    	if($uppod_settings['uppod.swf']=='http://'|$uppod_settings['uppod.swf']==''){
    		$flash = false;
    	}
    	if(!$flash && !$html5){
    		$o='Ошибка: в настройках плагина Uppod не указана ссылка на плеер (<a href="http://uppod.ru/help/q=wordpress">подробнее</a>)';
    	}
    	else{
			$o=($center?'<center>':'').'
			<div id="'.$m.'player'.$num.'" '.($style!=''?'style="'.$style.'"':'').'>'.$uppod_settings['adobe_update'].'</div>'.($center?'</center>':'').'
			<script type="text/javascript">
			'.($flash?'
			var ua = navigator.userAgent.toLowerCase();var flashInstalled = false;if (typeof(navigator.plugins)!="undefined"&&typeof(navigator.plugins["Shockwave Flash"])=="object"){ flashInstalled = true;} else if (typeof window.ActiveXObject != "undefined") {try {if (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) {flashInstalled = true;}} catch(e) {};};':'').'
			var flashvars = {'.($uppod[$m]['style'.$t]!=''&&$flash?'"st":"'.$uppod[$m]['style'.$t].'"':'"m":"'.$m.'"').$fv.($uppod_settings['uid']==1?',"uid":"'.$m.'player'.$num.'"':'').'};
			'.($flash?'
			   if(ua.indexOf("iphone") != -1 || ua.indexOf("ipad") != -1 || (ua.indexOf("android") != -1 && !flashInstalled)){':'').'
			   '.($uppod_settings['uid']!=1?'flashvars["uid"]="'.$m.'player'.$num.'";':'').'
			   '.($uppod['video']['style'.$t.'_html5']!=''?'flashvars["st"]="uppod'.$m.'";':'').'
			   '.($html5?'var player = new Uppod(flashvars);':'').'
			   '.($flash?'}else{
			   	   var params = {allowFullScreen:"true", allowScriptAccess:"always",id:"'.$m.'player'.$num.'",bgcolor:"'.$uppod_settings['bgcolor'].'"'.($uppod_settings['wmode']!=''?',"wmode":"'.$uppod_settings['wmode'].'"':'').'};
			       new swfobject.embedSWF("'.$uppod_settings['uppod.swf'].'", "'.$m.'player'.$num.'", "'.$uppod[$m]['width'.$t].'", "'.$uppod[$m]['height'.$t].'", "10.0.0.0", false, flashvars, params);
			   }':'').'
			</script>';
		}
	}
    return $o;
}
function Uppod_SWFObject() {
	global $uppod_settings;
	global $uppod;
	echo '<script src="'.$uppod_settings['swfobject.js'].'" type="text/javascript"></script>'.($uppod_settings['uppod.js']!=''?"<script src='".$uppod_settings['uppod.js']."' type='text/javascript'></script>":'').($uppod['video']['style_html5']!=''?"<script src='".$uppod['video']['style_html5']."' type='text/javascript'></script>":'').($uppod['video']['style2_html5']!=''?"<script src='".$uppod['video']['style2_html5']."' type='text/javascript'></script>":'').($uppod['audio']['style_html5']!=''?"<script src='".$uppod['audio']['style_html5']."' type='text/javascript'></script>":'').($uppod['photo']['style_html5']!=''?"<script src='".$uppod['photo']['style_html5']."' type='text/javascript'></script>":'');
}
function Uppod_Pl($str) {
	$pl="{'playlist':[";
	$obj=split(',',$str);
	for($i=0;$i<count($obj);$i++){
		$pl.="{'file':'".$obj[$i]."'},";
	}
	return chop($pl,',')."]}";
}
add_action('wp_head', 'Uppod_SWFObject');
add_shortcode('uppod', 'Uppod')
?>