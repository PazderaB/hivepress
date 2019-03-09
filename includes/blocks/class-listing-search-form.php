<?php
/**
 * Listing search form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing search form block class.
 *
 * @class Listing_Search_Form
 */
class Listing_Search_Form extends Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'title' => esc_html__( 'Listing Search Form', 'hivepress' ),
			]
		);

		parent::init( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$form = new \HivePress\Forms\Listing_Search( $this->get_attributes() );

		$form->set_values( $_GET );

		return $form->render();
	}
}
