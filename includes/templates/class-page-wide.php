<?php
/**
 * Abstract wide page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Wide page template class.
 *
 * @class Page_Wide
 */
abstract class Page_Wide extends Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'blocks' => [
							'page_content' => [
								'type'       => 'container',
								'tag'        => 'main',
								'_order'     => 10,

								'attributes' => [
									'class' => [ 'hp-page__content' ],
								],

								'blocks'     => [
									'breadcrumb_menu' => [
										'type'   => 'menu',
										'menu'   => 'breadcrumb',
										'_order' => 10,
									],

									'page_title'      => [
										'type'   => 'part',
										'path'   => 'page/page-title',
										'_order' => 20,
									],
								],
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
