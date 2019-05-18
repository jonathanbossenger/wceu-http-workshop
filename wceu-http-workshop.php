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

define( 'WCEU_MAILCHIMP_KEY', '64370682219ee7dc16662450084bab61-us3' );
define( 'WCEU_MAILCHIMP_LIST_ID', 'f7d0fcced3' );

require 'debugger.php';

/**
 * Step 1: Let's prepare the subscriber list
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
add_shortcode( 'wceu_subscribers_shortcode', 'wceu_subscribers_shortcode' );
function wceu_subscribers_shortcode() {
	ob_start();
	$subscriber_emails = wceu_get_mailchimp_subscribers();
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
 *
 * @param $api_key
 *
 * @return array|string|WP_Error
 */
function wceu_get_mailchimp_subscribers() {
	$api_key = WCEU_MAILCHIMP_KEY;
	$list_id = WCEU_MAILCHIMP_LIST_ID;

	$api_parts = explode( '-', $api_key );
	$dc        = $api_parts[1];

	$args = array(
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( 'user:' . $api_key ),
		),
		'timeout' => '30',
	);

	$api_url = 'https://' . $dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/';

	$response = wp_remote_get( $api_url, $args );
	if ( ! is_wp_error( $response ) ) {
		$response_object = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! empty( $response_object ) ) {
			$response = array();
			foreach ( $response_object->members as $member ) {
				$response[] = $member->email_address;
			}
		} else {
			$response = 'An error occurred retrieving the subscriber lists.';
		}
	} else {
		$response = 'An error occurred connecting the MailChimp API.';
	}

	return $response;
}


/**
 * Step 1: Let's build a simple form
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
add_shortcode( 'wceu_form_shortcode', 'wceu_form_shortcode' );
function wceu_form_shortcode() {
	ob_start();
	?>
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
 * Step 2: Let's process the form data
 * https://developer.wordpress.org/reference/hooks/wp/
 */
add_action( 'wp', 'wceu_maybe_process_form' );
function wceu_maybe_process_form() {
	//@todo homework: learn about and implement nonce checking
	if ( ! isset( $_POST['wceu_form'] ) ) {
		return;
	}
	$wceu_form = $_POST['wceu_form']; //phpcs:ignore WordPress.Security.NonceVerification
	if ( ! empty( $wceu_form ) && 'submit' === $wceu_form ) {
		$email = $_POST['email']; //phpcs:ignore WordPress.Security.NonceVerification

		$subscribe_data = array(
			'status'        => 'subscribed',
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
/**
 * Step 4: Let's add some logging
 * https://gist.github.com/jonathanbossenger/54c7741260f7e2687f00edae60489c74
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

	wceu_error_log( $response_object );

	if ( empty( $response_object || ! isset( $response_object->status ) || 'subscribed' !== $response_object->status ) ) {
		return false;
	}

	return true;
}
