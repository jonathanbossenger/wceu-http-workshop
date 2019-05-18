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

/**
 * Step 1: Let's prepare the subscriber list
 * https://developer.wordpress.org/reference/functions/add_shortcode/
 */
add_shortcode( 'wceu_subscribers_shortcode', 'wceu_subscribers_shortcode' );
function wceu_subscribers_shortcode() {
	ob_start();
	?>
	<h1>Subscriber List</h1>
	<table>
		<tr>
			<td>Email</td>
		</tr>
		<tr>
			<td></td>
		</tr>
	</table>
	<?php
	$html = ob_get_clean();

	return $html;
}
