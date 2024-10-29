<?php
/*
Plugin Name: BBC World Service Widget
Plugin URI: http://www.bbc.co.uk/worldservice/programmes/000000_widget_terms.shtml
Description: Display the BBC World Service widget on your site. <strong>Go to the <a href="widgets.php">Widgets page</a></strong>, once the plugin is activated. From there, you can add the widget to a sidebar and change the content language.
Author: BBC World Service
Author URI: http://www.bbc.co.uk/worldservice/
Version: 1.0.2
*/

/*
Copyright 2009, BBC World Service

Developed by Dharmafly: http://dharmafly.com
Read how the widget was built: http://dharmafly.com/bbc-world-service-widget

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

See the GNU General Public License:
     http://www.gnu.org/licenses/gpl.txt
     http://www.gnu.org/licenses/
*/

define('BBCWSWIDGET_VERSION',		'1.0.1');
define('BBCWSWIDGET_DEBUG',			false);
define('BBCWSWIDGET_LANG_FALLBACK',	'en');
define('BBCWSWIDGET_PLATFORM',		'wordpress');
define('BBCWSWIDGET_HTTP_BASE',		'http://www.bbc.co.uk/worldservice/widget/');
define('BBCWSWIDGET_WIDGETJS',		BBCWSWIDGET_HTTP_BASE . 'widget.js');
define('BBCWSWIDGET_PACKAGESJS',	BBCWSWIDGET_HTTP_BASE . 'live/packages.js');
define('BBCWSWIDGET_WP_WIDGET_URL',	$_SERVER['SCRIPT_NAME']);


// Public-facing widget contents
function bbcwswidget_contents() {
	$options = get_option('bbcwswidget');
?>
	<!-- BBC World Service Widget -->
	<script src="<?php echo BBCWSWIDGET_WIDGETJS; ?>" type="text/javascript"></script>
	<script type="text/javascript">
	bbcwswidget.settings({
	  "package" : "<?php echo $options['package'] ?>",
	  "platform" : "<?php echo BBCWSWIDGET_PLATFORM; ?>"
	});
	</script>
<?php
}

// Public-facing widget constructor
function bbcwswidget($args){
	extract($args);
	echo $before_widget;
	echo $before_title;
	bbcwswidget_contents();
	echo $after_widget;
}

// Admin panel widget controls
function bbcwswidget_control() {
	// Get blog installation language
	$wp_lang = strtolower(trim(str_replace('_', '-', get_bloginfo('language'))));
	// Get blog charset
	$wp_charset = strtolower(preg_replace('[^a-zA-Z0-9]', '', get_bloginfo('charset')));
	
	// Stored widget options
	$options = get_option('bbcwswidget');
	if (!is_array($options)){
		// Default options
		$options = array(
			'package'=> BBCWSWIDGET_LANG_FALLBACK, // Select the main language, ignoring the locale
			'packages_html' => ''
		);
		update_option('bbcwswidget', $options);
	}
	
	// When admin user changes widget controls
	if ($_POST['bbcwswidget_submit'] && $_POST['bbcwswidget_package']) {
		// Sanitize and format user input
		$options['package'] = strip_tags(stripslashes($_POST['bbcwswidget_package']));
		update_option('bbcwswidget', $options);
	}
	
	// When packages HTML loads with latest list of packages
	if ($_POST['bbcwswidget_packages_html'] && $_POST['bbcwswidget_package']) {
		$options['packages_html'] = stripslashes($_POST['bbcwswidget_packages_html']);
		$options['package'] = strip_tags(stripslashes($_POST['bbcwswidget_package']));
		update_option('bbcwswidget', $options);
	}
?>

<!-- BBC World Service Widget control -->
<div class="bbcwswidget_control">
	<label for="bbcwswidget_packages"><?php _e('Content'); ?>:</label>
	<select class="bbcwswidget_packages" name="bbcwswidget_package">
		<?php
			// Package select options
			$packages_html = preg_replace(
				'/(value="' . $options['package'] . '")/m',
				'$1 selected="selected"',
				$options['packages_html']
			);
			if ($wp_charset !== 'utf8'){
				$packages_html = mb_convert_encoding($packages_html, 'HTML-ENTITIES', 'UTF-8');
			}
			echo $packages_html;
		?>
	</select>
	<input type="hidden" name="bbcwswidget_submit" value="1" />
</div>

<script type="text/javascript" charset="utf-8">
<!--//--><![CDATA[//><!--

// Avoid the script executing multiple times, due to WordPress' cloning of widget HTML in the admin panel
if (typeof document.bbcwswidget_packages_called === 'undefined'){
	document.bbcwswidget_packages_called = true;
	
	function bbcwswidget_package(packages){
		var debug, $, html, wp_charset, wp_lang, lang_fallback, package_selected;
		
		debug = (<?php echo BBCWSWIDGET_DEBUG ? 'true' : 'false'; ?> && typeof console === 'object');
		$ = jQuery;
		wp_lang = '<?php echo $wp_lang; ?>';
		wp_charset = '<?php echo $wp_charset; ?>';
		lang_fallback = '<?php echo BBCWSWIDGET_LANG_FALLBACK; ?>';
		package_selected = '<?php echo ($options['packages_html'] !== '') ? $options['package'] : null; ?>';
		
		if (debug){
			if (!console.dir){
				console.dir = console.log;
			}
			console.dir({
				wp_lang: wp_lang,
				wp_charset: wp_charset,
				lang_fallback: lang_fallback,
				package_selected: package_selected,
				packages: packages
			});
		}
	
		// console log for debugging
		function log(){
			if (debug){
				console.log(arguments);
			}
		}
		
		// Does package with test id exist?
		function packageExists(testId){
			var packagesFound = $.grep(packages, function(pck){
				return pck.id === testId;
			});
			return !!packagesFound.length;
		}
		
		// Lang string has locale
		function hasLocale(lang){
			return lang.indexOf('-') !== -1;
		}		
		
		// Return root lang from lang-locale, e.g. 'en' from 'en-gb'
		function getLangRoot(lang){
			if (hasLocale(lang)){
				return wp_lang.slice(0, wp_lang.indexOf('-'));
			}
			return lang;
		}	
	
		// On error loading packages list, display message
		function errorLoadingPackageList(){
			jQuery(document).ready(function($){
				$('.bbcwswidget_control')
					.html('<p><?php _e('There was a problem retrieving the list of available World Service widgets'); ?>.</p><p><a href="' + window.location.pathname + '"><?php _e('Please try refreshing the page'); ?></a>.</p>')
					.parents('.widget')
						.find(':submit')
						.remove();
			});
		}
		
		///
		
		html = $.map(packages, function(pck){
			 return '<option value="' + pck.id + '" title="' + pck.title + ' (' + pck.lang_english + ')">' + pck.title + '</option>';
		}).join('');
		
		log('Package list HTML', html);

		// No package yet selected
		if (!package_selected){
			log('No package yet selected');
			
			// Use WP installation language local if possible
			if (packageExists(wp_lang)){
				package_selected = wp_lang;
				log('Using full wp_lang');
			}
			// If not, use WP installation language root, if possible
			else if (hasLocale(wp_lang) && packageExists(getLangRoot(wp_lang))){
				package_selected = getLangRoot(wp_lang);
				log('Using wp_lang root');
			}
			// If not, use default fallback language for the widget
			else if (packageExists(lang_fallback)){
				package_selected = lang_fallback;
				log('Using lang fallback');
			}
			// Hrmmm. Well, then use the first package found.
			else if (packages.length){
				package_selected = packages[0].id;
				log('Error: Using packages[0]');
			}
			// Oh dear. Erm, I'll get my coat.
			else {
				errorLoadingPackageList();
				log("Error: Can't select package");
				return false;
			}
		}
		log('package set as: ' + package_selected);
	  
		// Once the widget control DOM has loaded, post back the packages HTML list back to widgets.php
		$(document).ready(function($){
			// Post to widget controller, so that HTML can be rendered when widget control is redrawn
			$.post('<?php echo BBCWSWIDGET_WP_WIDGET_URL; ?>', {
				bbcwswidget_package: package_selected,
				bbcwswidget_packages_html: html
			});
		
			// Insert HTML into selectbox
			function populatePackagesSelectbox(pckId, el){
				pckId = pckId || package_selected;
				el = el || $('.bbcwswidget_packages');
				
				el.each(function(){
					$(this).html(html)
						.find('option[value=' + pckId + ']')
							.attr('selected', 'selected');
				});
			}
			
			// Activate selectbox population function
			populatePackagesSelectbox();
		});
	}
	
<?php
	// Encode the script tag to prevent WordPress mistaking the widget source for being a direct call to the script
	$script_tag = '<script '
		. 'src="' . BBCWSWIDGET_PACKAGESJS . '?platform=' . BBCWSWIDGET_PLATFORM . '&v=' . BBCWSWIDGET_VERSION . '" '
		. 'type="text/javascript" '
		. 'charset="utf-8">'
		. '</script>';
?>
	jQuery(decodeURIComponent("<?php echo rawurlencode($script_tag); ?>"))
		.appendTo('head');
}

//--><!]]>
</script>
<?php
}

function bbcwswidget_init() {
	register_sidebar_widget(__('BBC World Service'), 'bbcwswidget');
	register_widget_control(__('BBC World Service'), 'bbcwswidget_control');
}
add_action('widgets_init', 'bbcwswidget_init');
?>