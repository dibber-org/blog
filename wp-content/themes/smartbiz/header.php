<!DOCTYPE html>
<html>
<head <?php language_attributes(); ?>>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<?php global $smartbiz;
	if ( is_front_page() ) : ?>
		<title><?php bloginfo( 'name'); ?></title>
	<?php elseif ( is_404() ) : ?>
		<title><?php _e( 'Page Not Found', 'smartbiz' ); ?> | <?php bloginfo( 'name'); ?></title>
	<?php elseif ( is_search() ) : ?>
		<title><?php printf(__("Search results for '%s'", "smartbiz"), esc_attr(get_search_query())); ?> | <?php bloginfo( 'name'); ?></title>
	<?php else : ?>
		<title><?php wp_title($sep=''); ?> | <?php bloginfo( 'name'); ?></title>
	<?php endif; ?>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="screen" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<meta name="description" content="<?php bloginfo( 'name' ); ?>" >
	<?php if ((is_single() || is_category() || is_page() || is_home()) && (!is_paged())){} else { ?>
		<meta name="robots" content="noindex,follow" >
	<?php } ?>
	<?php if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' ); ?>
	<?php wp_head(); ?>
	<link rel="alternate" type="application/rss+xml" title="<?php bloginfo( 'name'); ?> RSS Feed" href="<?php bloginfo( 'rss2_url'); ?>" />
	<?php wp_head(); ?>
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
								<p><?php bloginfo('description'); ?></p>
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

