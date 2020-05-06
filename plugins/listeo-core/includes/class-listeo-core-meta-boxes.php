<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * WPSight_Meta_Boxes class
 */
class Listeo_Core_Meta_Boxes {
	/**
	 * Constructor
	 */
	public function __construct() {

		// Add custom meta boxes
		add_action( 'cmb2_admin_init', array( $this, 'add_meta_boxes' ) );
		add_filter( 'cmb2_render_listeomenu', array( $this,'cmb2_render_listeomenu_field_callback'), 10, 5 );
		add_filter( 'cmb2_sanitize_listeomenu', array( $this,'cmb2_sanitize_listeomenu_field'), 10, 5 );
		add_filter( 'cmb2_sanitize_listeomenu', array( $this,'cmb2_split_listeomenu_values'), 12, 4 );
		add_filter( 'cmb2_types_esc_listeomenu', array( $this,'cmb2_types_esc_listeomenu_field'), 10, 4 );
		
		add_action( 'cmb2_render_datetime', array( $this,'cmb2_render_callback_for_datetime'), 10, 5 );
		

		add_action( 'listing_category_add_form_fields', array( $this,'listeo_listing_category_add_new_meta_field'), 10, 2 );
		add_action( 'listing_category_edit_form_fields', array( $this,'listeo_listing_category_edit_meta_field'), 10, 2 );
		
		add_action( 'edited_listing_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );  
		add_action( 'created_listing_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );
		
		add_action( 'region_add_form_fields', array( $this,'listeo_listing_category_add_new_meta_field'), 10, 2 );
		add_action( 'region_edit_form_fields', array( $this,'listeo_listing_category_edit_meta_field'), 10, 2 );
		
		add_action( 'edited_region', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );  
		add_action( 'created_region', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );


		add_action( 'event_category_add_form_fields', array( $this,'listeo_listing_category_add_new_meta_field'), 10, 2 );
		add_action( 'event_category_edit_form_fields', array( $this,'listeo_listing_category_edit_meta_field'), 10, 2 );
		
		add_action( 'edited_event_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );  
		add_action( 'created_event_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );
	
		add_action( 'service_category_add_form_fields', array( $this,'listeo_listing_category_add_new_meta_field'), 10, 2 );
		add_action( 'service_category_edit_form_fields', array( $this,'listeo_listing_category_edit_meta_field'), 10, 2 );
		
		add_action( 'edited_service_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );  
		add_action( 'created_service_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );	

		add_action( 'rental_category_add_form_fields', array( $this,'listeo_listing_category_add_new_meta_field'), 10, 2 );
		add_action( 'rental_category_edit_form_fields', array( $this,'listeo_listing_category_edit_meta_field'), 10, 2 );
		
		add_action( 'edited_rental_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );  
		add_action( 'created_rental_category', array( $this,'listeo_save_taxonomy_custom_meta'), 10, 2 );

		add_action( 'cmb2_admin_init', array( $this,'listeo_register_taxonomy_metabox' ) );

	}

	public function add_meta_boxes( ) {
		
		$listing_admin_options = array(
				'id'           => 'listeo_core_listing_admin_metaboxes',
				'title'        => __( 'Listing admin data', 'listeo_core' ),
				'object_types' => array( 'listing' ),
				'show_names'   => true,

		);
		$cmb_listing_admin = new_cmb2_box( $listing_admin_options );

		$cmb_listing_admin->add_field( array(
			'name' => __( 'Expiration date', 'listeo_core' ),
			'desc' => '',
			'id'   => '_listing_expires',
			'type' => 'text_date_timestamp',
			
		) );
		// Listing type meta
		$listing_type_options = array(
				'id'           => 'listing_type',
				'title'        => __( 'Listing type', 'listeo_core' ),
				'object_types' => array( 'listing' ),
				'show_names'   => true,
		);
  		$cmb_listing_type = new_cmb2_box( $listing_type_options );
  		$cmb_listing_type->add_field( array(
			'name' => __( 'Listing Type', 'listeo_core' ),
			'id'   => '_listing_type',
			'type' => 'select',
			'desc' => 'Determines booking options',
			'options'   => array(
				'service' => __( 'Service', 'listeo_core' ),
				'rental' => __( 'Rental', 'listeo_core' ),
    			'event' => __( 'Event', 'listeo_core' ),
			),
		));  
		
		// EOF Listing type meta

		$cmb_keywords_options = array(
				'id'           => 'listing_keywords',
				'title'        => __( 'Listing keywords', 'listeo_core' ),
				'object_types' => array( 'listing' ),
				'show_names'   => true,
				'priority'   => 'default',
		);
  		$cmb_keywords_options = new_cmb2_box( $cmb_keywords_options );
		$cmb_keywords_options->add_field( array(
			'name' => __( 'Keywords', 'listeo_core' ),
			'id'   => 'keywords',
			'type' => 'text',
			'desc' => 'Optional keywords used in search',
			
		));  

		$tabs_box_options = array(
				'id'           => 'listeo_tabbed_metaboxes',
				'title'        => __( 'Listing fields', 'listeo_core' ),
				'object_types' => array( 'listing' ),
				'show_names'   => true,
			);

		// Setup meta box
		$cmb_tabs = new_cmb2_box( $tabs_box_options );

		// setting tabs
		$tabs_setting  = array(
			'config' => $tabs_box_options,
			'layout' => 'vertical', // Default : horizontal
			'tabs'   => array()
		);
		
					$tabs_setting['tabs'] = array(
 
			 // $this->meta_boxes_main_details(),
			  $this->meta_boxes_location(),
			  $this->meta_boxes_gallery(),
			  $this->meta_boxes_contact(),
			  $this->meta_boxes_event(),
			  $this->meta_boxes_service(),
			  $this->meta_boxes_rental(),
			  $this->meta_boxes_prices(),
			  $this->meta_boxes_video(),
			  $this->meta_boxes_cancellation_policy(),
			  $this->meta_boxes_custom(),
			 // $this->meta_boxes_details(),
			 
		);

		// set tabs
		$cmb_tabs->add_field( array(
			'id'   => '_tabs',
			'type' => 'tabs',
			'tabs' => $tabs_setting
		) );
  


		// Pricing 
		$cmb_menu = new_cmb2_box( array(
            'id'            => '_menu_metabox',
            'title'         => __( 'Menu (Pricing)', 'listeo_core' ),
            'object_types' => array( 'listing' ), // post type
            'context'       => 'normal',
            'priority'      => 'core',
            'show_names'    => true,
        ) );
		$cmb_menu->add_field( array(
			'name' => __( 'Pricing Status', 'listeo_core' ),
			'id'   => '_menu_status',
			'type' => 'checkbox',
		));
		$cmb_menu->add_field( array(
			'name' => __( 'Hide pricing table on listing page but show bookable services in booking widget', 'listeo_core' ),
			'id'   => '_hide_pricing_if_bookable',
			'type' => 'checkbox',
		));
        // Repeatable group
	        $menu_group = $cmb_menu->add_field( array(
	            'id'          => '_menu',
	            'type'        => 'group',
	            'options'     => array(
	                'group_title'   => __( 'Menu', 'listeo_core' ) . ' {#}', // {#} gets replaced by row number
	                'add_button'    => __( 'Add another Menu', 'listeo_core' ),
	                'remove_button' => __( 'Remove Menu', 'listeo_core' ),
	                'sortable'      => true, // beta
	            ),
	        ) );


	        $cmb_menu->add_group_field( $menu_group, array(
	            'name'    => __( 'Menu Title', 'listeo_core' ),
	            'id'      => 'menu_title',
	            'type'    => 'text',
	        
	        ) );  
	        $cmb_menu->add_group_field( $menu_group, array(
	            'name'    => __( 'Menu Items', 'listeo_core' ),
	            'id'      => 'menu_elements',
	            'type'    => 'listeomenu',
	            'repeatable' => true,
	        ) );  

        // EOF Pricing
  		
		// EOF Gallery


  		//  Opening hours
		$opening_hours_options = array(
				'id'           => 'listeo_core_opening_metaboxes',
				'title'        => __( 'Opening Hours', 'listeo_core' ),
				'object_types' => array( 'listing' ),
				'show_names'   => true,

		);


		$cmb_opening = new_cmb2_box( $opening_hours_options );

		$cmb_opening->add_field( array(
			'name' => __( 'Opening Hours Status', 'listeo_core' ),
			'id'   => '_opening_hours_status',
			'type' => 'checkbox',
			'desc' => 'Enable to show Opening Hours widget online',
		));
		
		$days = listeo_get_days();
		foreach ($days as $key => $value) {
			
				$cmb_opening->add_field( array(
					'name' => $value . __( ' Opening', 'listeo_core' ),
					'desc' => '',
					'id'   => '_'.$key.'_opening_hour',
					'type' => 'text_time',
					'attributes' => array(
						'data-timepicker' => json_encode( array(
							'timeFormat' => 'HH:mm',
						) ),
					),
					'time_format' => 'H:i',
					'after_field'  => '<button class="button button-secondary button-large clear-time-picker">'.esc_html__('Clear time','listeo_core').'</button>',
					'before_row'      => '<div class="opening_hours_column">',
				
				) );
				$cmb_opening->add_field( array(
					'name' => $value . __( ' Closing', 'listeo_core' ),
					'desc' => '',
					'id'   => '_'.$key.'_closing_hour',
					'type' => 'text_time',
					'attributes' => array(
						'data-timepicker' => json_encode( array(
							'timeFormat' => 'HH:mm',
						) ),
					),
					'time_format' => 'H:i',
					'after_field'  => '<button class="button button-secondary button-large clear-time-picker">'.esc_html__('Clear time','listeo_core').'</button>',
					'after_row'      => '</div>',
				) );
			
				
			
		}
		//  EOF Opening hours

		// Verified 
		$verified_box_options = array(
				'id'           => 'listeo_core_verified_metabox',
				'title'        => __( 'Verified Listing', 'listeo_core' ),
				'context'	   => 'side',
				'priority'     => 'core', 
				'object_types' => array( 'listing' ),
				'show_names'   => false,

		);

		// Setup meta box
		$cmb_verified = new_cmb2_box( $verified_box_options );

		$cmb_verified->add_field( array(
			'name' => __( 'Verified', 'listeo_core' ),
			'id'   => '_verified',
			'type' => 'checkbox',
			'desc' => __( 'Tick the checkbox to mark it as Verified', 'listeo_core' ),
		));
		// EOF Verified


		$featured_box_options = array(
				'id'           => 'listeo_core_featured_metabox',
				'title'        => __( 'Featured Listing', 'listeo_core' ),
				'context'	   => 'side',
				'priority'     => 'core', 
				'object_types' => array( 'listing' ),
				'show_names'   => false,

		);

		// Setup meta box
		$cmb_featured = new_cmb2_box( $featured_box_options );

		$cmb_featured->add_field( array(
			'name' => __( 'Featured', 'listeo_core' ),
			'id'   => '_featured',
			'type' => 'checkbox',
			'desc' => __( 'Tick the checkbox to make it Featured', 'listeo_core' ),
		));
		

		$advanced_box_options = array(
				'id'           => 'listeo_core_advanced_metabox',
				'title'        => __( 'Advanced meta data Listing', 'listeo_core' ),
				'priority'     => 'core', 
				'object_types' => array( 'listing' ),
				'show_names'   => true,

		);

		// Setup meta box
		$cmb_advanced = new_cmb2_box( $advanced_box_options );

		$cmb_advanced->add_field( array(
			'name' => __( 'WooCommerce Product ID', 'listeo_core' ),
			'id'   => 'product_id',
			'type' => 'text',
			'desc' => __( 'WooCommerce Product ID. Don\'t change it unless you know what you are doing:)', 'listeo_core' ),
		));



		$booking_box_options = array(
				'id'           => 'listeo_core_booking_metabox',
				'title'        => __( 'Booking options', 'listeo_core' ),
				'priority'     => 'core', 
				'object_types' => array( 'listing' ),
				'show_names'   => true,

		);

		// Setup meta box
		$cmb_booking = new_cmb2_box( $booking_box_options );

		$cmb_booking->add_field( array(
			'name' => __( 'Booking Status', 'listeo_core' ),
			'id'   => '_booking_status',
			'type' => 'checkbox',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Slots Status', 'listeo_core' ),
			'id'   => '_slots_status',
			'type' => 'checkbox',
		));

		$cmb_booking->add_field( array(
			'name' => __( 'Slots', 'listeo_core' ),
			'id'   => '_slots',
			'type' => 'slots',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Event Available Tickets', 'listeo_core' ),
			'id'   => '_event_tickets',
			'type' => 'text',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Regular Price', 'listeo_core' ),
			'id'   => '_normal_price',
			'type' => 'text',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Weekend Price', 'listeo_core' ),
			'id'   => '_weekday_price',
			'type' => 'text',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Reservation Fee', 'listeo_core' ),
			'id'   => '_reservation_price',
			'type' => 'text',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Reservation expires after', 'listeo_core' ),
			'id'   => '_expired_after',
			'type' => 'text',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Enable Price per Guest', 'listeo_core' ),
			'id'   => '_count_per_guest',
			'type' => 'checkbox',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Enable Instant Booking', 'listeo_core' ),
			'id'   => '_instant_booking',
			'type' => 'checkbox',
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Maximum number of guests', 'listeo_core' ),
			'id'   => '_max_guests',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Minimum stay (in days)', 'listeo_core' ),
			'id'   => '_min_days',
			'type' => 'text',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
		));
		$cmb_booking->add_field( array(
			'name' => __( 'Availability Calendar', 'listeo_core' ),
			'id'   => '_availability',
			'type' => 'listeo_calendar',
		));
	}

	public static function meta_boxes_location() {
		
		$fields = array(
			'id'     => 'locations_tab',
			'title'  => __( 'Location', 'listeo_core' ),
			'fields' => array(
				array(
					'name' => __( 'Address', 'listeo_core' ),
					'id'   => '_friendly_address',
					'type' => 'text',
					'desc' => 'Human readable address'
				),			
				array(
					'name' => __( 'Google Maps Address', 'listeo_core' ),
					'id'   => '_address',
					'type' => 'text',
					'desc' => 'Used for geolocation and links'
				),				
				array(
					'name' => __( 'Latitude', 'listeo_core' ),
					'id'   => '_geolocation_lat',
					'type' => 'text',
				),				
				array(
					'name' => __( 'Longitude', 'listeo_core' ),
					'id'   => '_geolocation_long',
					'type' => 'text',
				),
			)
		);

		// Set meta box
		return apply_filters( 'listeo_location_fields', $fields );
	}

	public static function meta_boxes_event() {
		
		$fields = array(
			'id'     => 'event_tab',
			'title'  => __( 'Event fields', 'listeo_core' ),
			'fields' => array(
				array(
					'name' 	=> __( 'Event date:', 'listeo_core' ),
					'id'   	=> '_event_date',
					'type' 	=> 'datetime',
					'invert' => true
				),	
				array(
					'name' 	=> __( 'Event end:', 'listeo_core' ),
					'id'   	=> '_event_date_end',
					'type' 	=> 'datetime',
					'invert' => true
				),	
							
				array(
					'name' 	=> __( 'Size', 'listeo_core' ),
					'id'   	=> '_size',
					'type' 	=> 'text',
					'invert' => true
				),				
				
			)
		);

		// Set meta box
		return apply_filters( 'listeo_event_fields', $fields );
	}
	public static function meta_boxes_prices() {
		
		$fields = array(
			'id'     => 'prices_tab',
			'title'  => __( 'Prices fields', 'listeo_core' ),
			'fields' => array(
				array(
					'name' 	=> __( 'Minimum Price Range:', 'listeo_core' ),
					'id'   	=> '_price_min',
					'type' 	=> 'text',					
				),	
				array(
					'name' 	=> __( 'Maximum Price Range:', 'listeo_core' ),
					'id'   	=> '_price_max',
					'type' 	=> 'text',
				),					
				
			)
		);

		// Set meta box
		return apply_filters( 'listeo_prices_fields', $fields );
	}

	public static function meta_boxes_gallery() {
		
		$fields = array(
			'id'     => 'gallery_tab',
			'title'  => __( 'Gallery', 'listeo_core' ),
			'fields' => array(
				array(
					'name' => __( 'Gallery display layout', 'listeo_core' ),
					'desc' => '',
					'id'   => '_gallery_style',
					'type' => 'select',
					'default' => get_option('listeo_gallery_type','top'),
					'options'   => array(
						'top' => __( 'Gallery on top', 'listeo_core' ),
		    			'content' => __( 'Gallery in content', 'listeo_core' ),
		    			
					)
				),
				array(
					'name' => __( 'Listing gallery', 'listeo_core' ),
					'desc' => '',
					'id'   => '_gallery',
					'type' => 'file_list',
					// 'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
				    'query_args' => array( 'type' => 'image' ), // Only images attachment
					// Optional, override default text strings
					'text' => array(
						'add_upload_files_text' => 'Add or Upload Images', // default: ""
					),
				)
			)
		);

		// Set meta box
		return apply_filters( 'listeo_gallery_fields', $fields );
	}

	public static function meta_boxes_contact() {
	
		$fields = array(
			'id'     => 'contact_tab',
			'title'  => __( 'Contact details', 'listeo_core' ),
			'fields' => array(
				array(
					'name' => __( 'Phone number', 'listeo_core' ),
					'id'   => '_phone',
					'type' => 'text',
				),			
				array(
					'name' => __( 'E-mail', 'listeo_core' ),
					'id'   => '_email',
					'type' => 'text',
				),	array(
					'name' => __( 'Contact Owner Widget  (uses email above) - Add Listeo Contact Widget to Sidebar', 'listeo_core' ),
					'id'   => '_email_contact_widget',
					'type' => 'checkbox',
				),				
				array(
					'name' => __( 'Website', 'listeo_core' ),
					'id'   => '_website',
					'type' => 'text',
				),				
				array(
					'name' => __( 'Facebook', 'listeo_core' ),
					'id'   => '_facebook',
					'type' => 'text',
				),
				array(
					'name' => __( 'Twitter', 'listeo_core' ),
					'id'   => '_twitter',
					'type' => 'text',
				),	
				array(
					'name' => __( 'YouTube', 'listeo_core' ),
					'id'   => '_youtube',
					'type' => 'text',
				),
				array(
					'name' => __( 'Instagram', 'listeo_core' ),
					'id'   => '_instagram',
					'type' => 'text',
				),array(
					'name' => __( 'Skype', 'listeo_core' ),
					'id'   => '_skype',
					'type' => 'text',
				),
				array(
					'name' => __( 'WhatsApp', 'listeo_core' ),
					'id'   => '_whatsapp',
					'type' => 'text',
					
				),
			)
		);

		// Set meta box
		return apply_filters( 'listeo_contact_fields', $fields );
	}


	public static function meta_boxes_service() {

		$fields = array(
			'id'     => 'service_tab',
			'title'  => __( 'Service fields', 'listeo_core' ),
			'fields' => array(
				
			)
		);

		// Set meta box
		return apply_filters( 'listeo_service_fields', $fields );
	}

	public static function meta_boxes_rental() {
		$fields = array(
			'id'     => 'rental_tab',
			'title'  => __( 'Rental fields', 'listeo_core' ),
			'fields' => array(
				array(
					'name' 	=> __( 'Area', 'listeo_core' ),
					'id'   	=> '_area',
					'type' 	=> 'text',
					'invert' => true
				),					
				array(
					'name' 	=> __( 'Rooms', 'listeo_core' ),
					'id'   	=> '_rooms',
					'type' 	=> 'text',
					'invert' => false
				),			
			)
		);


		// Set meta box
		return apply_filters( 'listeo_rental_fields', $fields );
	}


	public static function meta_boxes_video() {
		
		$fields = array(
			'id'     => 'video_tab',
			'title'  => __( 'Video', 'listeo_core' ),
			'fields' => array(
				'video' => array(
					'name' => __( 'Video', 'listeo_core' ),
					'id'   => '_video',
					'type' => 'text',
					'desc'      => __( 'URL to oEmbed supported service','listeo_core' ),
				),
			
			)
		);
		$fields = apply_filters( 'listeo_video_fields', $fields );
		
		// Set meta box
		return $fields;
	}

	public static function meta_boxes_cancellation_policy() {
		
		$fields = array(
			'id'     => 'cancellation_policy_tab',
			'title'  => __( 'Cancellation Policy', 'listeo_core' ),
			'fields' => array(
				array(
					'name' => __( 'Cancellation Policy', 'listeo_core' ),
					'id'   => '_cancellation_policy',
					'type' => 'textarea',
					'desc'      => __( 'Add Cancellation Policy Content','listeo_core' ),
				),
			
			)
		);
		$fields = apply_filters( 'listeo_cancellation_policy_fields', $fields );
		
		// Set meta box
		return $fields;
	}

	public static function meta_boxes_custom() {
		
		$fields = array(
			'id'     => 'custom_tab',
			'title'  => __( 'Custom fields', 'listeo_core' ),
			'fields' => array(
				'video' => array(
					'name' => __( 'Example field', 'listeo_core' ),
					'id'   => '_example',
					'type' => 'text',
					'desc'      => __( 'Example field description','listeo_core' ),
				),
			
			)
		);
		$fields = apply_filters( 'listeo_custom_fields', $fields );
		
		// Set meta box
		return $fields;
	}


	
	/**
	 * Render ListeoMenu Field
	 */
	function cmb2_render_listeomenu_field_callback( $field, $value, $object_id, $object_type, $field_type ) {

		// make sure we specify each part of the value we need.
		$value = wp_parse_args( $value, array(
			'name' => '',
			'description' => '',
			'price'      => '',
			'bookable'      => '',
		) );

		?>
		<div class="alignleft"><p><label for="<?php echo $field_type->_id( '_name' ); ?>'"><?php echo esc_html( $field_type->_text( 'listeomenu_name_text', 'Name' ) ); ?></label></p>
			<?php echo $field_type->input( array(
				'class' => '',
				'name'  => $field_type->_name( '[name]' ),
				'id'    => $field_type->_id( '_name' ),
				'value' => $value['name'],
				'desc'  => '',
			) ); ?>
		</div>

		
		<div class="alignleft"><p><label for="<?php echo $field_type->_id( '_price' ); ?>'"><?php echo esc_html( $field_type->_text( 'listeomenu_price_text', 'Price' ) ); ?></label></p>
			<?php echo $field_type->input( array(
				'class' => '',
				'name'  => $field_type->_name( '[price]' ),
				'id'    => $field_type->_id( '_price' ),
				'value' => $value['price'],
				'type'  => 'text',
				'desc'  => '',
			) ); ?>
		</div>
		<div class="alignleft"><p><label for="<?php echo $field_type->_id( '_bookable' ); ?>'"><?php echo esc_html( $field_type->_text( 'listeomenu_bookable_text', 'Bookable' ) ); ?></label></p>
			<?php echo $field_type->input( array(
				'class' => '',
				'name'  => $field_type->_name( '[bookable]' ),
				'id'    => $field_type->_id( '_bookable' ),
				'value' => 'on',
				'type'  => 'checkbox',
				'checked'  => ($value['bookable'] == 'on') ? 'checked' : false,

				'desc'  => '',
			) ); ?>
		</div>
		<br class="clear">
		<div><p><label for="<?php echo $field_type->_id( '_description' ); ?>'"><?php echo esc_html( $field_type->_text( 'listeomenu_description_text', 'Description' ) ); ?></label></p>
			<?php echo $field_type->textarea( array(
				'name'  => $field_type->_name( '[description]' ),
				'id'    => $field_type->_id( '_description' ),
				'value' => $value['description'],
				'desc'  => '',
			) ); ?>
		</div>
		<?php
		echo $field_type->_desc( true );

	}


	/**
	 * Optionally save the Address values into separate fields
	 */
	function cmb2_split_listeomenu_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}

		$_keys = array(  'name', 'description', 'price','bookable' );

		foreach ( $_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'listing_menu_items_'. $key, $value[ $key ] );
			}
		}

		// Tell CMB2 we already did the update
		return true;
	}
	

	/**
	 * The following snippets are required for allowing the address field
	 * to work as a repeatable field, or in a repeatable group
	 */

	function cmb2_sanitize_listeomenu_field( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {

		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {

			if ( '' == $val['name'] ) {
	            unset( $meta_value[$key] );
	        } else {
				// if($key == 'bookable'){
				// 	$meta_value['bookable'] = 'on';
				// } else {
					if(isset($val['booking'])){
						$val['booking'] = 'on';
					}
					//$meta_value[ $key ] = array_map( 'sanitize_text_field', $val );
					$meta_value[ $key ] = $val;
				//}
			}
			
		}

		return $meta_value;
	}

	function cmb2_types_esc_listeomenu_field( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_map( 'esc_attr', $val );
		}

		return $meta_value;
	}


	function listeo_register_taxonomy_metabox() {
		$prefix = 'listeo_';
	/**
	 * Metabox to add fields to categories and tags
	 */
	$cmb_term = new_cmb2_box( array(
		'id'               => $prefix . 'edit',
		'title'            => esc_html__( 'Listing Taxonomy Meta', 'listeo_core' ), // Doesn't output for term boxes
		'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
		'taxonomies'       => array( 'listing_category' ), // Tells CMB2 which taxonomies should have these fields
		// 'new_term_section' => true, // Will display in the "Add New Category" section
	) );


	$cmb_term->add_field( array(
		'name'           => 'Assign Features for this Category',
		'desc'           => 'Features can be created in Listings -> Features',
		'id'             =>  $prefix . 'taxonomy_multicheck',
		'taxonomy'       => 'listing_feature', //Enter Taxonomy Slug
		'type'           => 'taxonomy_multicheck',
		// Optional :
		'text'           => array(
			'no_terms_text' => 'Sorry, no terms could be found.' // Change default text. Default: "No terms"
		),
		'remove_default' => 'true' // Removes the default metabox provided by WP core. Pending release as of Aug-10-1
	) );

	
		$cmb_term->add_field( array(
		'name'           => 'Category description',
        'id'             =>  $prefix . 'taxonomy_content_description',
		'type'           => 'textarea',
	) );
	}
	/*
	 * Custom Icon field for Job Categories taxonomy 
	 **/

	// Add term page
	function listeo_listing_category_add_new_meta_field() {
		
		?>
		<div class="form-field">
	
			<label for="icon"><?php esc_html_e( 'Category Icon', 'listeo_core' ); ?></label>
				<select class="listeo-icon-select" name="icon" id="icon">
					
				<?php 

				 	// $faicons = listeo_fa_icons_list();
				 	
				  //  	foreach ($faicons as $key => $value) {

				  //  		echo '<option value="fa fa-'.$key.'" ';
				  //  		echo '>'.$value.'</option>';
				  //  	}
			   		$imicons = vc_iconpicker_type_iconsmind(array());
			   		
				   	foreach ($imicons as $key => $icon_array ) {
				   		$key = key($icon_array);
				   		$value = $icon_array[$key];
				   		echo '<option value="'.$key.'" ';
				   		echo '>'.$value.'</option>';
				   	}
				   ?>

				</select>
			<p class="description"><?php esc_html_e( 'Icon will be displayed in categories grid view','listeo_core' ); ?></p>
		</div>
		<?php //wp_enqueue_media(); ?>
		<div class="form-field">
			<label for="_cover"><?php esc_html_e( 'Category Cover', 'listeo_core' ); ?></label>
			<input style="width:100px" type="text" name="_cover" id="_cover" value="">
				<input type='button' class="listeo-custom-image-upload button-primary" value="<?php _e( 'Upload Image', 'listeo_core' ); ?>" id="uploadimage"/><br />
			<p class="description"><?php esc_html_e( 'Similar to the single jobs you can add image to the category header. It should be 1920px wide','listeo_core' ); ?></p>
		</div>

		
			
	<?php
	}
	

	// Edit term page
	function listeo_listing_category_edit_meta_field($term) {
	 
		// put the term ID into a variable
		$t_id = $term->term_id;
	 
		// retrieve the existing value(s) for this meta field. This returns an array
		
		?>		
		<tr class="form-field">
			<th scope="row" valign="top">

				<label for="icon"><?php esc_html_e( 'Category Icon', 'listeo_core' ); ?></label>

			<td>
				<select class="listeo-icon-select" name="icon" id="icon">
					<option value="empty">Empty</option>
				<?php 
					$icon = get_term_meta( $t_id, 'icon', true );
 
				 	$faicons = listeo_fa_icons_list();
				 	
				   	foreach ($faicons as $key => $value) {
				   		if($key){
					   		echo '<option value="fa fa-'.$key.'" ';
					   		if ($icon == 'fa fa-'.$key) { echo ' selected="selected"';}
					   		echo '>'.$value.'</option>';	
				   		}
				   		
				   	}
			   		$imicons = vc_iconpicker_type_iconsmind(array());
			   		
				   	foreach ($imicons as $key => $icon_array ) {
				   		$key = key($icon_array);
				   		$value = $icon_array[$key];
				   		echo '<option value="'.$key.'" ';
				   			if(isset($icon) && $icon == $key) { echo ' selected="selected"';}
				   		echo '>'.$value.'</option>';
				   	}
				   ?>

				</select>
				<p class="description"><?php esc_html_e( 'Icon will be displayed in categories grid view','listeo_core' ); ?></p>
			</td>
		</tr>
		<?php wp_enqueue_media(); ?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="_cover"><?php esc_html_e( 'Category Cover', 'listeo_core' ); ?></label></th>
			<td>
				<?php 
				$cover = get_term_meta( $t_id, '_cover', true );
				
				if($cover) :
					$cover_image = wp_get_attachment_image_src($cover,'medium');
					
					if ($cover_image)  {
						echo '<img src="'.$cover_image[0].'" style="width:300px;height: auto;"/><br>';
					} 
				endif;
				?>
				<input style="width:100px" type="text" name="_cover" id="_cover" value="<?php echo $cover; ?>">
				<input type='button' class="listeo-custom-image-upload button-primary" value="<?php _e( 'Upload Image', 'listeo_core' ); ?>" id="uploadimage"/><br />
			</td>
		</tr>
	<?php
	}


	// Save extra taxonomy fields callback function.
	function listeo_save_taxonomy_custom_meta( $term_id, $tt_id ) {


		if( isset( $_POST['icon'] ) && '' !== $_POST['icon'] ){
	        $icon = $_POST['icon'];

	        update_term_meta( $term_id, 'icon', $icon );
	    }

	    if( isset( $_POST['_cover'] ) && '' !== $_POST['_cover'] ){
	        $cover = sanitize_title( $_POST['_cover'] );
	        update_term_meta( $term_id, '_cover', $cover );
	    }
		
	}  

	function cmb2_render_callback_for_datetime( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
		echo $field_type_object->input( array( 'type' => 'text', 'class' => 'input-datetime' ) );
	}



}