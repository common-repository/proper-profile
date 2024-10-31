<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Proper_Profile
 * @subpackage Proper_Profile/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Proper_Profile
 * @subpackage Proper_Profile/admin
 * @author     Your Name <email@example.com>
 */
class Proper_Profile_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $proper_profile    The ID of this plugin.
	 */
	private $proper_profile;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $proper_profile       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $server;

	public function __construct( $proper_profile, $version ) {

		$this->proper_profile = $proper_profile;
		$this->version = $version;
		$this->server = getenv('PROPER_PROFILE_SERVER') ? getenv('PROPER_PROFILE_SERVER') : 'https://properprofile.com';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Proper_Profile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Proper_Profile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->proper_profile, plugin_dir_url( __FILE__ ) . 'css/proper-profile-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->proper_profile . '-fa', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->proper_profile . '-tooltipster', plugin_dir_url( __FILE__ ) . 'css/tooltipster.bundle.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->proper_profile . '-tooltipster-theme', plugin_dir_url( __FILE__ ) . 'css/tooltipster-sideTip-shadow.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Proper_Profile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Proper_Profile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->proper_profile, plugin_dir_url( __FILE__ ) . 'js/proper-profile-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->proper_profile . '-tooltipster', plugin_dir_url( __FILE__ ) . 'js/tooltipster.bundle.min.js', array( 'jquery' ), $this->version, false );

	}

	public function redirect_after_activation(){
		if(get_option('proper_profile_activation_redirect')){
			delete_option('proper_profile_activation_redirect');
			if(!isset($_GET['activate-multi'])){
				wp_redirect(admin_url( 'options-general.php?page=proper-profile' ));
			}
		}
	}

	public function get_proper_profile_ajax(){


		$person = $this->get_person_json($_GET['email']);
		if(is_array($person)){
			$this->proper_profile_widget($person,'proper-profile-widget-single');
		}else if($person == 401){
			echo '<p>Proper Profile seems to not have been enabled yet.<br/> Please enable it in your <a href="' . admin_url( 'options-general.php?page=proper-profile' ) . '">settings</a>.</p>';
		}else if($person == 403){
			$options = get_option( 'proper_profile_options' );
			echo '<p>You have exceeded your monthly Proper Profile quota!<br/>This means you can\'t get any more Proper Profiles this month.<br/>If you\'d like to keep getting proper information about your customers, please <a href="' . $this->server . '/upgrade?api_key=' . $options['api_key'] . '">upgrade your account</a>.</p>';
		}else if($person == 404){
			echo '<p>Sorry, no info regarding this email address.</p>';
		}else if($person == 500){
			echo '<p>Sorry, there was an internal error in ProperProfile servers. Please try again later.</p>';
		}else{
			var_dump($person);
			// echo '<p>Sorry 500, no info regarding this email address.</p>';
		}

		wp_die(); // this is required to terminate immediately and return a proper response

	}

	public function plugins_loaded(){
		require( plugin_dir_path( __FILE__ ) . '../includes/class-proper-profile-activator.php' );
		Proper_Profile_Activator::update_db_check();
	}

	public function buffer_callback($html){
		$replace = '$0<span data-email="$0" class="proper-profile-tooltip"><img class="proper-profile-badge" src="' . plugin_dir_url( __FILE__ ) . 'images/icon_new.png' . '"></span>';

		$replaced = preg_replace('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}(?=[\s<>])/m',$replace,$html);

		return $replaced;
	}

	public function buffer_start(){
		if(is_admin()){
			ob_start(array($this,'buffer_callback'));
		}
	}

	public function buffer_end(){
		if(is_admin()){
			ob_end_flush();
		}
	}

	public function admin_notices(){

		// relevant pages edit-shop_order shop_order


		// notice when no api key present, we do this on ALL pages of admin
		$proper_profile_options = get_option('proper_profile_options');
		if(!$proper_profile_options || !isset($proper_profile_options['api_key']) || !$proper_profile_options['api_key']){
			$this->admin_notice_missing_api_key();
		}else{

			// now checks are only if we're in woo relevant pages where the widget shows
			$screen = get_current_screen();
			// v1.1 we're no longed that hooked to woo, so we check everywhere....
			// if($screen->id == 'edit-shop_order' || $screen->id == 'shop_order'){
				$proper_profile_api_key = $proper_profile_options['api_key'];
				$proper_profile_api_url = $this->server . "/api/v1/verify?api_key={$proper_profile_api_key}";

				$response = wp_remote_get($proper_profile_api_url);
				if(is_wp_error($response)){
					// TBD what to do here?
				}elseif($response['response']['code'] == 401){
					$this->admin_notice_bad_api_key();
				}elseif($response['response']['code'] == 403){
					$this->admin_notice_upgrade_required();
				}elseif($response['response']['code'] == 500){
					// TBD what to do here?
				}else{
					// All is good, no notice required
				}
			// }


		}
	}

	private function admin_notice_missing_api_key(){
		require plugin_dir_path( __FILE__ ) . 'partials/admin-notice-missing-api-key.php';
	}

	private function admin_notice_upgrade_required(){
		$proper_profile_options = get_option('proper_profile_options');
		$api_key = $proper_profile_options['api_key'];
		$server = $this->server;
		require plugin_dir_path( __FILE__ ) . 'partials/admin-notice-upgrade-required.php';
	}

	private function admin_notice_bad_api_key(){
		require plugin_dir_path( __FILE__ ) . 'partials/admin-notice-bad-api-key.php';
	}

	public function add_order_proper_profile_column_header($columns){

		$new_columns = array();
		$proper_profile_options = get_option('proper_profile_options');
		if(is_array($proper_profile_options) && isset($proper_profile_options['is_display_in_orders_list'])){
			foreach ( $columns as $column_name => $column_info ) {

	        $new_columns[ $column_name ] = $column_info;

	        // if ( 'order_total' === $column_name ) {
					if ( 'order_title' === $column_name ) {
	            $new_columns['order_proper_profile'] = __( 'Proper Profile', 'proper-profile' );
	        }
	    }
		}else{
			$new_columns = $columns;
		}

    return $new_columns;
	}

	public function add_order_proper_profile_column_content($column){
		global $post;

		if ( 'order_proper_profile' === $column ) {
			$order = wc_get_order( $post->ID );
			$person = $this->get_person_json($order->get_billing_email());
			if($person){
				$this->proper_profile_widget($person,'proper-profile-widget-in-list');
			}
		}
	}

	private function get_person_json($email){
		global $wpdb;
		$ret = false;
		$table_name = $wpdb->prefix . 'proper_profile_persons';
		$json = $wpdb->get_var($wpdb->prepare("select data from {$table_name} where email=%s and cached_at > NOW() - INTERVAL 120 DAY",$email));
		if($json){
			$ret = json_decode($json,true);
		}else{
			$proper_profile_options = get_option('proper_profile_options');
			$proper_profile_api_key = $proper_profile_options['api_key'];
			$proper_profile_api_url = $this->server . "/api/v1/person?api_key={$proper_profile_api_key}&email={$email}";

			$response = wp_remote_get($proper_profile_api_url);
			if(is_wp_error($response)){
				// TBD what to do here?
				$ret = $response->get_error_message();
			}elseif($response['response']['code'] == 401){
				$ret = 401;
			}elseif($response['response']['code'] == 403){
				$ret = 403;
			}elseif($response['response']['code'] == 404){
				$ret = 404;
			}elseif($response['response']['code'] == 500){
				$ret = 500;
			}else{
				$json = wp_remote_retrieve_body($response);
				$sql = "INSERT INTO {$table_name} (email,data) VALUES(%s,%s) ON DUPLICATE KEY UPDATE data=%s,cached_at=NOW()";
				$wpdb->query($wpdb->prepare($sql,$email,$json,$json));
				// TBD what if the insert failed here? dont think we should wp_die here,
				// coz in the worst case it just won't be cached, but the user will get repsonse
				$ret = json_decode($json,true);
			}
		}
		return $ret;
	}


	private function proper_profile_widget($person,$widget_class){
		?>
			<div class="<?=$widget_class?>">
		<?php

		// a pic
		if(array_key_exists('photos',$person) && is_array($person['photos'])){
			?>
			<div class="proper-profile-profile-pic">
				<img src="<?=$person['photos'][0]['url']?>"/>
			</div>
			<?php
		}

		// name of dude
		if(array_key_exists('contactInfo',$person)){
			if(array_key_exists('fullName',$person['contactInfo'])){
				?>
					<div class="proper-profile-full-name">
						<?=$person['contactInfo']['fullName']?>
					</div>
				<?php

			}
		}

		// location of dude
		if(array_key_exists('demographics',$person)){
			if(array_key_exists('locationDeduced',$person['demographics'])){
				if(array_key_exists('normalizedLocation',$person['demographics']['locationDeduced'])){
					?>
						<div class="proper-profile-location">
							<?=$person['demographics']['locationDeduced']['normalizedLocation']?>
						</div>
					<?php
				}
			}
		}

		// job of dude
		if(array_key_exists('organizations',$person) && is_array($person['organizations'])){
			?>
				<div class="proper-profile-job">
					<?=$person['organizations'][0]['title']?> at <?=$person['organizations'][0]['name']?>
				</div>
			<?php
		}

		// a row of profile social icons
		if(array_key_exists('socialProfiles',$person) && is_array($person['socialProfiles'])){
			?>
			<div class="proper-profile-icons">
			<?php
				foreach ( $person['socialProfiles'] as $social_profile ) {
					$fa_class = $this->get_fa_class($social_profile['typeId']);
					if($fa_class){
						?>
						<span class="proper-profile-icon"><a href="<?=$social_profile['url']?>"><i class="fa fa-2x <?=$fa_class?>"></i></a></span>
						<!-- <i class="fa fa-facebook-official"><?=$social_profile['url']?></i> -->
						<?php
					}else{
						// we have no icon to display....
					}
					// echo $social_profile['url'] . " ";
				}
			?>
			</div>
			<?php
		}

		?>
			</div>
		<?php

	}

	private function get_fa_class($social_profile_type_id){
		$class = '';
		switch($social_profile_type_id){
			case 'facebook':
				$class = 'fa-facebook-official proper-profile-button-facebook';
				break;
			case 'twitter':
				$class = 'fa-twitter-square proper-profile-button-twitter';
				break;
			case 'linkedin':
				$class = 'fa-linkedin-square proper-profile-button-linkedin';
				break;
			case 'angellist':
				$class = 'fa-angellist';
				break;
			case 'foursquare':
				$class = 'fa-foursquare  proper-profile-button-foursquare';
				break;
			case 'github':
				$class = 'fa-github-square  proper-profile-button-github';
				break;
			case 'google':
				$class = 'fa-google-plus-square proper-profile-button-google';
				break;
			case 'pinterest':
				$class = 'fa-pinterest-square proper-profile-button-pinterest';
				break;
			case 'meetup':
				$class = 'fa-meetup proper-profile-button-meetup';
				break;
			case 'reddit':
				$class = 'fa-reddit-square proper-profile-button-reddit';
				break;
			case 'tumblr':
				$class = 'fa-tumblr-square proper-profile-button-tumblr';
				break;
			case 'flickr':
				$class = 'fa-flickr proper-profile-button-flickr';
				break;
			case 'youtube':
				$class = 'fa-youtube-square proper-profile-button-youtube';
				break;
			default:
				$break;
		}
		return $class;
	}

	public function single_order_widget($order){

		$proper_profile_options = get_option('proper_profile_options');
		if(is_array($proper_profile_options) && isset($proper_profile_options['is_display_in_order_page'])){
			$person = $this->get_person_json($order->get_billing_email());
			if($person){
				$this->proper_profile_widget($person,'proper-profile-widget-single');
			}
		}
	}

	public function add_action_links($links, $file){
		// if ($file == plugin_basename(__FILE__)) {
		if ($file == 'proper-profile/proper-profile.php') {
			$mylinks = array(
			 '<a href="' . admin_url( 'options-general.php?page=proper-profile' ) . '">Settings</a>',
			);
			return array_merge( $links, $mylinks );
		}else{
			return $links;
		}
	}

	public function get_api_key(){
		// verify nonce
		if( !$this->has_valid_nonce('proper_profile_get_api_key','proper_profile_get_api_key_nonce')){
			wp_die('trying to spoof us aye?');
		}

		if ( ! current_user_can( 'manage_options' ) ) {
		 	wp_die('trying to spoof us aye?');
		}


		$proper_profile_api_url = $this->server . "/api/v1/signup";

		$current_user = wp_get_current_user();
		$blog_url = get_bloginfo('url');

		$response = wp_remote_post($proper_profile_api_url,array(
			'headers' => array(
				'Content-Type' => 'application/json'
			),
			'body' => wp_json_encode(array(
				'email' => $current_user->user_email,
				'agent' => 'wordpress',
				'extra' => array(
					'site_url' => $blog_url
				)
			))
		));

		if(is_wp_error($response)){
			// TBD what to do?
			wp_die($response->get_error_message());
		}elseif($response['response']['code'] == 500){
			wp_die(wp_remote_retrieve_body( $response ));
		}else{
			$json = wp_remote_retrieve_body( $response );
			$ret = json_decode($json,true);
			update_option('proper_profile_options',array(
				'api_key' => $ret['api_key']
			));
			wp_redirect(admin_url( 'options-general.php?page=proper-profile&settings-updated=1' ));

		}



	}

	function proper_profile_options_page() {
	 // add top level menu page
	 add_options_page(
		 'Proper Profile',
		 'Proper Profile',
		 'manage_options',
		 'proper-profile',
		 array($this,'proper_profile_options_page_html')
	 );
	}


	function settings_init() {
	 // register a new setting for "proper-profile" page
	 register_setting( 'proper-profile', 'proper_profile_options' );

	 // register a new section in the "proper-profile" page
	 add_settings_section(
		'proper_profile_section_credentials',
		__( 'Credentials Settings', 'proper-profile' ),
		array($this,'proper_profile_section_credentials_cb'),
		'proper-profile'
	 );

	 // add_settings_section(
	 // 	'proper_profile_section_display',
	 // 	__( 'Display Settings', 'proper-profile' ),
	 // 	array($this,'proper_profile_section_display_cb'),
	 // 	'proper-profile'
	 // );

	 // register a new field in the "wporg_section_developers" section, inside the "wporg" page
	 add_settings_field(
		 'proper_profile_field_api_key',
		 __( 'API Key', 'proper-profile' ),
		 array($this,'proper_profile_field_api_key_cb'),
		 'proper-profile',
		 'proper_profile_section_credentials',
		 [
			 'label_for' => 'proper_profile_field_api_key',
			 'class' => 'proper_profile_row',
			 'proper_profile_custom_data' => 'custom',
		 ]
	 );

	 // add_settings_field(
	 // 		'proper_profile_field_is_display_in_orders_list',
	 // 		__( 'Display in Orders List', 'proper-profile' ),
	 // 		array($this,'proper_profile_field_is_display_in_orders_list_cb'),
	 // 		'proper-profile',
	 // 		'proper_profile_section_display',
	 // 		[
	 // 			'label_for' => 'proper_profile_field_is_display_in_orders_list',
	 // 			'class' => 'proper_profile_row',
	 // 			'proper_profile_custom_data' => 'custom',
	 // 		]
	 // 	);
   //
		// add_settings_field(
 	 // 		'proper_profile_field_is_display_in_order_page',
 	 // 		__( 'Display in Orders Page', 'proper-profile' ),
 	 // 		array($this,'proper_profile_field_is_display_in_order_page_cb'),
 	 // 		'proper-profile',
 	 // 		'proper_profile_section_display',
 	 // 		[
 	 // 			'label_for' => 'proper_profile_field_is_display_in_order_page',
 	 // 			'class' => 'proper_profile_row',
 	 // 			'proper_profile_custom_data' => 'custom',
 	 // 		]
 	 // 	);


	}



	function proper_profile_section_credentials_cb( $args ) {
	 ?>
	 <!-- <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Credentials Settings', 'proper-profile' ); ?></p> -->
	 <?php
	}

	function proper_profile_section_display_cb( $args ) {
	 ?>
	 <!-- <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Credentials Settings', 'proper-profile' ); ?></p> -->
	 <?php
	}


	function proper_profile_field_api_key_cb( $args ) {
	 // get the value of the setting we've registered with register_setting()
	 $options = get_option( 'proper_profile_options' );
	 // output the field
	 ?>
	 <input type="text" id="api_key"
	 name="proper_profile_options[api_key]"
	 placeholder="Paste your API key here..."
	 value="<?php echo isset( $options['api_key'] ) ? $options[ 'api_key' ]: '' ; ?>"
	 class="regular-text"
	 >
	 <p class="description">
	 <?php
	 printf(
    esc_html__( 'Please %1$s and fill it in here.', 'proper-profile' ),
    sprintf(
        '<a href="%s">%s</a>',
        $this->server,
        esc_html__( 'get your API key from ProperProfile', 'proper-profile' )
        )
    );
	 ?>
	 </p>
	 <?php
	}

	// function proper_profile_field_is_display_in_orders_list_cb($args){
	// 	$options = get_option( 'proper_profile_options' );
	// 	$is_display_in_orders_list = 0;
  //   if ( $options === false ) {
  //       // nothing is set, so apply the default here
  //       $is_display_in_orders_list = 1;
  //   }
  //   else if( is_array( $options ) && isset( $options['is_display_in_orders_list'] ) ) {
  //       // classy_show_resume is checked
  //       $is_display_in_orders_list = $options['is_display_in_orders_list'];
  //   }
	// 	$html = '<input type="checkbox" id="proper_profile_field_is_display_in_orders_list" name="proper_profile_options[is_display_in_orders_list]" value="1" ' . checked( $is_display_in_orders_list, 1, false ) . '/>';
  //   $html .= '<label for="proper_profile_field_is_display_in_orders_list">Whether or not to display the Proper Profile widget within the orders table.</label>';
  //
  //   echo $html;
	// }
  //
	// function proper_profile_field_is_display_in_order_page_cb($args){
	// 	$options = get_option( 'proper_profile_options' );
	// 	$is_display_in_order_page = 0;
  //   if ( $options === false ) {
  //       // nothing is set, so apply the default here
  //       $is_display_in_order_page = 1;
  //   }
  //   else if( is_array( $options ) && isset( $options['is_display_in_order_page'] ) ) {
  //       // classy_show_resume is checked
  //       $is_display_in_order_page = $options['is_display_in_order_page'];
  //   }
  //
	// 	$html = '<input type="checkbox" id="proper_profile_field_is_display_in_order_page" name="proper_profile_options[is_display_in_order_page]"  value="1" ' . checked( $is_display_in_order_page, 1, false ) . '/>';
  //   $html .= '<label for="proper_profile_field_is_display_in_order_page">Whether or not to display the Proper Profile widget within a single order page.</label>';
  //
  //   echo $html;
	// }

	function proper_profile_options_page_html() {
	 // check user capabilities
	 if ( ! current_user_can( 'manage_options' ) ) {
	 	return;
	 }

	 // add error/update messages

	 // check if the user have submitted the settings
	 // wordpress will add the "settings-updated" $_GET parameter to the url
	 if ( isset( $_GET['settings-updated'] ) ) {
	 // add settings saved message with the class of "updated"
	 	add_settings_error( 'proper_profile_messages', 'proper_profile_message', __( 'Settings Saved', 'proper-profile' ), 'updated' );
	 }

	 // show error/update messages
	 settings_errors( 'proper_profile_messages' );

	 $options = get_option( 'proper_profile_options' );
	 if(!isset($options['api_key']) || !$options['api_key']){
		 $current_user = wp_get_current_user();
		 $blog_url = get_bloginfo('url');
		 ?>
		 <div class="wrap">
			 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			 <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
					 <input type="hidden" name="action" value="proper_profile_get_api_key">
					 <?php wp_nonce_field( 'proper_profile_get_api_key', 'proper_profile_get_api_key_nonce' ); ?>
					 <?php submit_button('Enable Proper Profile'); ?>
					 <p class="description">By clicking this button you agree to create a ProperProfile account at https://properprofile.com for <?=$blog_url?> using <?=$current_user->user_email?> as an email.</p>
			 </form>



		 </div>
		 <?php
	 }else{




		 $proper_profile_api_key = $options['api_key'];
		 $proper_profile_api_url = $this->server . "/api/v1/status?api_key={$proper_profile_api_key}";

		 $response = wp_remote_get($proper_profile_api_url);
		 if(is_wp_error($response)){
			 wp_die($response->get_error_message());
		 }else{
			 $json = wp_remote_retrieve_body( $response );
			 $ret = json_decode($json,true);

			 global $wpdb;
			 $table_name = $wpdb->prefix . 'proper_profile_persons';
			 $persons_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
			 if(is_null($persons_count)){
				 wp_die('couldn\'t access Proper Profile database table!');
			 }

			 ?>
			 <div class="wrap">
				 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

				 <p>You've fetched <strong><?=$persons_count?></strong> Proper Profiles so far.</p>
				 <p>You've used <strong><?=$ret['used']?></strong> out of <strong><?=$ret['from']?></strong> Proper Profiles queries in this billing period.</p>
				 <form method="GET" action="<?=$this->server?>/upgrade">
					 <input type="hidden" name="api_key" value="<?=$options['api_key']?>">
					 <?php submit_button('Upgrade Your Account'); ?>
				 </form>
			</div>
			 <?php

		 }


	 }


	}

	private function has_valid_nonce($action, $field) {

		// If the field isn't even in the $_POST, then it's invalid.
		if ( ! isset( $_POST[$field] ) ) { // Input var okay.
				return false;
		}

		$field  = wp_unslash( $_POST[$field] );

		return wp_verify_nonce( $field, $action );

	}



}
