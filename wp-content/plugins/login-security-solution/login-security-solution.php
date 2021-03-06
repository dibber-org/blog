<?php

/**
 * Plugin Name: Login Security Solution
 *
 * Description: Requires very strong passwords, repels brute force login attacks, prevents login information disclosures, expires idle sessions, notifies admins of attacks and breaches, permits administrators to disable logins for maintenance or emergency reasons and reset all passwords.
 *
 * Plugin URI: http://wordpress.org/extend/plugins/login-security-solution/
 * Version: 0.22.0
 * Author: Daniel Convissor
 * Author URI: http://www.analysisandsolutions.com/
 * License: GPLv2
 * @package login-security-solution
 */

/**
 * The instantiated version of this plugin's class
 */
$GLOBALS['login_security_solution'] = new login_security_solution;

/**
 * The Login Security Solution plugin enhances WordPress' security
 *
 * @package login-security-solution
 * @link http://wordpress.org/extend/plugins/login-security-solution/
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 */
class login_security_solution {
	/**
	 * This plugin's identifier
	 */
	const ID = 'login-security-solution';

	/**
	 * This plugin's name
	 */
	const NAME = 'Login Security Solution';

	/**
	 * This plugin's table name prefix
	 * @var string
	 */
	protected $prefix = 'login_security_solution_';


	/**
	 * Is the dict command available?
	 * @var bool  true/false if known, null if unknown
	 */
	protected $available_dict;

	/**
	 * Is the grep command available?
	 * @var bool  true/false if known, null if unknown
	 */
	protected $available_grep;

	/**
	 * Is PHP's mbstring extension enabled?
	 * @var bool  true/false if known, null if unknown
	 */
	protected $available_mbstring;

	/**
	 * Location of our dictionary files
	 *
	 * Public for use by utilities.
	 *
	 * @var string
	 */
	public $dir_dictionaries;

	/**
	 * Location of our sequence files
	 * @var string
	 */
	protected $dir_sequences;

	/**
	 * Our URI query string key for passing messages to the login form
	 * @var string
	 */
	protected $key_login_msg;

	/**
	 * Has the internationalization text domain been loaded?
	 * @var bool
	 */
	protected $loaded_textdomain = false;

	/**
	 * This plugin's options
	 *
	 * Options from the database are merged on top of the default options.
	 *
	 * @see login_security_solution::set_options()  to obtain the saved
	 *      settings
	 * @var array
	 */
	protected $options = array();

	/**
	 * This plugin's default options
	 * @var array
	 */
	protected $options_default = array(
		'admin_email' => '',
		'deactivate_deletes_data' => 0,
		'disable_logins' => 0,
		'idle_timeout' => 15,
		'login_fail_minutes' => 120,
		'login_fail_tier_2' => 5,
		'login_fail_tier_3' => 10,
		'login_fail_notify' => 50,
		'login_fail_breach_notify' => 6,
		'login_fail_breach_pw_force_change' => 6,
		'pw_change_days' => 0,
		'pw_change_grace_period_minutes' => 15,
		'pw_complexity_exemption_length' => 20,
		'pw_length' => 8,
		'pw_reuse_count' => 0,
	);

	/**
	 * Our option name for storing the plugin's settings
	 * @var string
	 */
	protected $option_name;

	/**
	 * Name, with $table_prefix, of the table tracking login failures
	 * @var string
	 */
	protected $table_fail;

	/**
	 * Our usermeta key for tracking when passwords were changed
	 * @var string
	 */
	protected $umk_changed;

	/**
	 * Our usermeta key for tracking when a password grace period started
	 * @var string
	 */
	protected $umk_grace_period;

	/**
	 * Our usermeta key for tracking old passwords
	 * @var string
	 */
	protected $umk_hashes;

	/**
	 * Our usermeta key for tracking when the user last hit the site
	 * @var string
	 */
	protected $umk_last_active;

	/**
	 * Our usermeta key for tracking if a user's password needs to be changed
	 * @var string
	 */
	protected $umk_pw_force_change;

	/**
	 * Our usermeta key for tracking this user's verified IP addresses
	 * @var string
	 */
	protected $umk_verified_ips;


	/**
	 * Declares the WordPress action and filter callbacks
	 *
	 * @return void
	 * @uses login_security_solution::initialize()  to set the object's
	 *       properties
	 */
	public function __construct() {
		$this->initialize();

		add_action('auth_cookie_bad_username', array(&$this, 'auth_cookie_bad'));
		add_action('auth_cookie_bad_hash', array(&$this, 'auth_cookie_bad'));
		add_action('auth_cookie_valid', array(&$this, 'check'), 1, 2);
		add_action('password_reset', array(&$this, 'password_reset'), 10, 2);
		add_action('user_profile_update_errors',
				array(&$this, 'user_profile_update_errors'), 999, 3);

		add_action('personal_options', array(&$this, 'pw_policy_add_filter'));
		add_action('user_new_form_tag', array(&$this, 'pw_policy_add_filter'));
		add_action('login_init', array(&$this, 'pw_policy_add_filter'));

		add_filter('login_errors', array(&$this, 'login_errors'));
		add_filter('login_message', array(&$this, 'login_message'));

		if ($this->options['disable_logins']) {
			add_filter('comments_open', array(&$this, 'comments_open'));
		}

		if ($this->options['idle_timeout']) {
			add_action('wp_login', array(&$this, 'delete_last_active'));
			add_action('wp_logout', array(&$this, 'delete_last_active'));
			add_action('auth_cookie_expired', array(&$this, 'auth_cookie_expired'));
		}

		if ($this->options['login_fail_breach_notify']
			|| $this->options['login_fail_breach_pw_force_change'])
		{
			add_action('wp_login', array(&$this, 'wp_login'), 10, 2);
		}

		if (is_admin()) {
			$this->load_plugin_textdomain();

			require_once dirname(__FILE__) . '/admin.php';
			$admin = new login_security_solution_admin;

			if (is_multisite()) {
				$admin_menu = 'network_admin_menu';
				$admin_notices = 'network_admin_notices';
				$plugin_action_links = 'network_admin_plugin_action_links_login-security-solution/login-security-solution.php';
			} else {
				$admin_menu = 'admin_menu';
				$admin_notices = 'admin_notices';
				$plugin_action_links = 'plugin_action_links_login-security-solution/login-security-solution.php';
			}

			add_action($admin_menu, array(&$admin, 'admin_menu'));
			add_action('admin_init', array(&$admin, 'admin_init'));
			add_filter($plugin_action_links, array(&$admin, 'plugin_action_links'));

			if ($this->options['disable_logins']) {
				add_action('admin_notices', array(&$admin, 'admin_notices_disable_logins'));
			}

			register_activation_hook(__FILE__, array(&$admin, 'activate'));
			if ($this->options['deactivate_deletes_data']) {
				register_deactivation_hook(__FILE__, array(&$admin, 'deactivate'));
			}

			// NON-STANDARD: This is for the password change page.
			add_action($admin_menu, array(&$admin, 'admin_menu_pw_force_change'));
			if (!$admin->was_pw_force_change_done()) {
				add_action($admin_notices, array(&$admin, 'admin_notices_pw_force_change'));
			}
			add_action('admin_init', array(&$admin, 'admin_init_pw_force_change'));
		}
	}

	/**
	 * Sets the object's properties and options
	 *
	 * This is separated out from the constructor to avoid undesirable
	 * recursion.  The constructor sometimes instantiates the admin class,
	 * which is a child of this class.  So this method permits both the
	 * parent and child classes access to the settings and properties.
	 *
	 * @return void
	 *
	 * @uses login_security_solution::set_options()  to replace the default
	 *       options with those stored in the database
	 */
	protected function initialize() {
		global $wpdb;

		$this->table_fail = $wpdb->get_blog_prefix(0) . $this->prefix . 'fail';

		$this->key_login_msg = self::ID . '-login-msg-id';
		$this->option_name = self::ID . '-options';
		$this->umk_changed = self::ID . '-pw-changed-time';
		$this->umk_pw_force_change = self::ID . '-pw-force-change';
		$this->umk_grace_period = self::ID . '-pw-grace-period-start-time';
		$this->umk_hashes = self::ID . '-pw-hashes';
		$this->umk_last_active = self::ID . '-last-active';
		$this->umk_verified_ips = self::ID . '-verified-ips';

		$this->dir_dictionaries = dirname(__FILE__) . '/pw_dictionaries/';
		$this->dir_sequences = dirname(__FILE__) . '/pw_sequences/';

		$this->set_options();

		if ($this->options['login_fail_tier_2'] < 2) {
			$this->options['login_fail_tier_2'] = 2;
		}
		if ($this->options['pw_change_days']
			&& !$this->options['pw_reuse_count'])
		{
			$this->options['pw_reuse_count'] = 5;
		}
		if ($this->options['pw_change_grace_period_minutes'] < 5) {
			$this->options['pw_change_grace_period_minutes'] = 5;
		}
		if ($this->options['pw_complexity_exemption_length'] < 20) {
			$this->options['pw_complexity_exemption_length'] = 20;
		}
		if ($this->options['pw_length'] < 8) {
			$this->options['pw_length'] = 8;
		}
	}

	/*
	 * ===== ACTION & FILTER CALLBACK METHODS =====
	 */

	/**
	 * Passes failed auth cookie data to our login failure process
	 *
	 * NOTE: This method is automatically called by WordPress when a user's
	 * cookie has an invalid user name or password hash.
	 *
	 * @param array $cookie_elements  the auth cookie data
	 *
	 * @uses login_security_solution::process_login_fail()  to log the failure
	 *       and slow down the response as necessary
	 */
	public function auth_cookie_bad($cookie_elements) {
		if (empty($cookie_elements['username'])) {
			$username = '';
		} else {
			$username = $cookie_elements['username'];
		}
		if (empty($cookie_elements['hmac'])) {
			$hmac = '';
		} else {
			$hmac = $cookie_elements['hmac'];
		}
		###$this->log("auth_cookie_bad: $username, $hmac");

		// Remove cookies to prevent further mayhem.
		wp_clear_auth_cookie();

		// The auth cookie process happens so early that we can't close the
		// database connection yet.
		$this->process_login_fail($username, $hmac, false);
	}

	/**
	 * Removes the current user's last active time metadata
	 *
	 * NOTE: This method is automatically called by WordPress when a user's
	 * cookie has expired.
	 *
	 * @param array $cookie_elements  the auth cookie data
	 * @return mixed  return values provided for unit testing
	 */
	public function auth_cookie_expired($cookie_elements) {
		if (empty($cookie_elements['username'])) {
			return;
		}

		$user = get_user_by('login', $cookie_elements['username']);
		if (! $user instanceof WP_User) {
			return -1;
		}

		return delete_user_meta($user->ID, $this->umk_last_active);
	}

	/**
	 * Redirects the current user to the login screen if their password
	 * is expired or needs to be reset
	 *
	 * NOTE: This method is automatically called by WordPress after
	 * successful validation of authentication cookies.
	 *
	 * @param array $cookie_elements  values from the user's cookies
	 * @param WP_User $user  the current user
	 * @return mixed  return values provided for unit testing
	 *
	 * @uses login_security_solution::is_idle()  to know if it has been too
	 *       long since the user's last action
	 * @uses login_security_solution::is_pw_expired()  to know if it has been
	 *       too long since the password was last changed
	 * @uses login_security_solution::get_pw_force_change()  to know if the
	 *       user has to change their password for other reasons
	 * @uses login_security_solution::$options  for the disable_logins setting
	 * @uses login_security_solution::redirect_to_login()  to send the user to
	 *       the login form and tell them what the problem is
	 */
	public function check($cookie_elements, $user) {
		global $current_user;

		// The auth_cookie_valid action may be executed multiple times.
		// Bail if the current_user has not been determined yet.
		if (!($current_user instanceof WP_User) || empty($user->ID)) {
			return false;
		}

		/*
		 * NOTE: redirect_to_login() calls exit(), except when unit testing.
		 */

		if ($this->is_idle($user->ID)) {
			###$this->log("check(): Idle.");
			$this->redirect_to_login('idle', true);
			return -5;
		}

		if ($this->is_pw_expired($user->ID)) {
			$grace = $this->check_pw_grace_period($user->ID);
			if ($grace === true) {
				###$this->log("check(): First time here since password expired.");
				$this->redirect_to_login('pw_grace', true);
				return -1;
			} elseif ($grace === false) {
				###$this->log("check(): Grace period expired.");
				$this->redirect_to_login('pw_expired', false, 'retrievepassword');
				return -2;
			}
			// Grace period is in effect, let them slide for now.
		}

		if ($this->get_pw_force_change($user->ID)) {
			###$this->log("check(): Password force change.");
			$this->redirect_to_login('pw_force', false, 'retrievepassword');
			return -3;
		}

		if ($this->options['disable_logins']
			&& !current_user_can('administrator'))
		{
			###$this->log("check(): Disable logins.");
			$this->redirect_to_login();
			return -4;
		}

		return true;
	}

	/**
	 * Tells WordPress to disallow commenting on posts
	 *
	 * NOTE: This method is automatically called by WordPress when checking
	 * to see if comments are allowed on a post AND our "disable_logins"
	 * option is enabled
	 *
	 * @return bool  always returns false
	 */
	public function comments_open() {
		return false;
	}

	/**
	 * Removes the current user's last active time metadata
	 *
	 * NOTE: This method is automatically called by WordPress when users
	 * log in or out.
	 *
	 * @return mixed  return values provided for unit testing
	 */
	public function delete_last_active() {
		global $user_ID, $user_name;

		if (empty($user_ID)) {
			if (empty($user_name)) {
				###$this->log("delete_last_active(): Empty user_ID, user_name.");
				return;
			}
			$user = get_user_by('login', $user_name);
			if (! $user instanceof WP_User) {
				###$this->log("delete_last_active(): Unknown user_name.");
				return -1;
			}
			$user_ID = $user->ID;
		}

		return delete_user_meta($user_ID, $this->umk_last_active);
	}

	/**
	 * Alters the failure messages from logins and password resets that
	 * contain information disclosures
	 *
	 * The following measures are necessary, at least in WordPress 3.3:
	 * + Changes invalid user name message from log in process.
	 * + Changes invalid password message from log in process.
	 * + Unsets the user name when the password is wrong.
	 * + Changes invalid user name message from lost password process.
	 *
	 * These cloaking measures complicate cracking attempts by keeping
	 * attackers from knowing that half of the puzzle has been solved.
	 *
	 * NOTE: This method is automatically called by WordPress when attempted
	 * logins are unssucessful.
	 *
	 * @param string $out  the output from earlier login_errors filters
	 * @return string
	 *
	 * @uses login_security_solution::process_login_fail()  to log the failure
	 *       and slow down the response as necessary
	 */
	public function login_errors($out = '') {
		global $errors, $user_name;

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'register') {
			// Do not alter "invalid_username" or "invalid_email" messages
			// from registration process.  (WP 3.3 reuses error codes.)
			return $out;
		}

		$error_codes = $errors->get_error_codes();

		$codes_to_cloak = array('incorrect_password', 'invalid_username');
		if (array_intersect($error_codes, $codes_to_cloak)) {
			// Use POST value, global $user_name isn't always set.
			$user_name = empty($_POST['log']) ? '' : $_POST['log'];
			$user_pass = empty($_POST['pwd']) ? '' : $_POST['pwd'];
			// Unset user name to avoid information disclosure.
			unset($_POST['log']);
			###$this->log("login_fail(): $user_name, $user_pass.");
			$this->process_login_fail($user_name, $user_pass);
			$this->load_plugin_textdomain();
			return $this->hsc_utf8(__('Invalid username or password.', self::ID));
		}

		$codes_to_cloak = array('invalid_email', 'invalidcombo');
		if (array_intersect($error_codes, $codes_to_cloak)) {
			// This text is lifted directly from WordPress.
			return $this->hsc_utf8(__('Password reset is not allowed for this user'));
		}

		return $out;
	}

	/**
	 * Adds our message to the other messages that appear above the login form
	 *
	 * NOTE: This method is automatically called by WordPress for displaying
	 * text above the login form.
	 *
	 * @param string $out  the output from earlier login_message filters
	 * @return string
	 *
	 * @uses login_security_solution::$key_login_msg  to know which $_GET
	 *       parameter to watch for our message ID's
	 */
	public function login_message($out = '') {
		$this->load_plugin_textdomain();
		$ours = '';

		if (!empty($_GET[$this->key_login_msg])) {
			switch ($_GET[$this->key_login_msg]) {
				case 'idle':
					$ours = sprintf(__('It has been over %d minutes since your last action.', self::ID), $this->options['idle_timeout']);
					$ours .= ' ' . __('Please log back in.', self::ID);
					break;
				case 'pw_expired':
					$ours = __('The grace period for changing your password has expired.', self::ID);
					$ours .= ' ' . __('Please submit this form to reset your password.', self::ID);
					break;
				case 'pw_force':
					$ours = __('Your password must be reset.', self::ID);
					$ours .= ' ' . __('Please submit this form to reset it.', self::ID);
					break;
				case 'pw_grace':
					$ours = __('Your password has expired. Please log and change it.', self::ID);
					$ours .= ' ' . sprintf(__('We provide a %d minute grace period to do so.', self::ID), $this->options['pw_change_grace_period_minutes']);
					break;
				case 'pw_reset_bad':
					$ours = __('The password you tried to create is not secure. Please try again.', self::ID);
					break;
			}
		}

		if ($this->options['disable_logins']) {
			$ours = __('The site is undergoing maintenance.', self::ID);
			$ours .= ' ' . __('Please try again later.', self::ID);
		}

		if ($ours) {
			$out .= '<p class="login message">'
					. $this->hsc_utf8($ours) . '</p>';
		}

		return $out;
	}

	/**
	 * Conveys the password change information to the user's metadata
	 *
	 * NOTE: This method is automatically called by WordPress when users
	 * provide their new password via the password reset functionality.
	 *
	 * @param WP_User $user  the user object being edited
	 * @param string $user_pass  the unhashed new password
	 * @return mixed  return values provided for unit testing
	 *
	 * @uses login_security_solution::process_pw_metadata()  to update user
	 *       metadata
	 */
	public function password_reset($user, $user_pass) {
		if (empty($user->ID)) {
			###$this->log("password_reset(): user->ID not set.");
			return false;
		}

		$user->user_pass = $user_pass;
		if (!$this->validate_pw($user)) {
			###$this->log("password_reset(): Invalid password chosen.");
			$this->set_pw_force_change($user->ID);
			$this->redirect_to_login('pw_reset_bad', false, 'rp');
			return -1;
		}

		$this->save_verified_ip($user->ID, $this->get_ip());
		$this->process_pw_metadata($user->ID, $user_pass);
	}

	/**
	 * Declares our password policy gettext filter
	 *
	 * NOTE: This method is automatically called by WordPress
	 * on the wp-login.php, user-new.php, and user-edit.php pages.
	 *
	 * @return void
	 */
	public function pw_policy_add_filter() {
		add_filter('gettext', array(&$this, 'pw_policy_rewrite'), 11, 2);
	}

	/**
	 * Replaces WP's password policy text with ours
	 *
	 * NOTE: This method is automatically called by WordPress during gettext
	 * calls on the wp-login.php, user-new.php, and user-edit.php pages.
	 *
	 * @param string $translated  the translated output from earlier filters
	 * @param string $original  the un-translated text
	 * @return string  our translated password policy
	 *
	 * @uses login_security_solution::$options  for the pw_length and
	 *       pw_complexity_exemption_length values
	 */
	public function pw_policy_rewrite($translated, $original) {
		$policy = 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).';

		if ($original == $policy) {
			$translated = sprintf(__("The password should either be: A) at least %d characters long and contain upper and lower case letters plus numbers and punctuation, or B) at least %d characters long.", self::ID), $this->options['pw_length'], $this->options['pw_complexity_exemption_length']);
		}

		return $translated;
	}

	/**
	 * Ensures passwords meet policy requirements
	 *
	 * NOTE: This method is automatically called by WordPress when users save
	 * their profile information or when admins add a user.  The callback
	 * is activated in the edit_user() function in wp-admin/includes/user.php.
	 *
	 * @param WP_Error $errors  the means to provide specific error messages
	 * @param bool $update  is this an existing user?
	 * @param WP_User $user  the user object being edited
	 * @return bool|null  return values provided for unit testing
	 *
	 * @uses login_security_solution::is_pw_reused()  to know if it's an old
	 *       pw
	 * @uses login_security_solution::validate_pw()  to know if the pw is
	 *       kosher
	 * @uses login_security_solution::process_pw_metadata()  to update user
	 *       metadata
	 */
	public function user_profile_update_errors(&$errors, $update, $user) {
		if ($update) {
			if (empty($user->user_pass) || empty($user->ID)) {
				// Password is not being changed.
				return null;
			}
			if ($this->is_pw_reused($user->user_pass, $user->ID)) {
				$this->load_plugin_textdomain();
				$errors->add(self::ID,
					$this->err(__("Passwords can not be reused.", self::ID)),
					array('form-field' => 'pass1')
				);
				return false;
			}
		}
		$answer = $this->validate_pw($user, $errors);

		// Empty ID means an admin is adding a new user.
		if (!empty($user->ID) && !$errors->get_error_codes()) {
			$this->process_pw_metadata($user->ID, $user->user_pass);
			if ($user->ID == get_current_user_id()) {
				$this->save_verified_ip($user->ID, $this->get_ip());
			}
		}

		return $answer;
	}

	/**
	 * Removes the current user's last active time metadata
	 *
	 * NOTE: This method is automatically called by WordPress when users
	 * successfully log in.
	 *
	 * @param string $user_name  the user name from the current login form
	 * @param WP_User $user  the current user
	 * @return mixed  return values provided for unit testing
	 *
	 * @uses login_security_solution::get_ip()  to get the
	 *       $_SERVER['REMOTE_ADDR']
	 * @uses login_security_solution::get_network_ip()  gets the IP's
	 *       "network" part
	 * @uses login_security_solution::md5()  to hash the password
	 * @uses login_security_solution::get_login_fail()  to see if
	 *       they're over the limit
	 * @uses login_security_solution::get_verified_ips()  to check legitimacy
	 * @uses login_security_solution::$options  for the
	 *       login_fail_breach_notify value
	 * @uses login_security_solution::$options  for the
	 *       login_fail_breach_pw_force_change value
	 * @uses login_security_solution::set_pw_force_change() to keep atackers
	 *       from doing damage or changing the account's email address
	 * @uses login_security_solution::notify_breach()  to warn of the breach
	 */
	public function wp_login($user_name, $user) {
		if (!$user_name) {
			return;
		}
		if (!$this->options['login_fail_breach_notify']
			&& !$this->options['login_fail_breach_pw_force_change'])
		{
			return -1;
		}

		$ip = $this->get_ip();
		$network_ip = $this->get_network_ip($ip);
		$pass_md5 = $this->md5(empty($_POST['pwd']) ? '' : $_POST['pwd']);

		$return = 1;
		$fails = $this->get_login_fail($network_ip, $user_name, $pass_md5);

		/*
		 * Keep legitimate users from having to repeatedly reset passwords
		 * during active attacks against their user name (password ).  Do this
		 * if the user's current IP address is not involved with any of the
		 * recent failed logins and the current IP address has been verified.
		 */
		if (!$fails['network_ip']
			&& in_array($ip, $this->get_verified_ips($user->ID)))
		{
			$return += 8;
			$verified_ip = true;
		} else {
			$verified_ip = false;
		}

		if ($this->options['login_fail_breach_pw_force_change']
			&& $fails['total'] >= $this->options['login_fail_breach_pw_force_change']
			&& !$verified_ip)
		{
			###$this->log("wp_login(): Breach force change.");
			$this->set_pw_force_change($user->ID);
			$return += 2;
		}

		if ($this->options['login_fail_breach_notify']
			&& $fails['total'] >= $this->options['login_fail_breach_notify'])
		{
			// Send this, even if IP is verified, just in case.
			###$this->log("wp_login(): Breach notify.");
			$this->notify_breach($network_ip, $user_name, $pass_md5, $fails,
					$verified_ip);
			if ($verified_ip) {
				$this->notify_breach_user($user);
			}
			$return += 4;
		}

		return $return;
	}

	/*
	 * ===== INTERNAL METHODS ====
	 */

	/**
	 * Examines and manipulates password grace periods as needed
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return mixed  true if the grace period just started, integer of
	 *                minutes remaining if in effect, false if exceeded
	 *
	 * @uses login_security_solution::get_pw_grace_period()  to know the grace
	 *       period starting time
	 * @uses login_security_solution::set_pw_grace_period()  to set the grace
	 *       period starting time if it does not exist
	 * @uses login_security_solution::$options  for the
	 *       pw_change_grace_period_minutes setting
	 */
	protected function check_pw_grace_period($user_ID) {
		$start = $this->get_pw_grace_period($user_ID);
		if (!$start) {
			$this->set_pw_grace_period($user_ID);
			return true;
		}

		$remaining = $start - time()
				+ ($this->options['pw_change_grace_period_minutes'] * 60);

		if ($remaining < 0) {
			return false;
		}
		return $remaining;
	}

	/**
	 * Changes commonly used transpositions into their actual equivalents
	 *
	 * @param string $pw  the string to clean up
	 * @return string  the human readable string
	 */
	protected function convert_leet_speak($pw) {
		$leet   = array('!', '@', '$', '+', '1', '3', '4', '5', '6', '9', '0');
		$normal = array('i', 'a', 's', 't', 'l', 'e', 'a', 's', 'b', 'g', 'o');
		return str_replace($leet, $normal, $pw);
	}

	/**
	 * Remove's the "force password change" flag from the user's metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return bool
	 */
	protected function delete_pw_force_change($user_ID) {
		return delete_user_meta($user_ID, $this->umk_pw_force_change);
	}

	/**
	 * Remove's the "password grace period" from the user's metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return bool
	 */
	protected function delete_pw_grace_period($user_ID) {
		return delete_user_meta($user_ID, $this->umk_grace_period);
	}

	/**
	 * Safely composes translated error messages
	 *
	 * @param string $message  the error message
	 * @return string
	 */
	protected function err($message) {
		$error = $this->hsc_utf8(__("ERROR"));
		$message = $this->hsc_utf8($message);
		return "<strong>$error</strong>: $message";
	}

	/**
	 * Obtains the email addresses the notifications should go to
	 * @return string
	 */
	protected function get_admin_email() {
		$email = $this->options['admin_email'];
		if (!$email) {
			$email = get_site_option('admin_email');
		}
		return $email;
	}

	/**
	 * Obtains the IP address from $_SERVER['REMOTE_ADDR']
	 *
	 * Also performs basic sanity checks on the addresses.
	 *
	 * @return string  the IP address.  Empty string if input is bad.
	 *
	 * @uses login_security_solution::normalize_ip()  to clean up addresses
	 */
	protected function get_ip() {
		if (empty($_SERVER['REMOTE_ADDR'])) {
			return '';
		}

		return $this->normalize_ip($_SERVER['REMOTE_ADDR']);
	}

	/**
	 * Obtains the timestamp of the given user's last hit on the site
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int  the Unix timestamp of the user's last hit
	 */
	protected function get_last_active($user_ID) {
		return (int) get_user_meta($user_ID, $this->umk_last_active, true);
	}

	/**
	 * Obtains the number of login failures for the current IP, user name
	 * and password in the period specified by login_fail_minutes
	 *
	 * @param string $network_ip  a prior result from get_network_ip()
	 * @param string $user_name  the user name from the current login form
	 * @param string $pass_md5  the md5 hashed new password
	 * @return array  an associative array with the details
	 *
	 * @uses login_security_solution::$options  for the login_fail_minutes
	 *       setting
	 */
	protected function get_login_fail($network_ip, $user_name, $pass_md5) {
		global $wpdb;

		$wpdb->escape_by_ref($user_name);
		$wpdb->escape_by_ref($pass_md5);

		if ($network_ip) {
			// Can't use wpdb::prepare() because it adds quote marks.
			$wpdb->escape_by_ref($network_ip);
			if (strpos($network_ip, ':') === false) {
				$network_ip .= '.';
			} else {
				$network_ip .= ':';
			}
			$ip_search = "ip LIKE '$network_ip%'";
		} else {
			$ip_search = "ip = ''";
		}

		$sql = "SELECT COUNT(*) AS total,
					SUM(IF($ip_search, 1, 0)) AS network_ip,
					SUM(IF(user_login = '$user_name', 1, 0)) AS user_name,
					SUM(IF(pass_md5 = '$pass_md5', 1, 0)) AS pass_md5
				FROM `$this->table_fail`
				WHERE ($ip_search
					OR user_login = '$user_name'
					OR pass_md5 = '$pass_md5')
					AND date_failed > DATE_SUB(NOW(), INTERVAL "
					. (int) $this->options['login_fail_minutes'] . " MINUTE)";

		return $wpdb->get_row($sql, ARRAY_A);
	}

	/**
	 * Gets the "network" component of an IP address
	 *
	 * The "network" component for IPv4 is the first three groups ("Class C")
	 * while for IPv6 it is the first four groups.
	 *
	 * WARNING: This method performs no validation because the data comes
	 * from get_ip() which has already performed sanity checks.
	 *
	 * @param string $ip  a prior result from get_ip(). Defaults to
	 *                    $_SERVER['REMOTE_ADDR'].
	 *
	 * @return string  the IP address.  Empty string if input is bad.
	 *
	 * @uses login_security_solution::get_ip()  to get the
	 *       $_SERVER['REMOTE_ADDR']
	 */
	protected function get_network_ip($ip = '') {
		if (!$ip) {
			$ip = $this->get_ip();
			if (!$ip) {
				return $ip;
			}
		}

		if (!is_string($ip)) {
			return '';
		}

		if (strpos($ip, ':') === false) {
			return substr($ip, 0, strrpos($ip, '.'));
		} else {
			$groups = explode(':', $ip);
			return implode(':', array_intersect_key($groups, array(0, 1, 2, 3)));
		}
	}

	/**
	 * Produces text for use in the notify messages
	 *
	 * @param string $network_ip  a prior result from get_network_ip()
	 * @param string $user_name  the user name from the current login form
	 * @param string $pass_md5  the md5 hashed new password
	 * @return string
	 */
	protected function get_notify_counts($network_ip, $user_name, $pass_md5,
			$fails)
	{
		return sprintf(__("
Component                    Count     Value from Current Attempt
------------------------     -----     --------------------------------
Network IP                   %5d     %s
Username                     %5d     %s
Password MD5                 %5d     %s
", self::ID),
			$fails['network_ip'], $network_ip,
			$fails['user_name'], $user_name,
			$fails['pass_md5'], $pass_md5) . "\n";
	}

	/**
	 * Obtains the timestamp of when the user last changed their password
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int  the Unix timestamp of the user's last password change
	 */
	protected function get_pw_changed_time($user_ID) {
		return (int) get_user_meta($user_ID, $this->umk_changed, true);
	}

	/**
	 * Reads the "force password change" flag from the user's metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return bool  does the user need to change their password?
	 */
	protected function get_pw_force_change($user_ID) {
		return (bool) get_user_meta($user_ID, $this->umk_pw_force_change, true);
	}

	/**
	 * Lists IP addresses known to be good for the user
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return array  the IP addresses
	 */
	protected function get_verified_ips($user_ID) {
		$out = get_user_meta($user_ID, $this->umk_verified_ips, true);
		if (empty($out)) {
			$out = array();
		} elseif (!is_array($out)) {
			$out = (array) $out;
		}
		return $out;
	}

	/**
	 * Obtains the timestamp of when the user's "password grace period"
	 * started
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int  the Unix timestamp of the user's grace period beginning
	 */
	protected function get_pw_grace_period($user_ID) {
		return (int) get_user_meta($user_ID, $this->umk_grace_period, true);
	}

	/**
	 * Obtains the password hashes from the user's metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return array  the user's existing pasword hashes
	 */
	protected function get_pw_hashes($user_ID) {
		$hashes = get_user_meta($user_ID, $this->umk_hashes, true);
		if (empty($hashes)) {
			$hashes = array();
		} elseif (!is_array($hashes)) {
			$hashes = (array) $hashes;
		}
		return $hashes;
	}

	/**
	 * Does the password or given string use the same text?
	 *
	 * @param string $pw  the password to examine
	 * @param string $string  the string to compare the password against
	 * @return bool
	 */
	protected function has_match($pw, $string) {
		if (!is_string($string)) {
			return false;
		}
		$string = trim($string);
		if (!$string) {
			return false;
		}
		if (stripos($pw, $string) !== false) {
			return true;
		}
		if (stripos($string, $pw) !== false) {
			return true;
		}
		return false;
	}

	/**
	 * Sanitizes output via htmlspecialchars() using DB_CHARSET's encoding
	 *
	 * Makes query results safe for displaying in browsers.
	 *
	 * @param string $in   the string to sanitize
	 * @return string  the sanitized string
	 *
	 * @uses DB_CHARSET  set in wp-config.php to know which $encoding to use
	 */
	protected function hsc_db($in) {
		static $encoding;

		if (!isset($encoding)) {
			// Translate MySQL encoding to PHP encoding.
			switch (DB_CHARSET) {
				case 'latin1':
					$encoding = 'ISO-8859-1';
					break;
				case 'utf8':
				case 'utf8mb4':
					$encoding = 'UTF-8';
					break;
				case 'cp866':
					$encoding = 'cp866';
					break;
				case 'cp1251':
					$encoding = 'cp1251';
					break;
				case 'koi8r':
					$encoding = 'KOI8-R';
					break;
				case 'big5':
					$encoding = 'BIG5';
					break;
				case 'gb2312':
					$encoding = 'GB2312';
					break;
				case 'sjis':
					$encoding = 'Shift_JIS';
					break;
				case 'ujis':
					$encoding = 'EUC-JP';
					break;
				case 'macroman':
					$encoding = 'MacRoman';
					break;
				default:
					$encoding = 'UTF-8';
					if (WP_DEBUG) {
						trigger_error("Your DB_CHARSET doesn't map to a PHP encoding.", E_USER_WARNING);
					}
			}
		}

		return htmlspecialchars($in, ENT_QUOTES, $encoding);
	}

	/**
	 * Sanitizes output via htmlspecialchars() using UTF-8 encoding
	 *
	 * Makes this program's native text and translated/localized strings
	 * safe for displaying in browsers.
	 *
	 * @param string $in   the string to sanitize
	 * @return string  the sanitized string
	 */
	protected function hsc_utf8($in) {
		return htmlspecialchars($in, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Saves the failed login's info in the database
	 *
	 * @param string $ip  a prior result from get_ip()
	 * @param string $user_login  the user name from the current login form
	 * @param string $pass_md5  the md5 hashed new password
	 * @return void
	 */
	protected function insert_fail($ip, $user_login, $pass_md5) {
		global $wpdb;

		$wpdb->insert(
			$this->table_fail,
			array(
				'ip' => $ip,
				'user_login' => $user_login,
				'pass_md5' => $pass_md5,
			),
			array('%s', '%s', '%s')
		);
	}

	/**
	 * Examines how long ago the current user last interacted with the
	 * site and takes appropriate action
	 *
	 * @param int $user_ID  the user's id number
	 * @return mixed  true if idle.  Other replies all evaluate to empty
	 *                but use different types to aid unit testing.
	 *
	 * @uses login_security_solution::$options  for the idle_timeout value
	 * @uses login_security_solution::get_last_active()  for the user's last
	 *       hit time
	 * @uses login_security_solution::set_last_active()  to update the user's
	 *       time
	 */
	public function is_idle($user_ID) {
		if (!$this->options['idle_timeout']) {
			return null;
		}

		$last_active = $this->get_last_active($user_ID);
		if (!$last_active) {
			$this->set_last_active($user_ID);
			return 0;
		}

		if (($this->options['idle_timeout'] * 60) + $last_active < time()) {
			return true;
		}

		$this->set_last_active($user_ID);

		return false;
	}

	/**
	 * Does the current login failure exactly match an earlier failure
	 * in the period specified by login_fail_minutes?
	 *
	 * @param string $ip  a prior result from get_ip()
	 * @param string $user_name  the user name from the current login form
	 * @param string $pass_md5  the md5 hashed new password
	 * @return bool
	 *
	 * @uses login_security_solution::$options  for the login_fail_minutes
	 *       setting
	 */
	protected function is_login_fail_exact_match($ip, $user_name, $pass_md5) {
		global $wpdb;

		$wpdb->escape_by_ref($ip);
		$wpdb->escape_by_ref($user_name);
		$wpdb->escape_by_ref($pass_md5);

		$sql = "SELECT COUNT(*) AS total
				FROM `$this->table_fail`
				WHERE (ip = '$ip'
					AND user_login = '$user_name'
					AND pass_md5 = '$pass_md5')
					AND date_failed > DATE_SUB(NOW(), INTERVAL "
					. (int) $this->options['login_fail_minutes'] . " MINUTE)";

		return (bool) $wpdb->get_var($sql);
	}

	/**
	 * Does this password show up in the "dict" program?
	 *
	 * @param string $pw  the password to examine
	 * @return bool|null  true or false if known, null if dict isn't available
	 */
	protected function is_pw_dict_program($pw) {
		if ($this->available_dict === false) {
			return null;
		}

		$term = escapeshellarg($pw);
		exec("dict -m -s exact $term 2>&1", $output, $result);
		if (!$result) {
			return true;
		} elseif ($result == 127) {
			$this->available_dict = false;
			return null;
		}
		return false;
	}

	/**
	 * Is this password in our dictionary files?
	 *
	 * The checks are done using "grep."  If grep is not available, each file
	 * is examined using file() and in_array().
	 *
	 * The dictionary files are in the "pw_dictionaries" directory.  Feel free
	 * to add your own dictionary files.  Please be aware that checking the
	 * files is computationally "expensive" and the larger the files become,
	 * the more time and memory is needed.  Thus it is wise to only put
	 * passwords your files that would not be caught by our other tests.
	 * The "utilties/reduce-dictionary-files.php" script can be used to
	 * weed out unnecessary entries.
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_dictionary($pw) {
		if ($this->available_grep === true) {
			return $this->is_pw_dictionary__grep($pw);
		} elseif ($this->available_grep === false) {
			return $this->is_pw_dictionary__file($pw);
		}
		$result = $this->is_pw_dictionary__grep($pw);
		if ($result !== null) {
			return $result;
		}
		return $this->is_pw_dictionary__file($pw);
	}

	/**
	 * Examines the password files via file() and in_array()
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_dictionary__file($pw) {
		$dir = new DirectoryIterator($this->dir_dictionaries);
		foreach ($dir as $file) {
			if ($file->isDir()) {
				continue;
			}
			$words = file($this->dir_dictionaries . $file->getFilename(),
					FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
			if (in_array($pw, $words)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Examines the password files via grep, if it is available
	 *
	 * @param string $pw  the password to examine
	 * @return bool|null  true or false if known, null if grep isn't available
	 */
	protected function is_pw_dictionary__grep($pw) {
		if ($this->available_grep === false) {
			return null;
		}

		$term = escapeshellarg($pw);
		$dir = escapeshellarg($this->dir_dictionaries);
		exec("grep -iqrx $term $dir", $output, $result);
		if (!$result) {
			return true;
		} elseif ($result == 127) {
			$this->available_grep = false;
			return null;
		}
		return false;
	}

	/**
	 * Is the user's password expired?
	 *
	 * @param int $user_ID  the user's id number
	 * @return mixed  true if expired.  Other replies all evaluate to empty
	 *                but use different types to aid unit testing.
	 *
	 * @uses login_security_solution::$options  for the pw_change_days value
	 * @uses login_security_solution::get_last_changed_time()  to get the last
	 *       time the user changed their password
	 * @uses login_security_solution::set_last_changed_time()  to update the
	 *       user's password changed time if it's not available
	 */
	protected function is_pw_expired($user_ID) {
		if (!$this->options['pw_change_days']) {
			return null;
		}
		$time = $this->get_pw_changed_time($user_ID);
		if (!$time) {
			$this->set_pw_changed_time($user_ID);
			return 0;
		}
		if (((time() - $time) / 86400) > $this->options['pw_change_days']) {
			return true;
		}
		return false;
	}

	/**
	 * Does the password use the site's name, url or description?
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_like_bloginfo($pw) {
		// Note: avoiding get_bloginfo() because it's very expensive.
		if ($this->has_match($pw, get_option('blogname'))) {
			return true;
		}
		if ($this->has_match($pw, get_option('siteurl'))) {
			return true;
		}
		if ($this->has_match($pw, get_option('blogdescription'))) {
			return true;
		}
		return false;
	}

	/**
	 * Does the password contain data from the user's profile?
	 *
	 * @param string $pw  the password to examine
	 * @param WP_User $user  the current user
	 * @return bool
	 */
	protected function is_pw_like_user_data($pw, $user) {
		if (!empty($user->user_login)) {
			if ($this->has_match($pw, $user->user_login)) {
				return true;
			}
		}
		if (!empty($user->user_email)) {
			if ($this->has_match($pw, $user->user_email)) {
				return true;
			}
		}
		if (!empty($user->user_url)) {
			if ($this->has_match($pw, $user->user_url)) {
				return true;
			}
		}
		if (!empty($user->first_name)) {
			if ($this->has_match($pw, $user->first_name)) {
				return true;
			}
		}
		if (!empty($user->last_name)) {
			if ($this->has_match($pw, $user->last_name)) {
				return true;
			}
		}
		if (!empty($user->nickname)) {
			if ($this->has_match($pw, $user->nickname)) {
				return true;
			}
		}
		if (!empty($user->display_name)) {
			if ($this->has_match($pw, $user->display_name)) {
				return true;
			}
		}
		if (!empty($user->aim)) {
			if ($this->has_match($pw, $user->aim)) {
				return true;
			}
		}
		if (!empty($user->yim)) {
			if ($this->has_match($pw, $user->yim)) {
				return true;
			}
		}
		if (!empty($user->jabber)) {
			if ($this->has_match($pw, $user->jabber)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Does the password lack numbers?
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_missing_numeric($pw) {
		return !preg_match('/\d/u', $pw);
	}

	/**
	 * Does the password lack punctuation characters?
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_missing_punct_chars($pw) {
		return !preg_match('/[^\p{L}\p{Nd}]/u', $pw);
	}

	/**
	 * Does the password lack upper-case letters and lower-case letters?
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_missing_upper_lower_chars($pw) {
		if ($this->available_mbstring) {
			$upper = mb_strtoupper($pw);
			$lower = mb_strtolower($pw);
			if ($upper == $lower) {
				if (preg_match('/^[\P{L}\p{Nd}]+$/u', $pw)) {
					// Contains only numbers or punctuation.  Sorry, Charlie.
					return true;
				}
				// Unicameral alphabet.  That's cool.
				return false;
			}
			if ($pw != $lower && $pw != $upper) {
				return false;
			}
			return true;
		} else {
			if (!preg_match('/[[:upper:]]/u', $pw)) {
				return true;
			}
			if (!preg_match('/[[:lower:]]/u', $pw)) {
				return true;
			}
			return false;
		}
	}

	/**
	 * Does the password contain things other than ASCII characters?
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_outside_ascii($pw) {
		return !preg_match('/^[!-~ ]+$/u', $pw);
	}

	/**
	 * Is the user's password the same as one they've used earlier?
	 *
	 * @param string $pw  the password to examine
	 * @param int $user_ID  the user's id number
	 * @return mixed  true if reused.  Other replies all evaluate to empty
	 *                but use different types to aid unit testing.
	 */
	protected function is_pw_reused($pw, $user_ID) {
		if (!$this->options['pw_reuse_count']) {
			return null;
		}
		$hashes = $this->get_pw_hashes($user_ID);
		if (empty($hashes)) {
			return 0;
		}
		foreach ($hashes as $hash) {
			if (wp_check_password($pw, $hash)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Does the password contain characters in alphabetic or numeric order?
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_sequential_codepoints($pw) {
		$chars = $this->split($pw);
		$prior = array_shift($chars);
		$transitions = 0;
		foreach ($chars as $char) {
			// Use "> 2" because some alphabets have the upper and lower case
			// of a letter next to each other, so the next letter in the same
			// case is two points away.
			if (abs( hexdec(bin2hex($char)) - hexdec(bin2hex($prior)) ) > 2) {
				$transitions++;
			}
			$prior = $char;
		}
		return ($transitions < 5);
	}

	/**
	 * Does the password contain groups of characters next to each other
	 * on the keyboard?
	 *
	 * This method uses files stored in the "pw_sequences" directory.  Each
	 * file represents a different keyboard/language.  The files are created
	 * (for left-to-right languages) by typing each character on the keyboard
	 * starting with the top left key, working across the top row, then
	 * starting again on the left side of the next row down.  Do the full
	 * keyboard in upper-case mode first.  Then continue by doing the board
	 * in lower-case mode.  Feel free to add your own files.
	 *
	 * @param string $pw  the password to examine
	 * @return bool
	 */
	protected function is_pw_sequential_file($pw) {
		// First, determine offsets where character type changes occur.
		$split = preg_split('/(?<=[^[:punct:]])([[:punct:]])|(?<=[^[:alpha:]])([[:alpha:]])|(?<=\D)(\d)/', $pw, -1, PREG_SPLIT_OFFSET_CAPTURE|PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

		if (count($split) == 1) {
			// All one character type.
			$parts_fwd = array($pw);
			$parts_rev = array($this->strrev($pw));
		} else {
			// Multiple character types.

			// Don't want info from first element.
			array_shift($split);

			$parts_fwd = array();
			$parts_rev = array();
			$start = 0;

			// Now use those offsets to extract the character type blocks.
			foreach ($split as $part) {
				if ($this->strlen($part[0]) == 1) {
					$length = $part[1] - $start;
					if ($length > 2) {
						// Only examine blocks with 3 or more characters.
						$fwd = $this->substr($pw, $start, $length);
						$parts_fwd[] = $fwd;
						$parts_rev[] = $this->strrev($fwd);
					}
					$start = $part[1];
				}
			}
			$length = $this->strlen($pw) - $start;
			if ($length > 2) {
				// Only add the last block if it's 3 or more characters.
				$fwd = $this->substr($pw, $start, $length);
				$parts_fwd[] = $fwd;
				$parts_rev[] = $this->strrev($fwd);
			}
		}

		if (!$parts_fwd) {
			return false;
		}

		$dir = new DirectoryIterator($this->dir_sequences);
		foreach ($dir as $file) {
			if ($file->isDir()) {
				continue;
			}
			$kbd = file_get_contents($this->dir_sequences . $file->getFileName());

			foreach ($parts_fwd as $key => $part) {
				if ($this->strlen($part) < 3) {
					continue;
				}
				if (strpos($kbd, $part) !== false) {
					return true;
				}
				if (strpos($kbd, $parts_rev[$key]) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * A centralized way to load the plugin's textdomain for
	 * internationalization
	 * @return void
	 */
	protected function load_plugin_textdomain() {
		if (!$this->loaded_textdomain) {
			load_plugin_textdomain(self::ID, false, self::ID . '/languages');
			$this->loaded_textdomain = true;
		}
	}

	/**
	 * Sends a message to my debug log
	 */
	public function log($msg) {
		if (!is_scalar($msg)) {
			$msg = var_export($msg, true);
		}
		file_put_contents('/var/tmp/' . self::ID . '.log', "$msg\n", FILE_APPEND);
	}

	/**
	 * Generates a reproducible hash of the password
	 *
	 * Needed because WP's hash function creates different output each time,
	 * making it impossible to search against.
	 *
	 * @param string $pw  the password to process
	 * @return string  the hashed password
	 *
	 * @uses AUTH_SALT  to prevent rainbow table lookups
	 */
	protected function md5($pw) {
		return md5(AUTH_SALT . $pw);
	}

	/**
	 * Formats and sanity checks IP addresses
	 *
	 * @param string $ip  the IP address to check
	 * @return string  the formatted address.  Empty string if input is bad.
	 */
	protected function normalize_ip($ip) {
		if (!is_string($ip)) {
			return '';
		}
		$ip = trim($ip);
		if ($ip == '') {
			return $ip;
		}
		if (strpos($ip, ':') === false) {
			return $this->normalize_ipv4($ip);
		} else {
			return $this->normalize_ipv6($ip);
		}
	}

	/**
	 * Userland means for sanity checking IPv4 addresses
	 *
	 * @param string $ip  the IPv4 address, in "." separated format
	 * @return string  the IP address.  Empty string if input is bad.
	 */
	protected function normalize_ipv4($ip) {
		$groups = explode('.', $ip);
		if (count($groups) != 4) {
			return '';
		}
		$out = array();
		foreach ($groups as $group) {
			$group = (int) $group;
			if ($group > 255) {
				return '';
			}
			$out[] = $group;
		}
		return implode('.', $out);
	}

	/**
	 * Fills in compressed groups, providing a consistent format usable for
	 * wildcard searching
	 *
	 * Also performs sanity checks.
	 *
	 * The output does not comply with RFC 5952 because compressed addresses
	 * can cause mistakes in our "LIKE '$network_ip%'" queries.
	 *
	 * @link http://tools.ietf.org/html/rfc5952  A Recommendation for IPv6
	 * Address Text Representation
	 *
	 * @param string $ip  the IPv6 address, in ":" separated format
	 * @return string  the formatted address.  Empty string if input is bad.
	 */
	protected function normalize_ipv6($ip) {
		if (strpos($ip, ':::') !== false || $ip == '::') {
			return '';
		}

		$groups = explode(':', $ip);

		$compression_location = strpos($ip, '::');
		if ($compression_location === 0) {
			array_shift($groups);
		} elseif ($compression_location == strlen($ip) - 2) {
			array_pop($groups);
		}

		$count = count($groups);

		if ($count > 8) {
			return '';
		}
		if ($count < 8) {
			if (strpos($groups[$count -1], '.') !== false) {
				// Embedded IPv4.
				$prior = hexdec($groups[$count - 2]);
				if ($prior == 0 || $prior == 65535) {
					$ipv4 = $this->normalize_ipv4($groups[$count - 1]);
					if ($ipv4) {
						if ($prior) {
							return '0:0:0:0:0:ffff:' . $ipv4;
						} else {
							return '0:0:0:0:0:0:' . $ipv4;
						}
					}
				}
				return '';
			}
			if ($compression_location === false) {
				return '';
			}
		}

		$out = array();
		$missing = 9 - $count;
		foreach ($groups as $key => $value) {
			if ($value === '') {
				$out = array_merge($out, array_fill(0, $missing, '0'));
			} else {
				// Ensure no leading 0's and that values are legit.
				if (ctype_digit($value)) {
					$value = (int) $value;
					if ($value > 9999) {
						return '';
					}
				} else {
					$tmp = hexdec($value);
					if ($tmp > 65535) {
						return '';
					}
					$value = dechex($tmp);
				}
				$out[] = $value;
			}
		}

		$ip = implode(':', $out);
		return $ip;
	}

	/**
	 * Sends an email to the blog's administrator telling them a breakin
	 * may have occurred
	 *
	 * @param string $network_ip  a prior result from get_network_ip()
	 * @param string $user_name  the user name from the current login form
	 * @param string $pass_md5  the md5 hashed new password
	 * @param array $fails  the data from get_login_fail()
	 * @param bool $verified_ip  is the user coming form a verified ip?
	 * @return bool
	 *
	 * @uses login_security_solution::get_notify_counts()  for some shared text
	 * @uses wp_mail()  to send the messages
	 */
	protected function notify_breach($network_ip, $user_name, $pass_md5,
			$fails, $verified_ip)
	{
		$this->load_plugin_textdomain();

		$to = $this->sanitize_whitespace($this->get_admin_email());

		$blog = get_option('blogname');
		$subject = sprintf(__("POTENTIAL INTRUSION AT %s", self::ID), $blog);
		$subject = $this->sanitize_whitespace($subject);

		$message =
			sprintf(__("Your website, %s, may have been broken in to.", self::ID),
				$blog) . "\n\n"

			. sprintf(__("Someone just logged in using the following components. Prior to that, some combination of those components were a part of %d failed attempts to log in during the past %d minutes:", self::ID),
				$fails['total'], $this->options['login_fail_minutes']) . "\n\n"

			. $this->get_notify_counts($network_ip, $user_name, $pass_md5, $fails);

		if ($verified_ip) {
			$message .= __("The user's current IP address is one they have verified with your site in the past.  Therefore, the user will NOT be required to confirm their identity via the password reset process.  An email will be sent to them, just in case this actually was a breach.", self::ID) . "\n";
		} else {
			$message .= __("The user has been logged out and will be required to confirm their identity via the password reset functionality.", self::ID) . "\n";
		}

		return wp_mail($to, $subject, $message);
	}

	/**
	 * Sends an email to the current user letting them know a breakin
	 * may have occurred
	 *
	 * @param WP_User $user  the current user
	 * @return bool
	 *
	 * @uses wp_mail()  to send the messages
	 */
	protected function notify_breach_user($user)
	{
		$this->load_plugin_textdomain();

		$to = $this->sanitize_whitespace($user->user_email);

		$blog = get_option('blogname');
		$subject = sprintf(__("POTENTIAL INTRUSION AT %s", self::ID), $blog);
		$subject = $this->sanitize_whitespace($subject);

		$message =
			sprintf(__("Someone just logged into your '%s' account at %s.  Was it you that logged in?  We are asking because the site is being attacked.", self::ID), $user->user_login, get_option('siteurl')) . "\n\n"
			. __("IF IT WAS NOT YOU, please do the following right away:", self::ID) . "\n\n"
			. sprintf(__("1) Log into %s and change your password.", self::ID), wp_login_url()) . "\n\n"
			. sprintf(__("2) Send an email to %s letting them know it was not you who logged in.", self::ID), $this->get_admin_email()) . "\n";

		return wp_mail($to, $subject, $message);
	}

	/**
	 * Sends an email to the blog's administrator telling them that the site
	 * is being attacked
	 *
	 * @param string $network_ip  a prior result from get_network_ip()
	 * @param string $user_name  the user name from the current login form
	 * @param string $pass_md5  the md5 hashed new password
	 * @param array $fails  the data from get_login_fail()
	 * @return bool
	 *
	 * @uses login_security_solution::get_notify_counts()  for some shared text
	 * @uses wp_mail()  to send the messages
	 */
	protected function notify_fail($network_ip, $user_name, $pass_md5,
			$fails)
	{
		$this->load_plugin_textdomain();

		$to = $this->sanitize_whitespace($this->get_admin_email());

		$blog = get_option('blogname');
		$subject = sprintf(__("ATTACK HAPPENING TO %s", self::ID), $blog);
		$subject = $this->sanitize_whitespace($subject);

		$message =
			sprintf(__("Your website, %s, is undergoing a brute force attack.", self::ID),
				$blog) . "\n\n"

			. sprintf(__("There have been at least %d failed attempts to log in during the past %d minutes that used one or more of the following components:", self::ID),
				$fails['total'], $this->options['login_fail_minutes']) . "\n\n"

			. $this->get_notify_counts($network_ip, $user_name, $pass_md5, $fails)

			. sprintf(__("The %s plugin for WordPress is repelling the attack by making their login failures take a very long time.", self::ID),
				self::NAME) . "\n";

		return wp_mail($to, $subject, $message);
	}

	/**
	 * Records the failed login, disconnects the database, then calls sleep()
	 * for increasing amounts of time as more failures come in
	 *
	 * @param string $user_name  the user name from the current login form
	 * @param string $user_pass  the unhashed new password
	 * @param bool $close_db  should mysql_close() be called?
	 * @return int  the number of seconds sleep()'ed (for use by unit tests)
	 *
	 * @uses login_security_solution::get_ip()  to get the IP address
	 * @uses login_security_solution::get_network_ip()  gets the IP's
	 *       "network" part
	 * @uses login_security_solution::md5()  to hash the password
	 * @uses login_security_solution::get_login_fail()  to see if
	 *       they're over the limit
	 * @uses login_security_solution::notify_fail()  to warn of an attack
	 */
	protected function process_login_fail($user_name, $user_pass,
			$close_db = true)
	{
		global $wpdb;

		$ip = $this->get_ip();
		$network_ip = $this->get_network_ip($ip);
		$pass_md5 = $this->md5($user_pass);

		if ($this->is_login_fail_exact_match($ip, $user_name, $pass_md5)) {
			// Don't track duplicates, user is trying bad pw over and over.
			return -1;
		}

		$this->insert_fail($ip, $user_name, $pass_md5);

		$fails = $this->get_login_fail($network_ip, $user_name, $pass_md5);

		if ($this->options['login_fail_notify']
			&& ! ($fails['total'] % $this->options['login_fail_notify']))
		{
			$this->notify_fail($network_ip, $user_name, $pass_md5, $fails);
		}

		if ($fails['total'] < $this->options['login_fail_tier_2']) {
			// Use random, overlapping sleep times to complicate profiling.
			$sleep = rand(1, 7);
		} elseif ($fails['total'] < $this->options['login_fail_tier_3']) {
			$sleep = rand(4, 30);
		} else {
			$sleep = rand(25, 60);
		}

		if (!defined('LOGIN_SECURITY_SOLUTION_TESTING')) {
			if (is_multisite()) {
				// Get this cached before disconnecting the database.
				get_option('users_can_register');
			}

			// Keep login failures from becoming denial of service attacks.
			if ($close_db) {
				mysql_close($wpdb->dbh);
			}

			// Increasingly slow down attackers to the point they'll give up.
			sleep($sleep);
		}

		return $sleep;
	}

	/**
	 * Updates and removes the password related user metadata as needed
	 *
	 * For use when a password is changed.
	 *
	 * @param int $user_ID  the user's id number
	 * @param string $user_pass  the unhashed new password
	 * @return void
	 */
	protected function process_pw_metadata($user_ID, $user_pass) {
		if ($this->options['pw_change_days']) {
			$this->set_pw_changed_time($user_ID);
		}
		if ($this->options['pw_reuse_count']) {
			$this->save_pw_hash($user_ID, wp_hash_password($user_pass));
		}
		$this->delete_pw_force_change($user_ID);
		$this->delete_pw_grace_period($user_ID);
	}

	/**
	 * Sends HTTP Location headers that direct users to the login page
	 *
	 * Also permits adding message ID's to the URI query string that get
	 * interpreted by our login_message() method, which displays them above
	 * the login form.
	 *
	 * Utilizes WordPress' "redirect_to" functionality to bring users back to
	 * where they came from once they have logged in.
	 *
	 * @param string $login_msg_id  the ID representing the message to
	 *                              display above the login form
	 * @param bool $use_rt  use WP's "redirect_to" on successful login?
	 * @param string $action  "login" (default), "rp", or "retrievepassword"
	 * @return void
	 *
	 * @uses login_security_solution::$key_login_msg  to know which $_GET
	 *       parameter to put the message id into
	 * @see login_security_solution::login_message()  for rendering the
	 *      messages
	 * @uses wp_login_url()  to know where the login form is
	 * @uses wp_logout()  to deactivate the current session
	 * @uses wp_redirect()  to perform the actual redirect
	 */
	protected function redirect_to_login($login_msg_id = '', $use_rt = false,
			$action = 'login')
	{
		if ($use_rt && !empty($_SERVER['REQUEST_URI'])) {
			$uri = wp_login_url($_SERVER['REQUEST_URI']);
		} else {
			$uri = wp_login_url();
		}
		$uri = $this->sanitize_whitespace($uri);

		if (strpos($uri, '?') === false) {
			$uri .= '?';
		} else {
			$uri .= '&';
		}
		$uri .= 'action=' . urlencode($action);

		if ($action == 'rp') {
			$uri .= '&key=' . urlencode(@$_GET['key']);
			$uri .= '&login=' . urlencode(@$_GET['login']);
		}

		if ($login_msg_id) {
			$uri .= '&' . urlencode($this->key_login_msg) . '='
					. urlencode($login_msg_id);
		}

		wp_logout();
		wp_redirect($uri);

		if (!defined('LOGIN_SECURITY_SOLUTION_TESTING')) {
			exit;
		}
	}

	/**
	 * Replaces all whitespace characters with one space
	 * @param string $in  the string to clean
	 * @return string  the cleaned string
	 */
	protected function sanitize_whitespace($in) {
		return preg_replace('/\s+/', ' ', $in);
	}

	/**
	 * Logs password hashes to prevent passwords from being reused frequently
	 *
	 * Note: duplicate hashes are not stored.
	 *
	 * @param int $user_ID  the user's id number
	 * @param string $new_hash  the wp hashed password to save
	 * @return mixed  true on success, 1 if hash is already stored
	 */
	protected function save_pw_hash($user_ID, $new_hash) {
		$hashes = $this->get_pw_hashes($user_ID);

		if (in_array($new_hash, $hashes)) {
			return 1;
		}

		$hashes[] = $new_hash;

		$cut = count($hashes) - $this->options['pw_reuse_count'];
		if ($cut > 0) {
			array_splice($hashes, 0, $cut);
		}

		update_user_meta($user_ID, $this->umk_hashes, $hashes);

		return true;
	}

	/**
	 * Stores the user's current IP address
	 *
	 * Note: saves up to 10 adddresses, duplicates are not stored.
	 *
	 * @param int $user_ID  the user's id number
	 * @param string $new_ip  the ip address to add
	 * @return mixed  true on success, 1 if IP is already stored, -1 if IP empty
	 */
	protected function save_verified_ip($user_ID, $new_ip) {
		if (!$new_ip) {
			return -1;
		}

		$ips = $this->get_verified_ips($user_ID);

		if (in_array($new_ip, $ips)) {
			return 1;
		}

		$ips[] = $new_ip;

		$cut = count($ips) - 10;
		if ($cut > 0) {
			array_splice($ips, 0, $cut);
		}

		update_user_meta($user_ID, $this->umk_verified_ips, $ips);

		return true;
	}

	/**
	 * Stores the present time in the given user's "last active" metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int|bool  the record number if added, TRUE if updated, FALSE
	 *                   if error
	 */
	protected function set_last_active($user_ID) {
		return update_user_meta($user_ID, $this->umk_last_active, time());
	}

	/**
	 * Replaces the default option values with those stored in the database
	 * @uses login_security_solution::$options  to hold the data
	 */
	protected function set_options() {
		if (is_multisite()) {
			switch_to_blog(1);
			$options = get_option($this->option_name);
			restore_current_blog();
		} else {
			$options = get_option($this->option_name);
		}
		if (!is_array($options)) {
			$options = array();
		}
		$this->options = array_merge($this->options_default, $options);
	}

	/**
	 * Stores the present time in the given user's "password changed" metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int|bool  the record number if added, TRUE if updated, FALSE
	 *                   if error
	 */
	protected function set_pw_changed_time($user_ID) {
		return update_user_meta($user_ID, $this->umk_changed, time());
	}

	/**
	 * Puts the "force password change" flag into the user's metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int|bool  the record number if added, TRUE if updated, FALSE
	 *                   if error
	 */
	protected function set_pw_force_change($user_ID) {
		return update_user_meta($user_ID, $this->umk_pw_force_change, 1);
	}

	/**
	 * Stores the present time in the given user's "password grace period"
	 * metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int|bool  the record number if added, TRUE if updated, FALSE
	 *                   if error
	 */
	protected function set_pw_grace_period($user_ID) {
		return update_user_meta($user_ID, $this->umk_grace_period, time());
	}

	/**
	 * Breaks a password up into an array of individual characters
	 *
	 * @param string $pw  the password to examine
	 * @return array
	 */
	protected function split($pw) {
		return preg_split('/(?<!^)(?!$)/u', $pw);
	}

	/**
	 * Determines how long a string is using mb_strlen() if available
	 *
	 * @param string $pw  the string to evaluate
	 * @return int  the length of the string
	 */
	protected function strlen($pw) {
		if ($this->available_mbstring) {
			return mb_strlen($pw);
		} else {
			return strlen($pw);
		}
	}

	/**
	 * Removes non-letter and non-numeric characters from the password
	 *
	 * @param string $pw  the password to examine
	 * @return string
	 */
	protected function strip_nonword_chars($pw) {
		return preg_replace('/[^\p{L}\p{Nd}]/u', '', $pw);
	}

	/**
	 * Reverses a string in a multibyte safe way
	 *
	 * @param string $pw  the string to examine
	 * @return string  the reversed string
	 */
	protected function strrev($pw) {
		return implode('', array_reverse($this->split($pw)));
	}

	/**
	 * Extracts parts of strings, using mb_substr() if available
	 *
	 * @param string $pw  the string to evaluate
	 * @param int $start  the starting index (0 based)
	 * @param int $length  the number of characters to get
	 * @return string  the desired part of the password
	 */
	protected function substr($pw, $start, $length) {
		if ($this->available_mbstring) {
			return mb_substr($pw, $start, $length);
		} else {
			return substr($pw, $start, $length);
		}
	}

	/**
	 * Is the password valid?
	 *
	 * @param WP_User|string  the user object or password to be examined
	 * @param WP_Error $errors  the means to provide specific error messages
	 * @return bool
	 */
	public function validate_pw($user, &$errors = null) {
		$this->load_plugin_textdomain();

		if (is_object($user)) {
			$all_tests = true;

			if (empty($user->user_pass)) {
				if ($errors !== null) {
					$errors->add(self::ID,
						$this->err(__("Password not set.", self::ID)),
						array('form-field' => 'pass1')
					);
				}
				return false;
			}
			$pw = $user->user_pass;
		} else {
			$all_tests = false;
			$pw = $user;
		}

		if (!is_string($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords must be strings.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}

		$pw = trim($pw);

		if ($this->available_mbstring === null) {
			$this->available_mbstring = extension_loaded('mbstring');
		}

		if (!$this->available_mbstring
			&& $this->is_pw_outside_ascii($pw))
		{
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords must use ASCII characters.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}

		$length = $this->strlen($pw);
		if ($length < $this->options['pw_complexity_exemption_length']) {
			$enforce_complexity = true;
		} else {
			$enforce_complexity = false;
		}

		// NOTE: tests ordered from fastest to slowest.

		if ($length < $this->options['pw_length']) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Password is too short.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($enforce_complexity && $this->is_pw_missing_numeric($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(sprintf(__("Passwords must either contain numbers or be %d characters long.", self::ID), $this->options['pw_complexity_exemption_length'])),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($enforce_complexity && $this->is_pw_missing_punct_chars($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(sprintf(__("Passwords must either contain punctuation marks / symbols or be %d characters long.", self::ID), $this->options['pw_complexity_exemption_length'])),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($enforce_complexity && $this->is_pw_missing_upper_lower_chars($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(sprintf(__("Passwords must either contain upper-case and lower-case letters or be %d characters long.", self::ID), $this->options['pw_complexity_exemption_length'])),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}

		if ($this->is_pw_sequential_file($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords can't be sequential keys.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($this->is_pw_sequential_codepoints($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords can't have that many sequential characters.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}

		$non_leet = $this->convert_leet_speak($pw);
		$stripped = $this->strip_nonword_chars($non_leet);

		if ($all_tests
			&& ($this->is_pw_like_user_data($pw, $user)
				|| $this->is_pw_like_user_data($stripped, $user)))
		{
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords can't contain user data.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($this->is_pw_like_bloginfo($pw)
			|| $this->is_pw_like_bloginfo($stripped))
		{
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords can't contain site info.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($all_tests && $this->is_pw_dictionary($pw)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Password is too common.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}
		if ($this->is_pw_dict_program($stripped)) {
			if ($errors !== null) {
				$errors->add(self::ID,
					$this->err(__("Passwords can't be variations of dictionary words.", self::ID)),
					array('form-field' => 'pass1')
				);
			}
			return false;
		}

		return true;
	}
}
