<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
global $smartbiz;
?><!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyeleven' ), max( $paged, $page ) );

	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	/* Always have wp_head() just before the closing </head>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to add elements to <head> such
	 * as styles, scripts, and meta tags.
	 */
	wp_head();
?>
	<script type="text/javascript">
	/* <![CDATA[ */
	function resized(){
		var getH=document.getElementById('get_height');
		var setH=document.getElementById('set_height');
		var setH2=document.getElementById('navigation');
		setH.style.height=getH.clientHeight-6;
		setH2.style.height=getH.clientHeight;
	}
	/* ]]> */
	</script>
</head>
<body <?php body_class()?> onresize="resized();" onload="resized();">
<?php if ( ! isset( $content_width ) ) $content_width = 900;?>
	<div id="wrapper">
		<div id="header-wrapper">
			<table id="header" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
				<tr valign="top">
					<td>
						<div id="logo">
							<div class="title">
							<?php if ($smartbiz->hideLogo() !== 'true' ): ?>
								<a href="<?php echo home_url(); ?>"><img src="<?php if ($smartbiz->logoUrl() != '' ){ echo $smartbiz->logoUrl(); } else { echo get_template_directory_uri() .'/images/logo/logo_d.png';} ?>" alt="<?php echo bloginfo( 'name'); ?>" style="width:78px;height:70px;"></a>
							<?php endif; ?>
								<h1><a href="<?php echo home_url(); ?>/"><?php bloginfo('name'); ?></a></h1>
							</div>
							<div class="desc">
								<p><?php // bloginfo('description'); ?></p>
							</div>
						</div>
					</td>
					<?php if ($smartbiz->showSearch() == 'true' ): ?>
					<td id="header-search">
						<?php qtrans_generateLanguageSelectCode('image'); ?>
						<form method="get" action="">
							<fieldset>
                                                            <input type="text" name="s" id="header-search-text" size="15" placeholder="<?php _e( 'Search...', 'smartbiz' ) ?>" />
                                                            <input type="submit" id="header-search-submit" value="GO" />
							</fieldset>
						</form>
					</td><?php endif; ?>
				</tr>
				</tbody>
			</table>
		</div>

		<div id="nav_bar">
			<table id="get_height" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
				<tr valign="top">
					<td class="menu_border_left">
						<div class="border_radius navigation">
							<b class="r1">&nbsp;</b><b class="r2">&nbsp;</b><b class="r3">&nbsp;</b><b class="r4">&nbsp;</b><b class="r5">&nbsp;</b>
						</div>
					</td>
					<td rowspan="2" class="menu_border_center">
						<div id="navigation">
							<ul class="menu">
							<li class="<?php if (is_home()) echo 'current_page_item'; ?>"><a href="<?php echo home_url(); ?>/" class="<?php if (is_home()) echo 'current_page_item'; ?>"><?php _e( 'Home', 'smartbiz' ) ?></a></li>
							</ul>
							<?php smartbiz_nav(); ?>
						</div>
					</td>
					<td class="menu_border_right">
						<div class="border_radius navigation">
							<b class="r1">&nbsp;</b><b class="r2">&nbsp;</b><b class="r3">&nbsp;</b><b class="r4">&nbsp;</b><b class="r5">&nbsp;</b>
						</div>
					</td>
				</tr>
				<tr>
					<td id="set_height" class="navigation_b">&nbsp;</td>
					<td class="navigation_b">&nbsp;</td>
				</tr>
				</tbody>
			</table>
		</div>
		<?php if ($smartbiz->hideFeatured() !== 'true' && is_home()): ?>
		<div id="featured">
			<div class="text">
				<?php if ($smartbiz->headerFeatured() != '') : ?>
                            <?php // die('"'.$smartbiz->headerFeatured().'"') ?>
					<?php _e(str_replace("\n", "", $smartbiz->headerFeatured()), 'smartbiz'); ?>
				<?php elseif (false) : // Fake use of translation function ?>
					<p><?php _e( '<p>dibber is an attempt at creating a web application that would be usable by small scale farmers, gardeners... to help them establish their plot. They will then manage their activities while choosing to share their experiences publicly or exclusively with their dibber <em>friends</em> making the application a place of perpetual exchanges and mutual-help. More information in <a href="/en/basic-introduction/">the introduction</a>.</p>', 'smartbiz' ) ?></p>
				<?php else : ?>
					<p>Replace this with your custom text in the Header section of the <em>SmartBiz Options</em> page. Type some stuff in the box, click save, and your new Featured section shows up in the header. You can also update the Featured image with your custom one. You can hide this whole section by checking the related box in the <em>SmartBiz Options</em>.</p>
					<p>Manage Content Display options to show full posts instead of the excerpts on your blog page. Take advantage of the flexibility of the <em>SmartBiz Options</em> to customize your site to your likeness.</p>
				<?php endif; ?>
			</div>
<?php /*			<div class="img">
				<img src="<?php if ($smartbiz->headerUrl() != '' ) { echo $smartbiz->headerUrl(); } else { echo get_template_directory_uri().'/images/featured/featured_d.png';} ?>" alt="<?php echo bloginfo( 'name'); ?>" style="height: 226px;width: 407px;">
			</div> */ ?>
		</div>
		<b class="border_radius featured"><b class="r5">&nbsp;</b><b class="r4">&nbsp;</b><b class="r3">&nbsp;</b><b class="r2">&nbsp;</b><b class="r1">&nbsp;</b></b>
		<div id="margin">&nbsp;</div>
	</div>
		<div id="page">
		<b class="border_radius page"><b class="r1">&nbsp;</b><b class="r2">&nbsp;</b><b class="r3">&nbsp;</b><b class="r4">&nbsp;</b><b class="r5">&nbsp;</b></b>
		<div id="page-bgtop">
		<div id="page-bgbtm">
		<?php else : ?>
	</div>
		<div id="page">
		<div id="page-bgtop2">
		<div id="page-bgbtm">
		<?php endif; ?>

