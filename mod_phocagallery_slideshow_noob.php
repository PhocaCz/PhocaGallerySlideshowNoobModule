<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Module
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

defined('_JEXEC') or die('Restricted access');
if (!JComponentHelper::isEnabled('com_phocagallery', true)) {
	return JError::raiseError(JText::_('Phoca Gallery Error'), JText::_('Phoca Gallery is not installed on your system'));
}

if (! class_exists('PhocaGalleryLoader')) {
    require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocagallery'.DS.'libraries'.DS.'loader.php');
}

phocagalleryimport('phocagallery.path.path');
phocagalleryimport('phocagallery.file.file');
phocagalleryimport('phocagallery.file.filethumbnail');

$db 		= &JFactory::getDBO();
$document	= JFactory::getDocument();


$module_css_style	= trim( $params->get( 'module_css_style' ) );
$type 				= $params->get( 'type', 1 );//NOOB or STANDARD, KENBURNS (not ready for mootools 1.3)
$catId 				= $params->get( 'category_id', 0 );
$count				= $params->get( 'count_images', 5 );
$width 				= $params->get( 'width', 970 );
$height				= $params->get( 'height', 230 );
$buttons 			= $params->get( 'display_buttons', 1 );
$desc 				= $params->get( 'display_desc', 1 );
$duration 			= $params->get( 'duration', 2000 );
$durationFade 		= $params->get( 'duration_fade', 1600 );
$transition 		= $params->get( 'fx_transition', 'Bounce' );
$ease 				= $params->get( 'fx_ease', 'easeOut');
$descOpacity		= $params->get( 'desc_opacity', '0.6');
$pathImg 			= JURI::base(true).'/modules/mod_phocagallery_slideshow_noob/images/';
$mode				= 'horizontal';



JHTML::stylesheet('modules/mod_phocagallery_slideshow_noob/css/style.css' );
if ($type == 2) {
	JHTML::stylesheet('modules/mod_phocagallery_slideshow_noob/css/style-fade.css' );
}

$iCss 				= $params->get( 'css_description', 'width:500px;
	height:100px;
	-moz-border-radius: 10px 0px 0px 0px;
	-webkit-border-radius: 10px 0px 0px 0px;
	border-radius: 10px 0px 0px 0px;
	color: #000;
	bottom:0px;
	right:0px;
	background:#fff;
	position:absolute;' );

// IMAGES
$query      = ' SELECT a.title, a.description, a.filename, a.extl'
			. ' FROM #__phocagallery_categories AS cc'
			. ' LEFT JOIN #__phocagallery AS a ON a.catid = cc.id'
			. ' WHERE a.published = 1 AND a.catid = ' . (int)$catId
			//. ' WHERE cc.published = 1 AND a.published = 1 AND a.catid = ' . (int)$catId
			//. ' ORDER BY RAND()'
			. ' ORDER BY a.ordering'
			. ' LIMIT '.(int)$count;

			
$db->setQuery($query);
$images = $db->loadObjectList();

JHTML::_('behavior.framework', true);

if ($type == 1) {
	$opc = '';
	//JS NOOB
	$document->addScript(JURI::base(true).'/modules/mod_phocagallery_slideshow_noob/javascript/_class.noobSlide.packed.js');
		if ($transition == 'linear'){
		$tO = 'transition: Fx.Transitions.linear';
	} else {
		$tO = 'transition: Fx.Transitions.'.$transition.'.'.$ease;
	}
	
	$js = '';
	if (!empty($images)) {
		$js = '<script language=\'javascript\' type=\'text/javascript\'>
		//<![CDATA[
		window.addEvent(\'domready\',function(){'	. "\n";
		if ($desc == 1) {
			$js .= '   var pgnsinfo = $(\'pgnsinfo\').set(\'opacity\','.(float)$descOpacity.');'. "\n";
		}
		$js .= '   var pOItems =['	. "\n";
			$jsi	= array();
			foreach ($images as $k => $v) {
				
				$descImg = str_replace("\n", ' ', $v->description);
				$descImg = str_replace("\r", ' ', $descImg);
			
				$jsi[] = '  {title:\''.htmlspecialchars( addslashes($v->title)).'\', desc:\''.strip_tags( addslashes($descImg)).'\'}'. "\n";
			}
			$js .= implode($jsi, ',');
		$js .= '   ]'. "\n";
			
		$js .= '   var pNS = new noobSlide({
	   box: $(\'pgnsbox\'),
	   mode: \''.$mode.'\',
	   items: pOItems,
	   size: '.$width.',
	   autoPlay: true,'. "\n";

	   if ((int)$buttons == 1) {
	   // $js .= '   handle_event: \'mouseenter\',
	   $js .= '   addButtons: {
		 previous: $(\'pgnsprev\'),
		 play: $(\'pgnsplay\'),
		 stop: $(\'pgnsstop\'),
		 next: $(\'pgnsnext\')
		},'. "\n";
		
		} else if ((int)$buttons == 2) {
		$js .= '   addButtons: {
		 previous: $(\'pgnsprev\'),
		 next: $(\'pgnsnext\')
		},'. "\n";
		}
		//$js .= '   button_event: \'click\',
		$js .= '   fxOptions: {
		 duration: '.$duration.','."\n"
		 . $tO .','."\n"
		 
		 .'wait: false
		}'. "\n";
		
		if ($desc == 1) {
			$js .= ',	onWalk: function(currentItem){
				pgnsinfo.empty();
				new Element(\'h4\').set(\'html\', currentItem.title).inject(pgnsinfo);
				new Element(\'div\').set(\'html\', currentItem.desc).inject(pgnsinfo);
			}'. "\n";
		}

		$js .= '	});'. "\n";
		$js .= '   });'. "\n";
		$js .= '   //]]> '. "\n";
		$js .= '</script>'. "\n";
	}
	
} else if ($type == 2){
	$opc = 'opacity: 0;';
	$js = '<script language=\'javascript\' type=\'text/javascript\'>
		//<![CDATA[
		window.addEvent(\'domready\',function(){'	. "\n";

		if ($desc == 1) {
			$js .= '   var pgnsinfo = $(\'pgnsinfo\').set(\'opacity\','.(float)$descOpacity.');'. "\n";
		}
	$js .='
	var pgnsDuration 	= '.(int)$duration.';
	var pgnsCI 			= 0;/*Current Item*/
	var pgnsInt;
	var pgnsStop 		= 0;
	
	var pgnsImages 		= $(\'pgnsbox\').getElements(\'img\');
	pgnsImages.each(function(img,i){ 
		if(i > 0) {
			img.set(\'opacity\',0.000001);
		} else {
			img.set(\'opacity\',1);
		}
	});'. "\n";
	
	if ($desc == 1) {
		$js .= 
		'
		$(\'pgnsinfo\').setStyle(\'display\',\'block\');
		var pgnsInfos 		= $(\'pgnsinfo\').getElements(\'div.pgnsboxin\');
		pgnsInfos.each(function(div,i){ 
			if(i > 0) {
				div.set(\'opacity\',0.000001);
			} else {
				div.set(\'opacity\',1);
			}
		});'. "\n";
	}
  
	$js .= 'var pgnsShow = function() {'. "\n";
	
	if ($desc == 1) {
		$js .= 'new Fx.Morph(pgnsInfos[pgnsCI], {duration:'.$durationFade.'}).start({\'opacity\':0});'. "\n";
	}
	
	$js .= 'new Fx.Morph(pgnsImages[pgnsCI], {duration:'.$durationFade.'}).start({\'opacity\':0});
	pgnsCI = pgnsCI < pgnsImages.length - 1 ? pgnsCI + 1 : 0;'. "\n";
	
	if ($desc == 1) {
		$js .= 'new Fx.Morph(pgnsInfos[pgnsCI], {duration:'.$durationFade.'}).start({\'opacity\': 1});'. "\n";
	}
	$js .= 'new Fx.Morph(pgnsImages[pgnsCI], {duration:'.$durationFade.'}).start({\'opacity\': 1});
	};

	window.addEvent(\'load\',function(){
		pgnsInt = pgnsShow.periodical(pgnsDuration);
	});'. "\n\n";
  
if ((int)$buttons > 0) {

	// Next
	$js .= '$(\'pgnsnext\').addEvent(\'click\', function(){'. "\n";
			
	/*	$js .=	'if (pgnsStop == 300) {
				clearInterval(pgnsInt);
				pgnsStop = 1;
			} else {';
	*/		
				$js .= 'clearInterval(pgnsInt);'. "\n";
				//$js .= 'pgnsImages[pgnsCI].set(\'opacity\',0);'. "\n";
				$js .= 'new Fx.Morph(pgnsImages[pgnsCI], {duration:'.$durationFade.'}).start({opacity:0});'. "\n";
				if ($desc == 1) { 
					//$js .= 'pgnsInfos[pgnsCI].set(\'opacity\',0);'. "\n";
					$js .= 'new Fx.Morph(pgnsInfos[pgnsCI], {duration:'.$durationFade.'}).start({opacity:0});'. "\n";
				}
				$js .= 'pgnsCI = pgnsCI < pgnsImages.length - 1 ? pgnsCI + 1 : 0;'. "\n";
				//$js .= 'pgnsImages[pgnsCI].set(\'opacity\',1);'. "\n";
				$js .= 'new Fx.Morph(pgnsImages[pgnsCI], {duration:'.$durationFade.'}).start({opacity:1});'. "\n";
				if ($desc == 1) { 
					//$js .= 'pgnsInfos[pgnsCI].set(\'opacity\',1);'. "\n";
					$js .= 'new Fx.Morph(pgnsInfos[pgnsCI], {duration:'.$durationFade.'}).start({opacity:1});'. "\n";
				}
	//$js .=	'	}'. "\n";
	$js .= '});'. "\n";

	// Previous
	$js .= '$(\'pgnsprev\').addEvent(\'click\', function(){'. "\n";
			
	/*	$js .=	'if (pgnsStop == 300) {
				clearInterval(pgnsInt);
				pgnsStop = 1;
			} else {';
	*/		
				$js .= 'clearInterval(pgnsInt);'. "\n";
				//$js .= 'pgnsImages[pgnsCI].set(\'opacity\',0);'. "\n";
				$js .= 'new Fx.Morph(pgnsImages[pgnsCI], {duration:'.$durationFade.'}).start({opacity:0});'. "\n";
				if ($desc == 1) { 
					//$js .= 'pgnsInfos[pgnsCI].set(\'opacity\',0);'. "\n";
					$js .= 'new Fx.Morph(pgnsInfos[pgnsCI], {duration:'.$durationFade.'}).start({opacity:0});'. "\n";
				}
				$js .= 'pgnsCI = pgnsCI > 0 ? pgnsCI - 1 : pgnsImages.length - 1;'. "\n";
				//$js .= 'pgnsImages[pgnsCI].set(\'opacity\',1);'. "\n";
				$js .= 'new Fx.Morph(pgnsImages[pgnsCI], {duration:'.$durationFade.'}).start({opacity:1});'. "\n";
				if ($desc == 1) { 
					//$js .= 'pgnsInfos[pgnsCI].set(\'opacity\',1);'. "\n";
					$js .= 'new Fx.Morph(pgnsInfos[pgnsCI], {duration:'.$durationFade.'}).start({opacity:1});'. "\n";
				}
	//$js .=	'	}'. "\n";
	$js .= '});'. "\n";

	if ((int)$buttons == 1) {
		// Stop
		$js .= '$(\'pgnsstop\').addEvent(\'click\', function(){
			$clear(pgnsInt);
			pgnsStop = 1;
		});'. "\n";

		// Play
		$js .= '$(\'pgnsplay\').addEvent(\'click\', function(){
			$clear(pgnsInt);
			pgnsStop = 0;
			pgnsInt = pgnsShow.periodical(pgnsDuration);
		});'. "\n";
		}
	}
	$js .= '});'. "\n";

	$js .= '   //]]> '. "\n";
	$js .= '</script>'. "\n";

} else {
	$opc = '';
	//JS SLIDESHOW
	// NOT READY FOR MOOTOOLS 1.3
	/*
	$document->addScript(JURI::base(true).'/modules/mod_phocagallery_slideshow_noob/javascript/slideshow.js');
	$document->addScript(JURI::base(true).'/modules/mod_phocagallery_slideshow_noob/javascript/slideshow.kenburns.js');
	
	if ($desc == 1) {
		$captionK = 'true';
	} else {
		$captionK = 'false';
	}
	
	if ($buttons == 1) {
		$buttonsK = 'true';
	} else {
		$buttonsK = 'false';
	}
	
	// TODO:
	$buttonsK = 'false';
	
	$js = '';
	if (!empty($images)) {
		$js = '<script language=\'javascript\' type=\'text/javascript\'>
		//<![CDATA[
		window.addEvent(\'domready\', function(){
	    var pOItems = {'. "\n";
		
		$jsi	= array();
			foreach ($images as $k => $v) {
				$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($v->filename, 'large');
				//$thumbName	= 'phoca_thumb_l_' . PhocaGalleryFile::getTitleFromFile($v->filename, 1);
				$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($v->filename, 'large');
				$jsi[] = '  \''.$thumbLink->rel.'\': {caption:\''.strip_tags( addslashes($v->description)).'\'}'. "\n";
			}
			$js .= implode($jsi, ',');
	    $js .= '   }'. "\n";
	    $js .= '   var pShow = new Slideshow.KenBurns(\'pgnsbox\', pOItems, { captions: true, controller: '.$buttonsK.', delay: 5000, duration: '.$duration.', height: '.$height.', hu: \''.JURI::base(true).'/' .'\', width: '.$width.' });';
		$js .= '	});'. "\n";
		$js .= '//]]>'. "\n";
		$js .= '</script>'. "\n";
	}
	*/
}

// HTML
if (!empty($images)) {

// 1 ...Noob
// 2 ...Fade
if ($type == 1 || $type == 2) {
	if ($module_css_style)			{echo '<div style="'.$module_css_style.'">';}

	echo '<div id="pgnsboxs">'. "\n";
	echo '<div id="pgnsboxm" style="width: '.$width.'px; height: '.$height.'px;">'. "\n";
	
	echo '<div id="pgnsbox">'. "\n";
	foreach ($images as $k => $v) {
	
		if (isset($v->extl) &&  $v->extl != '') {
			echo '<span><img style="'.$opc.'" src="'.PhocaGalleryText::strTrimAll($v->extl).'" alt="'.htmlspecialchars($v->title).'" /></span>'. "\n";
		} else {
			$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($v->filename, 'large');
			echo '<span><img style="'.$opc.'" src="'.JURI::base(true).'/'.$thumbLink->rel.'" alt="'.htmlspecialchars($v->title).'" /></span>'. "\n";
		}
	

	}
	echo '</div>';

	if ($desc == 1) {
		if ($type == 1) {
			echo '<div id="pgnsinfo" style="'.$iCss.'"></div>';//Added by JS
			
		} else if ($type == 2) {
			echo '<div id="pgnsinfo" style="'.$iCss.'">';//Added by HTML
			foreach ($images as $k => $v) {
				echo '<div class="pgnsboxin">';
				echo '<h4>'.htmlspecialchars( $v->title).'</h4>';
				echo '<br />'.strip_tags($v->description);
				echo '</div>';
			}
			echo '</div>';
		}
	}
	if ($buttons == 1) {
		echo '<div id="pgnsbuttons">';
		echo '<span id="pgnsprev"><img src="'.$pathImg.'prev.png'.'" alt="&lt;&lt;" /></span>'. "\n";
		echo '<span id="pgnsplay"><img src="'.$pathImg.'play.png'.'" alt="&gt;" /></span>'. "\n";
		echo '<span id="pgnsstop"><img src="'.$pathImg.'stop.png'.'" alt="||" /></span>'. "\n";
		echo '<span id="pgnsnext"><img src="'.$pathImg.'next.png'.'" alt="&gt;&gt;" /></span>'. "\n";
		echo '</div>'. "\n";
	} else if ($buttons == 2) {
		echo '<div id="pgnsbuttonslargeleft">';
		echo '<span id="pgnsprev"><img src="'.$pathImg.'prevl.png'.'" alt="&lt;&lt;" /></span>'. "\n";
		echo '</div>'. "\n";
		
		echo '<div id="pgnsbuttonslargeright">';
		echo '<span id="pgnsnext"><img src="'.$pathImg.'nextl.png'.'" alt="&gt;&gt;" /></span>'. "\n";
		echo '</div>'. "\n";
	
	}
	echo '</div>'. "\n";
	echo '</div>'. "\n";
	
	if ($module_css_style)			{echo '</div>';}
	
} else {	
	
	// NOT READY FOR MOOTOOLS 1.3
	/*
	echo '<div id="pgnsbox">'. "\n";
	$thumbLink	= PhocaGalleryFileThumbnail::getThumbnailName($images[0]->filename, 'large');
	echo '<img src="'.JURI::base(true).'/'.$thumbLink->rel.'" alt="'.htmlspecialchars($images[0]->title).'" width="'.$width.'" height="'.$height.'" />'. "\n";
	echo '</div>';
	*/
	// TODO CAPTIONS AREA
	
}

}

$document->addCustomTag($js);
?>