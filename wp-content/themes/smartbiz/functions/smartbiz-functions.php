<?php
	error_reporting( E_ALL ^ E_NOTICE );
	require_once( 'smartbiz-admin.php' );

	if (!class_exists('SmartBiz')) {
		class SmartBiz extends GetBusinessBlog {

		function SmartBiz () {
		$smartbiz_theme=get_theme_data( get_template_directory().'/style.css' );
		/* THEME VARIABLES */
		$this->themename = $smartbiz_theme['Title'];
		$this->themeurl = $smartbiz_theme['URI'];
		$this->shortname = "SB";
		$directory = get_stylesheet_directory_uri();
		$this->paths = wp_upload_dir(true);
		
			$this->options = array(

				array(	"name" => __('Logo <span>customize your logo</span>', 'smartbiz'),
								"type" => "subhead"),
									
				array(	"name" => __('Logo image file URL', 'smartbiz'),
								"id" => $this->shortname."_logo_image_url",
								"desc" => __('Provide your logo image full URL here', 'smartbiz'),
								"std" => '',
								"type" => "text"),

				array(	"name" => __('Hide logo image', 'smartbiz'),
								"id" => $this->shortname."_hide_logo",
								"desc" => __('Check this box to hide the logo image in the header', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),
	
				array(	"name" => __('Top Menu <span>control your top navigation menu</span>', 'smartbiz'),
								"type" => "subhead"),
									
				array(	"name" => __('Hide all pages', 'smartbiz'),
								"id" => $this->shortname."_hide_pages",
								"desc" => __('Check this box to hide your blog pages in the navigation menu', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),
									
				array(	"name" => __('Hide all categories', 'smartbiz'),
								"id" => $this->shortname."_hide_categories",
								"desc" => __('Check this box to hide your blog categories in the navigation menu', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),				

				array(	"name" => __('Header <span>manage featured section & search</span>', 'smartbiz'),
								"type" => "subhead"),

				array(	"name" => __('Show search bar in the header', 'smartbiz'),
								"id" => $this->shortname."_show_search",
								"desc" => __('Check this box to display search field on top of the navigation menu', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),								
									
				array(	"name" => __('Featured', 'smartbiz'),
								"id" => $this->shortname."_featured",
								"desc" => __('Add text to be displayed in the featured section of your blog header', 'smartbiz'),
								"type" => "textarea",
								"options" => array( "rows" => "5", "cols" => "40") 
								),									

				array(	"name" => __('Hide featured section', 'smartbiz'),
								"id" => $this->shortname."_hide_featured",
								"desc" => __('Check this box to hide featured section in your header', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),

				array(	"name" => __('Featured image file URL', 'smartbiz'),
								"id" => $this->shortname."_featured_image_url",
								"desc" => __('Provide your featured image full URL here', 'smartbiz'),
								"std" => '',
								"type" => "text"),

				array(	"name" => __('Content Display <span>content display options</span>', 'smartbiz'),
								"type" => "subhead"),
									
				array(	"name" => __('Show full posts', 'smartbiz'),
								"id" => $this->shortname."_show_full",
								"desc" => __('Check this box to display full posts instead of the excerpts', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),

				array(	"name" => __('Sidebar Spots <span>customize your sidebar</span>', 'smartbiz'),
								"type" => "subhead"),

				array(	"name" => __('Custom code content for Spot 1', 'smartbiz'),
								"id" => $this->shortname."_spot1",
								"desc" => __('Use text or properly formatted XHTML/HTML to be displayed on top of the widgets', 'smartbiz'),
								"std" => '',
								"type" => "textarea",
								"options" => array( "rows" => "5", "cols" => "40") 
								),									

				array(	"name" => __('Hide Spot 1', 'smartbiz'),
								"id" => $this->shortname."_hide_spot1",
								"desc" => __('Check this box to disable Spot 1 in the sidebar', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),

				array(	"name" => __('Custom code content for Spot 2', 'smartbiz'),
								"id" => $this->shortname."_spot2",
								"desc" => __('Use text or properly formatted XHTML/HTML to be displayed below the widgets', 'smartbiz'),
								"std" => '',
								"type" => "textarea",
								"options" => array( "rows" => "5", "cols" => "40") 
								),									

				array(	"name" => __('Hide Spot 2', 'smartbiz'),
								"id" => $this->shortname."_hide_spot2",
								"desc" => __('Check this box to disable Spot 2 in the sidebar', 'smartbiz'),
								"std" => '',
								"type" => "checkbox"),									

				array(	"name" => __('Footer <span>manage your footer</span>', 'smartbiz'),
								"type" => "subhead"),

				array(	"name" => __('Copyright name', 'smartbiz'),
								"id" => $this->shortname."_copyright",
								"desc" => __('Provide the name of your business here', 'smartbiz'),
								"std" => '',
								"type" => "text"),

				array(	"name" => __('Statistics code', 'smartbiz'),
								"id" => $this->shortname."_statistics",
								"desc" => __('Paste your Google Analytics or any other tracking code here. The script will be inserted before the closing <code>&#60;/body&#62;</code> tag.', 'smartbiz'),
								"std" => '',
								"type" => "textarea",
								"options" => array( "rows" => "5", "cols" => "40") ),

				);
				parent::GetBusinessBlog();
			}

			/* LOGO FUNCTIONS */
			function logoUrl () {
				return get_option($this->shortname.'_logo_image_url');
			}
			function hideLogo () {
				return get_option($this->shortname.'_hide_logo');
			}
			
			/* TOP MENU FUNCTIONS */
			function hidePages () {
				return get_option($this->shortname.'_hide_pages');
			}			
			function hideCategories () {
				return get_option($this->shortname.'_hide_categories');
			}
			
			/* HEADER FUNCTIONS */
			function showSearch () {
				return get_option($this->shortname.'_show_search');
			}			
			function headerFeatured () {
				return stripslashes(wpautop(get_option($this->shortname.'_featured')));
			}
			function hideFeatured () {
				return get_option($this->shortname.'_hide_featured');
			}
			function headerUrl () {
				return get_option($this->shortname.'_featured_image_url');
			}

			/* CONTENT DISPLAY FUNCTIONS */
			function showFull () {
				return get_option($this->shortname.'_show_full');
			}

			/* SIDEBAR SPOTS FUNCTIONS */
			function spot1Content () {
				return	stripslashes(get_option($this->shortname.'_spot1'));
			}			
			function hideSpot1 () {
				return get_option($this->shortname.'_hide_spot1');
			}
			function spot2Content () {
				return	stripslashes(get_option($this->shortname.'_spot2'));
			}			
			function hideSpot2 () {
				return get_option($this->shortname.'_hide_spot2');
			}			

			/* FOOTER FUNCTIONS */
			function copyrightName () {
				return wp_filter_post_kses(get_option($this->shortname.'_copyright'));
			}
			function statisticsCode () {
				return stripslashes(get_option($this->shortname.'_statistics'));
			}

		}
	}

	if (class_exists('SmartBiz')) {
		$smartbiz = new SmartBiz();
	}

?>