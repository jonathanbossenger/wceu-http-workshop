<?php
/**
 * Plugin Name:     Wceu Http Workshop
 * Plugin URI:      https://jonathanbossenger.com
 * Description:     Simple Shortcode based newsletter subscribe form that connets to the MailChimp API to subscribe a user
 * Author:          Jonathan Bossenger
 * Author URI:      https://jonathanbossenger.com
 * Text Domain:     wceu-http-workshop
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wceu_Http_Workshop
 */

define( 'WCEU_MAILCHIMP_KEY', '183cf599ae5f86ac092e80746dbf8a12-us13' );
define( 'WCEU_MAILCHIMP_LIST_ID', '79447f0a95' );

/**
 * Step 1: Let's build a simple form
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
add_shortcode( 'wceu_form_shortcode', 'wceu_form_shortcode' );
function wceu_form_shortcode() {
	ob_start();
	?>
	<form>
		<input type="hidden" name="wceu_form" value="submit">
		<div>
			<label for="email">Email address</label>
			<input type="text" id="email" name="email" placeholder="Email address">
		</div>
		<div>
			<input type="submit" id="submit" name="submit" value="Submit">
		</div>
	</form>
	<?php
	$form = ob_get_clean();

	return $form;
}

/**
 * Step 2: Let's process the form data
 * https://developer.wordpress.org/reference/hooks/wp/
 */
add_action( 'wp', 'wceu_maybe_process_form' );
function wceu_maybe_process_form() {
	//@todo homework: learn about and implement nonce checking
	if ( ! isset( $_GET['wceu_form'] ) ) {
		return;
	}
	$wceu_form = $_GET['wceu_form']; //phpcs:ignore WordPress.Security.NonceVerification
	if ( ! empty( $wceu_form ) && 'submit' === $wceu_form ) {
		$email = $_GET['email']; //phpcs:ignore WordPress.Security.NonceVerification

		$subscribe_data = array(
			'email_address' => $email,
		);
		$subscribed     = subscribe_email_to_mailchimp_list( $subscribe_data );
		if ( $subscribed ) {
			update_option( 'wceu_email', $email );
		}
	}
}

/**
 * Step 3: Let's POST the form data to MailChimp
 * https://developer.wordpress.org/reference/functions/wp_remote_post/
 */
function subscribe_email_to_mailchimp_list( $subscribe_data ) {

	$api_key = WCEU_MAILCHIMP_KEY;
	$list_id = WCEU_MAILCHIMP_LIST_ID;

	$api_parts = explode( '-', $api_key );
	$dc        = $api_parts[1];

	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ), //phpcs:ignore
		),
		'body'    => json_encode( $subscribe_data ), //phpcs:ignore
		'timeout' => '30',
	);

	$api_url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/';

	$response = wp_remote_post( $api_url, $args );
	if ( is_wp_error( $response ) ) {
		return false;
	}

	$response_object = json_decode( wp_remote_retrieve_body( $response ) );
	if ( empty( $response_object || ! isset( $response_object->status ) || 'subscribed' !== $response_object->status ) ) {
		return false;
	}

	return true;
}
