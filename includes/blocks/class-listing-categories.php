<?php
/**
 * Listing categories block.
 *
 * @package HivePress\Blocks
 */
// todo.
namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing categories block class.
 *
 * @class Listing_Categories
 */
class Listing_Categories extends Block {

	/**
	 * Template type.
	 *
	 * @var string
	 */
	protected $template = 'view';

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Listing categories number.
	 *
	 * @var int
	 */
	protected $number;

	/**
	 * Listing category parent.
	 *
	 * @var int
	 */
	protected $parent;

	/**
	 * Listing categories order.
	 *
	 * @var string
	 */
	protected $order;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'title'    => hivepress()->translator->get_string( 'listing_categories' ),

				'settings' => [
					'columns' => [
						'label'    => esc_html__( 'Columns', 'hivepress' ),
						'type'     => 'select',
						'default'  => 3,
						'required' => true,
						'_order'   => 10,

						'options'  => [
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],

					'number'  => [
						'label'     => esc_html__( 'Number', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'default'   => 3,
						'_order'    => 20,
					],

					'parent'  => [
						'label'       => esc_html__( 'Parent Category', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'default'     => '',
						'_order'      => 30,
					],

					'order'   => [
						'label'   => esc_html__( 'Order', 'hivepress' ),
						'type'    => 'select',
						'default' => '',
						'_order'  => 40,

						'options' => [
							'name'  => esc_html__( 'Category Name', 'hivepress' ),
							'count' => esc_html__( 'Listing Count', 'hivepress' ),
						],
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set category parent.
		if ( ! isset( $this->parent ) ) {
			$this->parent = hp\get_array_value( $this->context, 'listing_category_id' );
		}

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get column width.
		$columns      = absint( $this->columns );
		$column_width = 12;

		if ( $columns > 0 && $columns <= 12 ) {
			$column_width = round( $column_width / $columns );
		}

		// Set query.
		$query = Models\Listing_Category::query()->limit( $this->number );

		// Set parent.
		if ( $this->parent ) {
			$query->filter( [ 'parent_id' => $this->parent ] );
		}

		// Set order.
		if ( 'name' === $this->order ) {
			$query->order( [ 'name' => 'asc' ] );
		} elseif ( 'count' === $this->order ) {
			$query->order( [ 'count' => 'desc' ] );
		} else {
			$query->set_args(
				[
					'meta_key' => 'hp_order',
					'orderby'  => 'meta_value_num',
					'order'    => 'ASC',
				]
			);
		}

		// Get cached IDs.
		$listing_category_ids = hivepress()->cache->get_cache( array_merge( $query->get_args(), [ 'fields' => 'ids' ] ), 'listing_category' );

		if ( is_array( $listing_category_ids ) ) {
			$query = Models\Listing_Category::query()->filter(
				[
					'id__in' => $listing_category_ids,
				]
			)->order( 'id__in' )->limit( count( $listing_category_ids ) );
		}

		// Query categories.
		$categories = $query->get_all();

		// Cache IDs.
		if ( is_null( $listing_category_ids ) && $categories->count() <= 1000 ) {
			hivepress()->cache->set_cache(
				array_merge( $query->get_args(), [ 'fields' => 'ids' ] ),
				'listing_category',
				array_map(
					function( $category ) {
						return $category->get_id();
					},
					$categories->getArrayCopy()
				)
			);
		}

		// Render categories.
		if ( ! empty( $categories ) ) {
			$output  = '<div class="hp-grid hp-block">';
			$output .= '<div class="hp-row">';

			foreach ( $categories as $category ) {
				$output .= '<div class="hp-grid__item hp-col-sm-' . esc_attr( $column_width ) . ' hp-col-xs-12">';

				$output .= ( new Template(
					[
						'template' => 'listing_category_' . $this->template . '_block',

						'context'  => [
							'listing_category' => $category,
						],
					]
				) )->render();

				$output .= '</div>';
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		return $output;
	}
}
