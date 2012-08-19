<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

automatic_feed_links();

if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => 'sidebarOrange',
		'before_widget' => '   <div class="leftLinks">',
		'after_widget' => '<div style="clear:both;"></div></span></div>',
		'before_title' => '<h2 class="bg1">',
		'after_title' => '</h2><span class="widgetContent">',
	));
	register_sidebar(array(
		'name' => 'sidebarGray',
		'before_widget' => '   <div class="leftLinks">',
		'after_widget' => '<div style="clear:both;"></div></span></div>',
		'before_title' => '<h2 class="bg2">',
		'after_title' => '</h2><span class="widgetContent">',
	));
	register_sidebar(array(
		'name' => 'sidebarGreen',
		'before_widget' => '   <div class="leftLinks">',
		'after_widget' => '<div style="clear:both;"></div></span></div>',
		'before_title' => '<h2 class="bg3">',
		'after_title' => '</h2><span class="widgetContent">',
	));
}

?>
