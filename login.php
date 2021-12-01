<?php
/**
* Plugin Name: Finalrope Rest APIs
* Plugin URI: https://finalrope.com/
* Description: WordPress custom Rest APIs created by Finalrope Soft Solutions Pvt. Ltd.
* Version: 1.0
* Author: Ravikas
* Author URI: https://finalrope.com/
* License: A license under GPL12
*/


/***** Login APIs *****/
add_action( 'rest_api_init', function () {
  register_rest_route( 'login-apis/finalrope', 'login-call', array(
    'methods' => 'POST',
    'callback' => 'login_apis',
  ));
});

function login_apis( $data ) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$creds = array();
	$creds['user_login'] = $username;
	$creds['user_password'] = $password;

	$user = wp_signon( $creds, false );
	$login_arr = array();
	
	if ( is_wp_error($user)) {
		$login_arr = array(
			"status" => "failed",
			"username" => $username,
			"userpass" => $password
		);
	}else{
		$user_arr = get_user_by( 'login', $user->user_login );
		$user = new WP_User( $user_arr->ID );

		/***** Check if paying customer *****/
		function paying_yesno($user_id) {
			$customer_orders = get_posts( array(
				'numberposts' => 1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user_id,
				'post_type'   => 'shop_order',
				'post_status' => 'wc-completed',
				'fields'      => 'ids',
			));
			return count($customer_orders) > 0 ? true : false; 
		}

		$login_arr = array(
			"status" => "success",
			"id" => $user->ID,
			"date_created" => $user->user_registered,
			"userpass" => $password,
			"email"   => $user->user_email,
			"first_name"   => $user->first_name,
			"last_name"    => $user->last_name,
			"role" => $user->roles[0],
			"username" => $username,
			"billing" => array(
				"first_name" => $user->billing_first_name,
				"last_name"  => $user->billing_last_name,
				"company"    => $user->billing_company,
				"address_1"  => $user->billing_address_1,
				"address_2"  => $user->billing_address_2,
				"city"       => $user->billing_city,
				"state"      => $user->billing_state,
				"postcode"   => $user->billing_postcode,
				"country"    => $user->billing_country,
				"email"      => $user->billing_email,
				"phone"      => $user->billing_phone
			),
			"shipping" => array(
				"first_name" => $user->shipping_first_name,
				"last_name"  => $user->shipping_last_name,
				"company"    => $user->shipping_company,
				"address_1"  => $user->shipping_address_1,
				"address_2"  => $user->shipping_address_2,
				"city"       => $user->shipping_city,
				"tate"       => $user->shipping_state,
				"postcode"   => $user->shipping_postcode,
				"country"    => $user->shipping_country,
				"email"      => $user->shipping_email,
				"phone"      => $user->shipping_phone
			),
			"is_paying_customer" => paying_yesno($user_id),
			"avtar_url" => get_avatar_url( get_the_author_meta($user_arr->ID) )
		);
	}
	return($login_arr);
}