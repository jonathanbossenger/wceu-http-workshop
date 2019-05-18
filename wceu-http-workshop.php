<?php
/**
 * Plugin Name:     Wceu Http Workshop
 * Plugin URI:      https://jonathanbossenger.com
 * Description:     Simple Shortcode based newsletter subscribe form that connets to the MailChimp API to subscribe a user
 * Author:          Jonathan Bossenger
 * Author URI:      https://jonathanbossenger.com
 * Text Domain:     wceu-http-workshop
 * Domain Path:     /languages
 * Version:         0.0.1
 *
 * @package         Wceu_Http_Workshop
 */

require 'debugger.php';

define( 'WCEU_MAILCHIMP_KEY', '64370682219ee7dc16662450084bab61-us3' );
define( 'WCEU_MAILCHIMP_LIST_ID', 'f7d0fcced3' );

/**
 * Step 1: Let's prepare the subscriber list
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
add_shortcode( 'wceu_subscribers_shortcode', 'wceu_subscribers_shortcode' );
function wceu_subscribers_shortcode() {
	$subscriber_emails   = array();
	$subscriber_response = wceu_get_mailchimp_subscribers();

	if ( 'failure' === $subscriber_response['status'] ) {
		echo esc_html( $subscriber_response['message'] );
	} else {
		$subscriber_emails = $subscriber_response['subscriber_emails'];
	}

	ob_start();
	?>
	<h1>Subscriber List</h1>
	<table>
		<tr>
			<td>Email</td>
		</tr>
		<?php foreach ( $subscriber_emails as $subscriber_email ) { ?>
			<tr>
				<td><?php echo esc_html( $subscriber_email ); ?></td>
			</tr>
		<?php } ?>
	</table>
	<?php
	$html = ob_get_clean();

	return $html;
}

/**
 * Step 2: Let's get the subscribers from MailChimp
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
/**
 * Step 3: Let's add some debugging
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
/**
 * Get MailChimp Subscriber Lists
 */
function wceu_get_mailchimp_subscribers() {

	$response = array(
		'status'  => 'failure',
		'message' => '',
	);

	$api_key = WCEU_MAILCHIMP_KEY;
	$list_id = WCEU_MAILCHIMP_LIST_ID;

	$api_parts = explode( '-', $api_key );
	$dc        = $api_parts[1];

	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ), //phpcs:ignore WordPress.PHP
		),
		'timeout' => '30',
	);

	$api_url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/';

	$api_response = wp_remote_get( $api_url, $args );

	wceu_error_log( $api_response );

	if ( is_wp_error( $api_response ) ) {
		$response['message'] = 'An error occurred connecting the MailChimp API.';

		return $response;
	}

	$response_object = json_decode( wp_remote_retrieve_body( $api_response ) );

	wceu_error_log( $response_object );

	if ( empty( $response_object ) ) {
		$response['message'] = 'An error occurred retrieving the subscriber lists.';

		return $response;
	}

	$response['status']  = 'success';
	$response['message'] = 'Retrieved Subscriber List';
	foreach ( $response_object->members as $member ) {
		$response['subscriber_emails'][] = $member->email_address;
	}

	return $response;
}

/**
 * Step 4: Let's build a simple form
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
add_shortcode( 'wceu_form_shortcode', 'wceu_form_shortcode' );
function wceu_form_shortcode() {
	ob_start();
	?>
	<h1>Subscribe</h1>
	<form method="post">
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
 * Step 5: Let's process the form data
 * https://developer.wordpress.org/reference/hooks/wp/
 */
add_action( 'wp', 'wceu_maybe_process_form' );
function wceu_maybe_process_form() {
	//@todo homework: learn about and implement nonce checking
	if ( ! isset( $_POST['wceu_form'] ) ) { //phpcs:ignore WordPress.Security
		return;
	}
	$wceu_form = $_POST['wceu_form']; //phpcs:ignore WordPress.Security.NonceVerification
	if ( ! empty( $wceu_form ) && 'submit' === $wceu_form ) {
		$email = $_POST['email']; //phpcs:ignore WordPress.Security.NonceVerification
		update_option( 'wceu_email', $email );
	}
}
