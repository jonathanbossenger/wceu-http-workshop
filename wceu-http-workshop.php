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

/**
 * Step 1: Let's build a simple form
 */
add_shortcode( 'wceu_form_shortcode', 'wceu_form_shortcode' );
function wceu_form_shortcode() {
	ob_start();
	?>
	<form>
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
