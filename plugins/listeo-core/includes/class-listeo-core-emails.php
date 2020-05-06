<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
/**
 * listeo_core_listing class
 */
class Listeo_Core_Emails {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0
	 */
	private static $_instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.0
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_action( 'listeo_core_listing_submitted', array($this, 'new_listing_email'));
		add_action( 'listeo_core_listing_submitted', array($this, 'new_listing_email_admin'));
		add_action( 'listeo_core_expired_listing', array($this, 'expired_listing_email'));
		add_action( 'listeo_core_expiring_soon_listing', array($this, 'expiring_soon_listing_email'));

		add_action( 'pending_to_publish', array( $this, 'published_listing_email' ) );
		add_action( 'pending_payment_to_publish', array( $this, 'published_listing_email' ) );
		add_action( 'preview_to_publish', array( $this, 'published_listing_email' ) );
		// add_action( 'draft_to_publish', array( $this, 'published_listing_email' ) );
		// add_action( 'auto-draft_to_publish', array( $this, 'published_listing_email' ) );
		// add_action( 'expired_to_publish', array( $this, 'published_listing_email' ) );

		
		//booking emails
		add_action( 'listeo_mail_to_user_waiting_approval', array($this, 'mail_to_user_waiting_approval'));
		add_action( 'listeo_mail_to_user_instant_approval', array($this, 'mail_to_user_instant_approval'));
		add_action( 'listeo_mail_to_owner_new_reservation', array($this, 'mail_to_owner_new_reservation'));
		add_action( 'listeo_mail_to_user_canceled', array($this, 'mail_to_user_canceled'));
		add_action( 'listeo_mail_to_owner_new_intant_reservation', array($this, 'mail_to_owner_new_instant_reservation'));
		add_action( 'listeo_mail_to_user_pay', array($this, 'mail_to_user_pay'));
		add_action( 'listeo_mail_to_owner_paid', array($this, 'mail_to_owner_paid'));
		add_action( 'listeo_mail_to_user_free_confirmed', array($this, 'mail_to_user_free_confirmed'));
		add_action( 'listeo_mail_to_user_new_conversation', array($this, 'new_conversation_mail'));
		add_action( 'listeo_mail_to_user_new_message', array($this, 'new_message_mail'));
		
		add_action( 'listeo_welcome_mail', array($this, 'welcome_mail'));


	}



	function new_listing_email($post_id ){
		$post = get_post($post_id);
		if ( $post->post_type !== 'listing' ) {
			return;
		}

		if(!get_option('listeo_listing_new_email')){
			return;
		}
		
		$author   	= 	get_userdata( $post->post_author ); 
		$email 		=  $author->data->user_email;

		$args = array(
			'user_name' 	=> $author->display_name,
			'user_mail' 	=> $email,
			'listing_date' => $post->post_date,
			'listing_name' => $post->post_title,
			'listing_url'  => get_permalink( $post->ID ),
			);

		$subject 	 = get_option('listeo_listing_new_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_listing_new_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );

		self::send( $email, $subject, $body );
	}

	function new_listing_email_admin($post_id ){
		$post = get_post($post_id);
	
		if ( $post->post_type !== 'listing' ) {
			return;
		}
		if ( $post->post_status !== 'pending' ) {
			return;
		}
		
		if(!get_option('listeo_new_listing_admin_notification')){
			return;
		}
		

		$email = get_option('admin_email');
		$args = array(
			
			'user_mail' 	=> get_option('admin_email'),
			'listing_name' => $post->post_title,
			);

		$subject 	 = esc_html__('There is new listing waiting for approval','listeo_core');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = esc_html__('There is listing waiting for your approval "{listing_name}"','listeo_core');
		$body 	 = $this->replace_shortcode( $args, $body );

		self::send( $email, $subject, $body );
	}

	function published_listing_email($post ){
		if ( $post->post_type != 'listing' ) {
			return;
		}

		if(!get_option('listeo_listing_published_email')){
			return;
		}
		if(get_post_meta($post->ID, 'listeo_published_mail_send', true) == "sent"){
			return;
		}
		$author   	= 	get_userdata( $post->post_author ); 
		$email 		=  $author->data->user_email;

		$args = array(
			'user_name' 	=> $author->display_name,
			'user_mail' 	=> $email,
			'listing_date' => $post->post_date,
			'listing_name' => $post->post_title,
			'listing_url'  => get_permalink( $post->ID ),
			);

		$subject 	 = get_option('listeo_listing_published_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_listing_published_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		update_post_meta( $post->ID, 'listeo_published_mail_send', 'sent' );
		self::send( $email, $subject, $body );
	}	

	function expired_listing_email($post_id ){
		$post = get_post($post_id);
		if ( $post->post_type !== 'listing' ) {
			return;
		}

		if(!get_option('listeo_listing_expired_email')){
			return;
		}
		
		$author   	= 	get_userdata( $post->post_author ); 
		$email 		=  $author->data->user_email;

		$args = array(
			'user_name' 	=> $author->display_name,
			'user_mail' 	=> $email,
			'listing_date' => $post->post_date,
			'listing_name' => $post->post_title,
			'listing_url'  => get_permalink( $post->ID ),
			);

		$subject 	 = get_option('listeo_listing_expired_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_listing_expired_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );

		self::send( $email, $subject, $body );
	}

	function expiring_soon_listing_email($post_id ){
		$post = get_post($post_id);
		if ( $post->post_type !== 'listing' ) {
			return;
		}
		$already_sent = get_post_meta( $post_id, 'notification_email_sent', true );
		if($already_sent) {
			return;
		}

		if(!get_option('listeo_listing_expiring_soon_email')){
			return;
		}
		
		$author   	= 	get_userdata( $post->post_author ); 
		$email 		=  $author->data->user_email;

		$args = array(
			'user_name' 	=> $author->display_name,
			'user_mail' 	=> $email,
			'listing_date' => $post->post_date,
			'listing_name' => $post->post_title,
			'listing_url'  => get_permalink( $post->ID ),
			);

		$subject 	 = get_option('listeo_listing_expiring_soon_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_listing_expiring_soon_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		add_post_meta($post_id, 'notification_email_sent', true );
		self::send( $email, $subject, $body );

	}

	// booking emails
		// [email] => admin@listeo.com
  //   [booking] => Array
  //       (
  //           [ID] => 4
  //           [bookings_author] => 1
  //           [owner_id] => 1
  //           [listing_id] => 604
  //           [date_start] => 2019-01-16 09:00:00
  //           [date_end] => 2019-01-16 11:00:00
  //           [comment] => {"first_name":"\u0141ukasz asdas","last_name":"Girek asdas d","email":"admin@listeo.com","phone":"+48 0500389009","childrens":"0","adults":"2"}
  //           [order_id] => 
  //           [status] => 
  //           [type] => reservation
  //           [created] => 2019-01-02
  //           [expiring] => 
  //           [price] => 75
  //       )
		
	function mail_to_user_waiting_approval($args){
		if(!get_option('listeo_booking_user_waiting_approval_email')){
			return;
		}
		$email 		=  $args['email'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$booking = $args['booking'];
		
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['bookings_author']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_booking_user_waiting_approval_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 =  get_option('listeo_booking_user_waiting_approval_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}	


	function mail_to_user_instant_approval($args){
		if(!get_option('listeo_instant_booking_user_waiting_approval_email')){
			return;
		}
		$email 		=  $args['email'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$booking = $args['booking'];
		
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['bookings_author']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_instant_booking_user_waiting_approval_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 =  get_option('listeo_instant_booking_user_waiting_approval_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}


	function mail_to_owner_new_instant_reservation($args){

		if(!get_option('listeo_booking_instant_owner_new_booking_email')){
			return;
		}
		$email 		=  $args['email'];
		$booking = $args['booking'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['owner_id']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_booking_instant_owner_new_booking_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_booking_instant_owner_new_booking_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function mail_to_owner_new_reservation($args){
		if(!get_option('listeo_booking_owner_new_booking_email')){
			return;
		}
		$email 		=  $args['email'];
		$booking = $args['booking'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['owner_id']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_booking_owner_new_booking_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_booking_owner_new_booking_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function mail_to_user_canceled($args){
		if(!get_option('listeo_booking_user_cancallation_email')){
			return;
		}
		$email 		=  $args['email'];
		$booking = $args['booking'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['owner_id']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_booking_user_cancellation_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_booking_user_cancellation_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function mail_to_user_free_confirmed($args){
		if(!get_option('listeo_free_booking_confirmation')){
			return;
		}

		$email 		=  $args['email'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$booking = $args['booking'];
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['bookings_author']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_free_booking_confirmation_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_free_booking_confirmation_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function mail_to_user_pay($args){
		if(!get_option('listeo_pay_booking_confirmation_user')){
			return;
		}
		$email 		=  $args['email'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$booking = $args['booking'];
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['bookings_author']),
			'user_mail' 	=> $email,
			'booking_date' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'aduts' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			'payment_url'  => $args['payment_url'],
			'expiration'  => $args['expiration']
			);

		$subject 	 = get_option('listeo_pay_booking_confirmation_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_pay_booking_confirmation_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function mail_to_owner_paid($args){
		if(!get_option('listeo_pay_booking_confirmation')){
			return;
		}
		$email 		=  $args['email'];
		$booking_data = $this->get_booking_data_emails($args['booking']);
		$booking = $args['booking'];
		$args = array(
			'user_name' 	=> get_the_author_meta('display_name',$booking['owner_id']),
			'user_mail' 	=> $email,
			'booking_created' => $booking['created'],
			'listing_name' => get_the_title($booking['listing_id']),
			'listing_url'  => get_permalink($booking['listing_id']),
			'dates' => (isset($booking_data['dates'])) ? $booking_data['dates'] : '',
			'details' => (isset($booking_data['details'])) ? $booking_data['details'] : '',
			'service' => (isset($booking_data['service'])) ? $booking_data['service'] : '',
			'tickets' => (isset($booking_data['tickets'])) ? $booking_data['tickets'] : '',
			'adults' =>(isset($booking_data['adults'])) ? $booking_data['adults'] : '',
			'children' => (isset($booking_data['children'])) ? $booking_data['children'] : '',
			);

		$subject 	 = get_option('listeo_paid_booking_confirmation_email_subject');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_paid_booking_confirmation_email_content');
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function welcome_mail($args){
		$email 		=  $args['email'];

		$args = array(
			'email'         => $email,
	        'login'         => $args['login'],
	        'password'      => $args['password'],
	        'first_name' 	=> $args['first_name'],
	        'last_name' 	=> $args['last_name'],
	        'user_name' 	=> $args['display_name'],
			'user_mail' 	=> $email,
			'login_url' 	=> $args['login_url'],
			
			);
		$subject 	 = get_option('listeo_listing_welcome_email_subject','Welcome to {site_name}!');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_listing_welcome_email_content','Welcome to {site_name}! You can log in {login_url}, your username: "{login}", and password: "{password}".');
		
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}


	function new_conversation_mail($args){
		$conversation_id = $args['conversation_id']; 
		//{user_mail},{user_name},{listing_name},{listing_url},{site_name},{site_url}
		global $wpdb;

        $conversation_data  = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        WHERE  id = '$conversation_id'

        ");

        $read_user_1 = $conversation_data[0]->read_user_1;
        if($read_user_1==0){
        	$user_who_send = $conversation_data[0]->user_2;
        	$user_to_notify = $conversation_data[0]->user_1;
        }
        $read_user_2 = $conversation_data[0]->read_user_2;
        if($read_user_2==0){
        	$user_who_send = $conversation_data[0]->user_1;
        	$user_to_notify = $conversation_data[0]->user_2;
        }


		$user_to_notify_data   	= 	get_userdata( $user_to_notify ); 
		$email 		=  $user_to_notify_data->user_email;

		$user_who_send_data = get_userdata( $user_who_send ); 
		$sender = $user_who_send_data->first_name;
		if(empty($sender)){
			$sender = $user_who_send_data->nickname;
		}
        // ["id"]=> string(2) "36" ["timestamp"]=> string(10) "1573163130" ["user_1"]=> string(1) "1" ["user_2"]=> string(2) "14" ["referral"]=> string(14) "author_archive" ["read_user_1"]=> string(1) "1" ["read_user_2"]=> string(1) "0" ["last_update"]=> string(10) "1573172773"

		$args = array(
			'user_mail'     => $email,
	        'user_name' 	=> $user_to_notify_data->first_name,
			'conversation_url' => get_permalink('listeo_messages_page').'?action=view&conv_id='.$conversation_id,
			'sender'		=> $sender,
			);
		$subject 	 = get_option('listeo_new_conversation_notification_email_subject','You got new conversation!');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_new_conversation_notification_email_content',"Hi {user_name},<br>
                    There's a new conversation from {sender} waiting for your on {site_name}.<br> Check it  <a href='{conversation_url}'>here</a>
                    <br>Thank you");
		
		$body 	 = $this->replace_shortcode( $args, $body );
		self::send( $email, $subject, $body );
	}

	function new_message_mail($id){
		$conversation_id = $id; 
		//{user_mail},{user_name},{listing_name},{listing_url},{site_name},{site_url}
		global $wpdb;

        $conversation_data  = $wpdb -> get_results( "
        SELECT * FROM `" . $wpdb->prefix . "listeo_core_conversations` 
        WHERE  id = '$conversation_id'

        ");

        $read_user_1 = $conversation_data[0]->read_user_1;
        if($read_user_1==0){
        	$user_who_send = $conversation_data[0]->user_2;
        	$user_to_notify = $conversation_data[0]->user_1;
        }
        $read_user_2 = $conversation_data[0]->read_user_2;
        if($read_user_2==0){
        	$user_who_send = $conversation_data[0]->user_1;
        	$user_to_notify = $conversation_data[0]->user_2;
        }


		$user_to_notify_data   	= 	get_userdata( $user_to_notify ); 
		$email 		=  $user_to_notify_data->user_email;

		$user_who_send_data = get_userdata( $user_who_send ); 
		$sender = $user_who_send_data->first_name;
		if(empty($sender)){
			$sender = $user_who_send_data->nickname;
		}
        // ["id"]=> string(2) "36" ["timestamp"]=> string(10) "1573163130" ["user_1"]=> string(1) "1" ["user_2"]=> string(2) "14" ["referral"]=> string(14) "author_archive" ["read_user_1"]=> string(1) "1" ["read_user_2"]=> string(1) "0" ["last_update"]=> string(10) "1573172773"

		$args = array(
			'user_mail'     => $email,
	        'user_name' 	=> $user_to_notify_data->first_name,
			'sender'		=> $sender,
			'conversation_url' => get_permalink('listeo_messages_page').'?action=view&conv_id='.$conversation_id,
			);
		$subject 	 = get_option('listeo_new_message_notification_email_subject','You got new conversation!');
		$subject 	 = $this->replace_shortcode( $args, $subject );

		$body 	 = get_option('listeo_new_message_notification_email_content',"Hi {user_name},<br>
                    There's a new message from {sender} waiting for your on {site_name}.<br> Check it  <a href='{conversation_url}'>here</a>
                    <br>Thank you");
		
		$body 	 = $this->replace_shortcode( $args, $body );
		global $wpdb;
		$result  = $wpdb->update( 
            $wpdb->prefix . 'listeo_core_conversations', 
            array( 'notification'  => 'sent' ), 
            array( 'id' => $conversation_id ) 
        );

		self::send( $email, $subject, $body );

		//mark this converstaito as sent
		
	}
	

	function get_booking_data_emails($args){

		$listing_type = get_post_meta($args['listing_id'],'_listing_type',true);
		$booking_data = array();
		
		switch ($listing_type) {
			case 'rental':
				$booking_data['dates'] = date(get_option( 'date_format' ), strtotime($args['date_start'])) .' - '. date(get_option( 'date_format' ), strtotime($args['date_end'])); 
				break;
			case 'service':
				$booking_data['dates'] = date(get_option( 'date_format' ), strtotime($args['date_start'])) .__(' at ','listeo_core'). date(get_option( 'time_format' ), strtotime($args['date_start']));
				break;
			case 'event':
				$booking_data['dates'] = date(get_option( 'date_format' ), strtotime($args['date_start'])).' '.esc_html__(' at ','listeo_core').' '.date(get_option( 'time_format' ), strtotime($args['date_start']));
				break;
			
			default:
				# code...
				break;
		}
		
		$booking_details = '';
		$details = json_decode($args['comment']);
		if (isset($details->childrens) && $details->childrens > 0) {
			$booking_data['children'] = sprintf( _n( '%d Child', '%s Children', $details->childrens, 'listeo_core' ), $details->childrens );
			$booking_details .= $booking_data['children'];
		}
		if (isset($details->adults) && $details->adults > 0) {
			$booking_data['adults'] = sprintf( _n( '%d Guest', '%s Guests', $details->adults, 'listeo_core' ), $details->adults );
			$booking_details .= $booking_data['adults'];
		}
		if (isset($details->tickets) && $details->tickets > 0) {
			$booking_data['tickets'] = sprintf( _n( '%d Ticket', '%s Tickets', $details->tickets, 'listeo_core' ), $details->tickets );
			$booking_details .= $booking_data['tickets'];
		}
		if (isset($details->service)) {
			$booking_data['service'] = listeo_get_extra_services_html($details->service);
		}
		$booking_data['details'] = $booking_details;

		return $booking_data;
		
	}
	/**
	 * general function to send email to agent with specify subject, body content
	 */
	public static function send( $emailto, $subject, $body ){

		$from_name 	= get_option('listeo_emails_name',get_bloginfo( 'name' ));
		$from_email = get_option('listeo_emails_from_email', get_bloginfo( 'admin_email' ));
		$headers 	= sprintf( "From: %s <%s>\r\n Content-type: text/html", $from_name, $from_email );

		if( empty($emailto) || empty( $subject) || empty($body) ){
			return ;
		}
		$subject = htmlentities(htmlspecialchars_decode($subject));
		$template_loader = new listeo_core_Template_Loader;
		ob_start();

			$template_loader->get_template_part( 'emails/header' ); ?>
			<tr>
				<td align="left" valign="top" style="border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; padding-left: 25px; padding-right: 25px; padding-bottom: 28px; width: 87.5%; font-size: 16px; font-weight: 400; 
				padding-top: 28px; 
				color: #666;
				font-family: sans-serif;" class="paragraph">
				<?php echo $body;?>
				</td>
			</tr>
		<?php
			$template_loader->get_template_part( 'emails/footer' ); 
		$content = ob_get_clean();
			
		wp_mail( @$emailto, @$subject, @$content, $headers );

	}

	public  function replace_shortcode( $args, $body ) {

		$tags =  array(
			'user_mail' 	=> "",
			'user_name' 	=> "",
			'booking_date' => "",
			'listing_name' => "",
			'listing_url' => '',
			'site_name' => '',
			'site_url'	=> '',
			'payment_url'	=> '',
			'expiration'	=> '',
			'dates'	=> '',
			'children'	=> '',
			'adults'	=> '',
			'tickets'	=> '',
			'details'	=> '',
			'login'	=> '',
			'password'	=> '',
			'first_name'	=> '',
			'last_name'	=> '',
			'login_url'	=> '',
			'sender'	=> '',
			'conversation_url'	=> '',
		);
		$tags = array_merge( $tags, $args );

		extract( $tags );

		$tags 	 = array( '{user_mail}',
						  '{user_name}',
						  '{booking_date}',
						  '{listing_name}',
						  '{listing_url}',
						  '{site_name}',
						  '{site_url}',
						  '{payment_url}',
						  '{expiration}',
						  '{dates}',
						  '{children}',
						  '{adults}',
						  '{tickets}',
						  '{details}',
						  '{login}',
						  '{password}',
						  '{first_name}',
						  '{last_name}',
						  '{login_url}',
						  '{sender}',
						  '{conversation_url}',
						);

		$values  = array(   $user_mail, 
							$user_name ,
							$booking_date,
							$listing_name,
							$listing_url,
							get_bloginfo( 'name' ) ,
							get_home_url(), 
							$payment_url,
							$expiration,
							$dates,
							$children,
							$adults,
							$tickets,
							$details,
							$login,
							$password,
							$first_name,
							$last_name,
							$login_url,
							$sender,
							$conversation_url,
		);
	
		$message = str_replace($tags, $values, $body);	
		
		$message = nl2br($message);
		$message = htmlspecialchars_decode($message,ENT_QUOTES);

		return $message;
	}
}
?>