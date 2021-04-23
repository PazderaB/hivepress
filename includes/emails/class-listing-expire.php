<?php
/**
 * Listing expire email.
 *
 * @package HivePress\Emails
 */

namespace HivePress\Emails;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing expire email class.
 *
 * @class Listing_Expire
 */
class Listing_Expire extends Email {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => hivepress()->translator->get_string( 'listing_expired' ),
				'description' => esc_html__( 'This email is sent to users when listing is expired.', 'hivepress' ),
				'recipient'   => hivepress()->translator->get_string( 'vendor' ),
				'tokens'      => [ 'user_name', 'listing_title', 'listing_url' ],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Email arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'subject' => hivepress()->translator->get_string( 'listing_expired' ),
				'body'    => hp\sanitize_html( __( 'Hi, %user_name%! Your listing "%listing_title%" has expired, click on the following link to renew it: %listing_url%', 'hivepress' ) ),
			],
			$args
		);

		parent::__construct( $args );
	}
}
