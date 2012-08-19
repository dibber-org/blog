<?php
	if (!class_exists( 'GetBusinessBlog')) {
		class GetBusinessBlog {

			var $themename = "";
			var $themeurl = "";
			var $shortname = "";
			var $options = array();

			function GetBusinessBlog () {
				add_action( 'init', array(&$this, 'initAdminFunctions'));
				add_action( 'admin_menu', array(&$this, 'addAdminOptions'));
			}

			/* Admin Page CSS & JS */
			function initAdminFunctions () {
				if ( !empty($_GET['page']) && $_GET['page'] == basename(__FILE__) ) {
					wp_enqueue_style( 'gbb', get_template_directory_uri().'/functions/styles/admin.css');
					wp_enqueue_script( 'gbb', get_template_directory_uri().'/functions/js/admin.js', array( 'jquery' ));
				}
			}

			/* Options Page */
			function addAdminOptions () {
				if ( !empty($_GET['page']) && $_GET['page'] == basename(__FILE__) ) {
					if (!empty($_REQUEST['action']) &&  'save' == $_REQUEST['action'] ) {
						foreach ($this->options as $value) {
							update_option( $value['id'], $_REQUEST[ $value['id'] ] );
						}
						foreach ($this->options as $value) {
							if ( isset( $_REQUEST[ $value['id'] ] ) ) {
								update_option( $value['id'], $_REQUEST[ $value['id'] ]	);
							} else {
								delete_option( $value['id'] );
							}
						}
						header("Location: themes.php?page=".basename(__FILE__)."&saved=true");
						die;
					} else if ( !empty($_REQUEST['action']) && 'reset' == $_REQUEST['action'] ) {
						foreach ($this->options as $value) {
							delete_option( $value['id'] );
						}
						header("Location: themes.php?page=".basename(__FILE__)."&reset=true");
						die;
					}
				}
				add_theme_page ($this->themename." Options", $this->themename." Options", 'edit_theme_options', basename(__FILE__), array (&$this, 'adminOptions'));
			}

			function adminOptions () {
				if ( !empty($_REQUEST['saved']) && $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>' . $this->themename . __( ' settings saved!', 'smartbiz' ). '</strong></p></div>';
				if ( !empty($_REQUEST['reset']) && $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>' . $this->themename . __( ' settings reset.', 'smartbiz' ). '</strong></p></div>'; ?>

<div id="gbb-admin">
	<div class="header clear">
		<div class="title">
			<h1><?php echo $this->themename; ?> <?php _e( 'Options', 'smartbiz' ); ?></h1>
		</div>
		<div class="logo">
			<a href="<?php $smartbiz_theme=get_theme_data( get_template_directory().'/style.css' );echo $smartbiz_theme['AuthorURI'];?>">GetBusinessBlog.com</a>
		</div>
	</div>
	<div class="upgrade">
		<h3><?php _e( 'Upgrade to Pro Version', 'smartbiz' ); ?></h3>
		<p>View <a href="<?php echo $this->themeurl;?>" target="_blank"><?php _e( 'SmartBiz Pro Demo', 'smartbiz' ); ?></a> to see why it converts so well.</p>		
		<p><?php 
		echo sprintf( __( 'As a Pro member, you will enjoy these exclusive benefits:<br /><br />
		- <b>Included customizations:</b> up to one hour of our time for your custom changes <br />
		- Extra theme <b>options and features</b> <br />		
		- Unlimited access to dedicated <b>technical support</b> <br />
		- Access to our comprehensive <b>theme tutorials</b> <br />
		- Lifetime <b>theme updates</b> <br />
		- <b>25%% discount</b> on all %s services', 'smartbiz'), $smartbiz_theme['Author'] ); ?></p>
		<p><a href="<?php echo $this->themeurl;?>" target="_blank"><?php _e( 'Learn more about SmartBiz Pro and upgrade', 'smartbiz' ); ?></a></p>
	</div>
	<div id="body">
		<form method="post">
<?php
				for ($i = 0; $i < count($this->options); $i++) :
					switch ($this->options[$i]["type"]) :

						case "subhead":
							if ($i != 0) { ?>
		<div class="save-button submit">
			<input type="hidden" name="action" value="save" />
			<input class="button" type="submit" value="<?php _e( 'Save changes', 'smartbiz' ); ?>" name="save"/>
		</div>
	</div>
</div>
<?php } ?>
<div class="options">
	<h3><?php echo $this->options[$i]["name"]; ?></h3>
	<div class="options-body clear">
		<?php $notice = (!empty($this->options[$i]["notice"]))?$this->options[$i]["notice"]:'' ?>
		<?php if ($notice != '' ){ ?>
			<p class="notice"><?php echo $notice; ?></p>
		<?php } ?>
						<?php
							break;

					case "checkbox":
						?>
		<?php $this->options[$i] ?>
		<div class="option check clear">
			<div class="option-body"><span><?php echo $this->options[$i]["desc"]; ?></span></div>
			<input id="<?php echo $this->options[$i]["id"]; ?>" type="checkbox" name="<?php echo $this->options[$i]["id"]; ?>" value="true"<?php echo (get_option($this->options[$i]['id'])) ? ' checked="checked"' : ''; ?> />
			<label for="<?php echo $this->options[$i]["id"]; ?>"><?php echo $this->options[$i]["name"]; ?></label>
		</div>

						<?php
							break;

						case "text":
							?>
		<?php $this->options[$i] ?>
		<div class="option text clear">
			<div class="option-body"><span><?php echo $this->options[$i]["desc"]; ?></span></div>
			<label for="<?php echo $this->options[$i]["id"]; ?>"><?php echo $this->options[$i]["name"]; ?></label>
			<input id="<?php echo $this->options[$i]["id"]; ?>" type="text" name="<?php echo $this->options[$i]["id"]; ?>" value="<?php echo stripslashes((get_option($this->options[$i]["id"]) != "") ? get_option($this->options[$i]["id"]) : $this->options[$i]["std"]); ?>" />
		</div>

						<?php
							break;

						case "textarea":
							?>
		<?php $this->options[$i] ?>
		<div class="option textarea clear">
			<div class="option-body"><span><?php echo $this->options[$i]["desc"]?></span></div>
			<label for="<?php echo $this->options[$i]["id"]?>"><?php echo $this->options[$i]["name"]?></label>
			<textarea id="<?php echo $this->options[$i]["id"]?>" name="<?php echo $this->options[$i]["id"]?>"<?php echo ($this->options[$i]["options"] ? ' rows="'.$this->options[$i]["options"]["rows"].'" cols="'.$this->options[$i]["options"]["cols"].'"' : ""); ?>><?php
				echo ( get_option($this->options[$i]['id']) != "") ? stripslashes(get_option($this->options[$i]['id'])) : ( (!empty($this->options[$i]['std'])) ?stripslashes($this->options[$i]['std']):'');
			?></textarea>
		</div>
						<?php
							break;
					endswitch;
				endfor;
			?>
					<div class="save-button submit">
						<input type="submit" value="<?php _e( 'Save changes', 'smartbiz' ); ?>" name="save"/>
					</div>
				</div>
			</div>
			<div class="saveall-button submit">
				<input class="button-primary" type="submit" value="<?php _e( 'Save all changes', 'smartbiz' ); ?>" name="save"/>
			</div>
			</form>
			<div class="reset-button submit">
				<form method="post">
					<input type="hidden" name="action" value="reset" />
					<input class="reset" type="submit" value="<?php _e( 'Reset all options', 'smartbiz' ); ?>" name="reset"/>
				</form>
			</div>

			<script type="text/javascript">
				<?php
					for ($i = 0; $i < count($this->options); $i++) :
					endfor;
				?>
			</script>
	</div>
</div>
			<?php
			}
		}
	}
?>