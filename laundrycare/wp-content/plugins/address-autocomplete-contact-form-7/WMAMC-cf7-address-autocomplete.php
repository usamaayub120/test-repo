<?php
/*

* Plugin Name: Address autocomplete Contact Form 7 

* Description: This plugin will add a new field to contact form 7 for autocomplete address.

* Version: 1.1.1

* Author: Webman Technologies

* Requires at least: 4.4

* Tested up to: 4.9

* Text Domain: WMAMC_cf7_address_autocomplete_trdom 

* License: GPLv2 or later



Address autocomplete Contact Form 7 is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Address autocomplete Contact Form 7 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>


 Copyright (C) 2018  Webman Technologies


 */

defined( 'ABSPATH' ) or exit;

/*	Contact Form 7 check 	*/
$active_plugins = get_option( 'active_plugins', array() );
if( !in_array( 'contact-form-7/wp-contact-form-7.php',$active_plugins ) )
{
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
	deactivate_plugins('WMAMC-cf7-address-autocomplete/WMAMC-cf7-address-autocomplete.php');
	if( isset( $_GET['activate'] ))
      unset( $_GET['activate'] );
}

class WMAMC_cf7_address_autocomplete
{
	private $fields, $names;
	protected static $instance;
	protected $adminpage;
	protected $generator;
	
	public function __construct()
	{
		
		/*	 plugin activation	*/
		
		register_activation_hook(__FILE__, array( $this, 'WMAMC_cf7_address_autocomplete_plugin_activate') );
		
		
		/*	plugin deactivation	*/
		
		register_deactivation_hook(__FILE__, array( $this, 'WMAMC_cf7_address_autocomplete_plugin_deactivate') );
		
		
		/*	add address autocomplete field 	*/
		
		add_action('wpcf7_init', array($this, 'WMAMC_cf7_address_autocomplete_create_address_field'));
		
		
		/*	create google place api menu under contacts menu	*/
		
		add_action('admin_menu', array($this,'WMAMC_cf7_address_autocomplete_menu_item'));
		
		
		/*	display fields on google place api page	*/
		
		add_action('admin_init', array($this,'WMAMC_cf7_address_autocomplete_display_gpa_fields'));
		
		/*	check version of contact form 7	*/
		add_action('admin_init', array($this,'WMAMC_cf7_address_autocomplete_wpcf7_version_check'));
		
		/*	create tag for address autocomplete in admin panel*/
		
		add_action( 'wpcf7_admin_init', array($this,'WMAMC_cf7_address_autocomplete_add_tag_generator'), 20 );
		
		
		/*	add shortcode for autocomplete field*/
		
		add_action( 'wpcf7_init', array($this, 'WMAMC_cf7_address_autocomplete_shortcode_add')); 
		
		
		/*	validation for autocomplete field*/
		
		add_filter( 'wpcf7_validate_address_geo_autocomplete',  array($this, 'WMAMC_cf7_address_autocomplete_filter_validation' ), 10, 2);
		add_filter( 'wpcf7_validate_address_geo_autocomplete*',  array($this, 'WMAMC_cf7_address_autocomplete_filter_validation' ), 10, 2);
		
		
		/*	messages for address autocomplete field	*/
		
		add_filter( 'wpcf7_messages', array($this, 'WMAMC_cf7_address_autocomplete_messages'));	
		
		
		/*	include plugin script*/
		add_action( 'wp_footer', array($this,'WMAMC_cf7_gpa_plugin_script'), 21, 1 );
		
		
		add_action('admin_init', array($this,'WMAMC_cf7_address_autocomplete_load_plugin'));
		
		
		/*	load google api script*/
		
		add_action( 'wp_enqueue_scripts', array($this,'WMAMC_cf7_gpa_load_user_api' ));
		
		
		/* Validation filter */

		
	// end of construct function
	}
	
	
	
	
	/**********************
		check if current version of plugin
		********************************************/
	public function WMAMC_cf7_address_autocomplete_wpcf7_version_check()
	{
		$version = get_option( 'WMAMC_cf7_address_autocomplete_version' );
		
		return version_compare($version, '1.0.0', '=') ? true : false;
	}

	
	/**
	 * Display an error message when parent plugin is missing
	 */
	function WMAMC_cf7_address_autocomplete_self_deactivate_notice()
	{
	?>
		<div class="notice notice-error">
			<?php    echo "<h2>" . __( 'Please install and activate contact form 7 plugin before activating this plugin.', 'WMAMC_cf7_address_autocomplete_trdom' ) . "</h2>"; ?>
		</div>
	<?php
	}
	
	
	/******************
		creating Google Place API menu item under contacts main menu
		***************************************************************/
		
	public function WMAMC_cf7_address_autocomplete_menu_item()
	{
		$this->adminpage = add_submenu_page(
											'wpcf7',
											__('Google Place API','google-place-api'),
											__('Google Place API','google-place-api'), 
											'manage_options',
											'google-place-api',
											array($this, 'WMAMC_cf7_google_place_admin' ),
											'dashicons-admin-post'
										);
	}
	
	
	public function WMAMC_cf7_address_autocomplete_load_plugin()
	{

		/* do stuff once right after activation */

		if (is_admin() && get_option('Activated_Plugin') == 'WMAMC-cf7-address-autocomplete') 
		{
			delete_option( 'Activated_Plugin' );
			
			if (!class_exists('WPCF7')) 
			{
				add_action('admin_notices', array($this,'WMAMC_cf7_address_autocomplete_self_deactivate_notice'));
				
				/** Deactivate our plugin if contact form 7 is not installed ***/
				
				deactivate_plugins(plugin_basename(__FILE__));
				
				if (isset($_GET['activate'])) 
				{
					unset($_GET['activate']);
				}
			}
		}
	}
	
	
	/***********************
		creating google place api page in admin panel
		**********************************************************/
		
	public function WMAMC_cf7_google_place_admin()
	{
?>
		<div class="wrap">
			<h1>Google Places API Info.</h1>
			<form method="post" action="options.php">
				<?php
					settings_fields('section');
					do_settings_sections('orem-cf7-gpa-options');      
					submit_button(); 
				?>
			
			</form>
		</div>
<?php
	}
	


	/****************************
		creating text field for Google Places API Key
		****************************************************************/
	
	public function WMAMC_cf7_gpa_display_api_key_element() 
	{
?>
		<input type="text" required name="orem_cf7_geo_api_key" id="api_key" value="<?php echo get_option('orem_cf7_geo_api_key')?>" />
<?php
	}
	
	/*****************
		displaying fields on google place api page
		*******************************************************/
	public function WMAMC_cf7_address_autocomplete_display_gpa_fields()
	{
		$user_permission = current_user_can( 'update_core' );
		if ($user_permission == true)
		{
			add_settings_section('section', 'All Settings', null, 'orem-cf7-gpa-options');
			
			add_settings_field('orem_cf7_geo_api_key', 'Google Places API Key', array($this,'WMAMC_cf7_gpa_display_api_key_element'), 'orem-cf7-gpa-options', 'section');
	  
			register_setting('section', 'orem_cf7_geo_api_key');
		}
	}
	
	
	/***************
		Loads google place api scripts
		****************************************/
		
	public function WMAMC_cf7_gpa_load_user_api()
	{
	  $gpa_page = get_option( 'orem_cf7_geo_gpa_page' );
	  $api_key = get_option( 'orem_cf7_geo_api_key' );
	  if(is_ssl())
	  {
			$securee = 'https';
	  }
	  else
	  {
			$securee = 'http';
	  }
	  $api_script .= $securee.'://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places';
	  /* Below we are making sure the script is only loaded on the page designated in the Google Places API settings page (i.e. Contact, Home, etc.). */
		
		/*  Once you have your key enter it in the Google Places API Key field on the Google Places API settings page. */
		  wp_enqueue_script( 'gpa-google-places-api', $api_script, array(), 'null', true );
		
	}
	
	/**************************
		loading plugin script when anything is entered in autocomplete field in frontend
		***************************************************************************************/
		
	public function WMAMC_cf7_gpa_plugin_script() 
	{
		$gpa_page = get_option( 'orem_cf7_geo_gpa_page' );
		  /* Below we are making sure the script is only loaded on the page designated in the Google Places API settings page (i.e. Contact, Home, etc.)   */
		
?>
				<script>
				  window.onload = function initialize_gpa() {
				  /* Create the autocomplete object and associate it with the UI input control.
				   Restrict the search to geographical location types. */
				   
				   
				       var acInputs = document.getElementsByClassName("wpcf7-autocomplete");

							for (var i = 0; i < acInputs.length; i++) {

								var autocomplete = new google.maps.places.Autocomplete(acInputs[i]);
								autocomplete.inputId = acInputs[i].id;

								google.maps.event.addListener(autocomplete, 'place_changed', function () {
									
								});
							}
	
				  }
				</script>
<?php 
				
	}
	
	
	/************
		shortcode added for address autocomplete field
		****************************************************/
		
	public function WMAMC_cf7_address_autocomplete_shortcode_add()
	{
		wpcf7_add_shortcode(array( 'address_geo_autocomplete', 'address_geo_autocomplete*'), array($this, 'wpcf7_addrauto_form_tag_handler'), true);
		
	// end of function orem_cf7_address_autocomplete_shortcode_add
	}
	
	/**************
		creating autocomplete field
		**************************************/
		
	public function WMAMC_cf7_address_autocomplete_create_address_field()
	{
		wpcf7_add_form_tag( array( 'address_geo_autocomplete', 'address_geo_autocomplete*' ),
		'wpcf7_addrauto_form_tag_handler', array( 'name-attr' => true ) );
		
	// end of function orem_cf7_address_autocomplete_create_address_field
	}
	
	/******************
		handling autocomplete tag
		***********************************************/
	public function wpcf7_addrauto_form_tag_handler($tag)
	{
		/* $tag = new WPCF7_Shortcode($tag); */
		if (empty($tag->name)) 
		{
			return '';
		}
		
		$validation_error = wpcf7_get_validation_error( $tag->name );
		$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-autocomplete' );
		
		/* $class = wpcf7_form_controls_class( $tag->type ); */

		if ( $validation_error ) 
		{
			$class .= ' wpcf7-not-valid';
		}
		
		$atts = array();
		$atts['size']		= $tag->get_size_option( '40' );
		$atts['maxlength']	= $tag->get_maxlength_option();
		$atts['class']		= $tag->get_class_option( $class );
		$atts['id']			= $tag->get_id_option();
		$atts['tabindex']	= $tag->get_option( 'tabindex', 'int', true );

		if ( $tag->has_option( 'readonly' ) ) 
		{
			$atts['readonly'] = 'readonly';
		}

		if ( $tag->is_required() ) 
		{
			$atts['aria-required'] = 'true';
		}
		$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
		
		
		
		if ( $tag->has_option( 'placeholder' ) )
		{
			$place = $tag->get_option( 'placeholder', '[-0-9a-zA-Z_\s]+', true );
			$place = str_replace("_", " ", $place);
			$atts['placeholder'] = $place;
		}			
		$atts['type']	= 'text';
		$atts['name']	= $tag->name;
		$atts = wpcf7_format_atts($atts);
        $this->fields[$tag->name]   = $tag->values;
        $this->names[]  = $tag->name;   

		$html = sprintf(
			'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
			sanitize_html_class( $tag->name ), $atts, $validation_error );
		return $html;
		
		
	// 	end of function wpcf7_addrauto_form_tag_handler
	}
	
	/********************
		validating the autocomplete field
		***************************************************/
		
	public function WMAMC_cf7_address_autocomplete_filter_validation( $result, $tag ) 
	{		
		$tag = new WPCF7_Shortcode($tag);
		$name = $tag->name;

		$value = isset( $_POST[$name] )
			? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
			: '';

		if ( $tag->is_required() && '' == $value) 
		{
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
		}
		
		return $result;
	}	
	
	
	/************************
		tag is generated for address autocomplete
		*********************************************/

	public function WMAMC_cf7_address_autocomplete_add_tag_generator() 
	{
		if ( ! function_exists( 'wpcf7_add_tag_generator' ) )
			return;

		wpcf7_add_tag_generator( 'address_geo_autocomplete', __( 'Address Autocomplete', 'WMAMC_cf7_address_autocomplete_trdom' ),
			'tb-tg-pane-autocomplete', array($this, 'WMAMC_cf7_address_autocomplete_tag_generator_addrauto' ) );
	}
	
	/******************
		address autocomplete tag details and html genrated
		*********************************************************/
		
	public function WMAMC_cf7_address_autocomplete_tag_generator_addrauto($contact_form, $args = '' )
	{
		$args = wp_parse_args( $args, array() );
			$type = 'address_geo_autocomplete';

			$description = __( "Generate a form-tag for a group of autocomplete field.", 'WMAMC_cf7_address_autocomplete_trdom' );
			$desc_link ="";

		?>
		<div class="control-box">
			<fieldset>
				<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

				<table class="form-table">
					<tbody>					
						<tr>
							<th scope="row"><?php echo esc_html( __( 'Field type', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></th>
							<td>
								<fieldset>
								<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></legend>
								<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></label>
								</fieldset>
							</td>
						</tr>					

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></label></th>
							<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id (optional)', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></label></th>
							<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class (optional)', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></label></th>
							<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>							
						</tr>

						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-place' ); ?>"><?php echo esc_html( __( 'Placeholder (optional)', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?></label></th>
							<td><input type="text" name="placeholder" class="oneline option" id="<?php echo esc_attr( $args['content'] . '-place' ); ?>" /></td>							
						</tr>

						
					</tbody>
				</table>
			</fieldset>
		</div>

		<div class="insert-box">
			<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

			<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'WMAMC_cf7_address_autocomplete_trdom' ) ); ?>" />
			</div>

			<br class="clear" />

			<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
		</div>
		<?php
	}
	
	/*************
		autocomplete tag messages
		*************************************/
		
	public function WMAMC_cf7_address_autocomplete_messages($messages) 
	{
		return array_merge($messages, array(
			'invalid_value' => array(
				'description' => __( "The value selected is invalid.", 'WMAMC_cf7_address_autocomplete_trdom' ),
				'default' => __( 'Autocomplete value seems invalid.', 'WMAMC_cf7_address_autocomplete_trdom' )
			),

			'invalid_required' => array(
			    'description' => 'Please fill in the required field.',
			    'default' => 'Please fill in the required field.'
			) ) );
	}
	
	
	
	public function WMAMC_cf7_address_autocomplete_plugin_activate()
	{
		
		$user_permission = current_user_can( 'update_core' );
		if ($user_permission == true)
		{
			add_option('Activated_Plugin', 'WMAMC-cf7-address-autocomplete');
		}
		$version = get_option( 'WMAMC_cf7_address_autocomplete_version' );

		if( version_compare($version, '5.0.1', '<')) 
		{
			// Do some special things when we update to 5.0.1.
		}
		if ($user_permission == true)
		{
			update_option( 'WMAMC_cf7_address_autocomplete_version','1.0.0');
		}
	
	// end of function orem_cf7_address_autocomplete_plugin_activate
	}
	
	public function WMAMC_cf7_address_autocomplete_plugin_deactivate()
	{
	
	// end of function orem_cf7_address_autocomplete_plugin_deactivate	
	}
	
	
	public function WMAMC_cf7_address_autocomplete_instance()
	{
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
		
	// end of function orem_cf7_address_autocomplete_instance
	}
	
	
// end	of class Orem_cf7_address_autocomplete
}


function WMAMC_cf7_address_autocomplete() 
{
	return WMAMC_cf7_address_autocomplete::WMAMC_cf7_address_autocomplete_instance();
}
WMAMC_cf7_address_autocomplete();

?>