<?php

/*
Plugin Name: Dagens Access Control
Plugin URI: http://www.retrofitter.dk
Description: Adds possibility to control which users can access content.
Version: 1.0.1
Author: Steffen Bang Nielsen
Author URI: http://www.retrofitter.dk
License: GPL2
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
add_action( 'plugins_loaded', 'dagens_access_control_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */

function dagens_access_control_load_textdomain() {
  load_plugin_textdomain( 'dagens_access_control', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


function dagens_access_body_classes( $classes ) {
	$user_type = UserInfo();

	if ($user_type == 'subscriber') {
		$classes[] = 'subscriber-access';
	}
	elseif ($user_type == 'social') {
		$classes[] = 'social-access';
	}
	elseif ($user_type == 'company') {
		$classes[] = 'company-access';
	}
	elseif ($user_type == 'visitor') {
		$classes[] = 'visitor-access';
	}

	return $classes;
}
add_filter( 'body_class', 'dagens_access_body_classes' );

if ( ! function_exists( 'dagens_access_user_info' ) ) :

	add_action('wp_footer', 'dagens_access_user_info');

	function dagens_access_user_info()
	{
		$the_ip = getip();
		$userref = getref();
		$user_access_way = UserAccessWay();
		$user_type = UserInfo();
		?>
		<div id="user-info" class="hide">
			<p class="referer">
				<a href="<?php echo esc_url( $userref ); ?>">Referer=<?php echo esc_url( $userref ); ?></a>
			</p>
			<p class="accessway">
				Accessway=<?php echo esc_attr( $user_access_way ); ?>
			</p>
			<p class="userip">
				IP=<?php echo $the_ip; ?>
			</p>
			<p class="useraccess">
				Useraccess=<?php echo $user_type; ?>
			</p>
			<p class="client-ip">
				Client-ip=<?php echo $_SERVER["HTTP_CLIENT_IP"]; ?>
			</p>
			<p class="forwarded-ip">
				forwarded-ip=<?php echo $_SERVER["HTTP_X_FORWARDED_FOR"]; ?>
			</p>
			<p class="remote-ip">
				remote-ip=<?php echo $_SERVER["REMOTE_ADDR"]; ?>
			</p>
			<p class="remote-agent">
				remote-agent=<?php echo $_SERVER["HTTP_USER_AGENT"]; ?>
			</p>
		</div>
		<?php
	}
endif;

add_filter( 'template_include', 'dagens_access_control', 99 );
function dagens_access_control( $template ) {

	$user_type = UserInfo();

	if ( is_single() && ! current_user_can( 'read_posts' ) && ! has_post_format( array( 'aside', 'link' ) ) ) {
		if ($user_type == 'company') {
			$new_template = locate_template( array( 'single-company-access.php' ) );
		} elseif ($user_type == 'social') {
			$new_template = locate_template( array( 'single-social-access.php' ) );
		}
		if ( '' != $new_template ) {
			return $new_template ;
		}
	}

	return $template;
}

function UserInfo() {
	$the_ip  = getip();
	$user_access_way = UserAccessWay();

	if ( current_user_can( 'read_posts' ) ) {
		$useraccess = 'subscriber';
	} elseif ( $the_ip == '85.81.93.191' ) {
		$useraccess = 'company';
	} elseif ( $user_access_way == 'google' || $user_access_way == 'facebook' || $user_access_way == 'twitter' || $user_access_way == 'linkedin' ) {
		$useraccess = 'social';
	} else {
		$useraccess = 'visitor';
	}

	return $useraccess;
}

function UserAccessWay()
{
	$user_ref = getref();
	$user_agent = getagent();

	if (strpos($user_agent, 'googlebot') !== false) {
		$accessway = 'google';
	}
	if (strpos($user_ref, 'google.') !== false) {
		$accessway = 'google';
	}
	if (strpos($user_ref, 'facebook.') !== false) {
		$accessway = 'facebook';
	}
	if (strpos($user_ref, 'twitter.') !== false) {
		$accessway = 'twitter';
	}
	if (strpos($user_ref, 't.co') !== false) {
		$accessway = 'twitter';
	}
	if (strpos($user_ref, 'linkedin.') !== false) {
		$accessway = 'linkedin';
	}
	return $accessway;
}

function getip(){
	if(isset($_SERVER["REMOTE_ADDR"])){
		return $_SERVER["REMOTE_ADDR"];
	}elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
		return $_SERVER["HTTP_X_FORWARDED_FOR"];
	}elseif(isset($_SERVER["HTTP_CLIENT_IP"])){
		return $_SERVER["HTTP_CLIENT_IP"];
	}
}

function getref() {
	$userref = $_SERVER['HTTP_REFERER'];
	return $userref;
}

function getagent() {
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	return $useragent;
}