<?php
/**
 * Listing model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing.
 *
 * @OA\Schema(description="")
 */
class Listing extends Entity {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [

					/**
					* @OA\Property(
					*   property="title",
					*   type="string",
					*   description="Listing title.",
					* )
					 */
					'title'            => [
						'label'      => hivepress()->translator->get_string( 'title' ),
						'type'       => 'text',
						'max_length' => 256,
						'required'   => true,
						'_alias'     => 'post_title',
					],

					'status'           => [
						'type'    => 'select',

						'options' => [
							'publish'    => '',
							'future'     => '',
							'draft'      => esc_html_x( 'Hidden', 'listing', 'hivepress' ),
							'pending'    => esc_html_x( 'Pending', 'listing', 'hivepress' ),
							'private'    => '',
							'trash'      => '',
							'auto-draft' => '',
							'inherit'    => '',
						],
					],

					'drafted'          => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'featured'         => [
						'label'     => esc_html_x( 'Featured', 'listing', 'hivepress' ),
						'type'      => 'checkbox',
						'_external' => true,
					],

					'verified'         => [
						'label'     => esc_html_x( 'Verified', 'listing', 'hivepress' ),
						'type'      => 'checkbox',
						'_external' => true,
					],

					'created_date'     => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date',
					],

					'created_date_gmt' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_date_gmt',
					],

					'modified_date'    => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'post_modified',
					],

					'expired_time'     => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'featured_time'    => [
						'type'      => 'number',
						'min_value' => 0,
						'_external' => true,
					],

					'vendor'           => [
						'type'   => 'id',
						'_alias' => 'post_parent',
						'_model' => 'vendor',
					],

					'categories'       => [
						'option_args' => [ 'taxonomy' => 'hp_listing_category' ],
						'_model'      => 'listing_category',
					],

					'image'            => [
						'type'      => 'id',
						'_alias'    => '_thumbnail_id',
						'_model'    => 'attachment',
						'_external' => true,
					],

					'images'           => [
						'label'     => hivepress()->translator->get_string( 'images' ),
						'caption'   => hivepress()->translator->get_string( 'select_images' ),
						'type'      => 'attachment_upload',
						'multiple'  => true,
						'max_files' => 10,
						'formats'   => [ 'jpg', 'jpeg', 'png' ],
						'_model'    => 'attachment',
						'_relation' => 'one_to_many',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets image IDs.
	 *
	 * @return array
	 */
	final public function get_images__id() {
		if ( ! isset( $this->values['images__id'] ) ) {

			// Get cached image IDs.
			$image_ids = hivepress()->cache->get_post_cache( $this->id, 'image_ids', 'models/attachment' );

			if ( is_null( $image_ids ) ) {
				$image_ids = [];

				foreach ( get_attached_media( 'image', $this->id ) as $image ) {
					if ( ! $image->hp_parent_field || 'images' === $image->hp_parent_field ) {
						$image_ids[] = $image->ID;
					}
				}

				if ( has_post_thumbnail( $this->id ) ) {
					$image_id = absint( get_post_thumbnail_id( $this->id ) );

					if ( ! in_array( $image_id, $image_ids, true ) ) {
						array_unshift( $image_ids, $image_id );
					}
				}

				// Cache image IDs.
				hivepress()->cache->set_post_cache( $this->id, 'image_ids', 'models/attachment', $image_ids );
			}

			// Set field value.
			$this->set_images( $image_ids );
			$this->values['images__id'] = $image_ids;
		}

		return $this->fields['images']->get_value();
	}

	/**
	 * Gets image URLs.
	 *
	 * @param string $size Image size.
	 * @return array
	 */
	final public function get_images__url( $size = 'thumbnail' ) {

		// Get field name.
		$name = 'images__url__' . $size;

		if ( ! isset( $this->values[ $name ] ) ) {

			// Get image URLs.
			$image_urls = [];

			if ( $this->get_images__id() ) {
				foreach ( $this->get_images__id() as $image_id ) {
					$urls = wp_get_attachment_image_src( $image_id, $size );

					if ( $urls ) {
						$image_urls[] = hp\get_first_array_value( $urls );
					}
				}
			}

			// Set field value.
			$this->values[ $name ] = $image_urls;
		}

		return $this->values[ $name ];
	}
}
