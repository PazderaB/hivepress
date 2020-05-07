<?php
/**
 * Attachment upload field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attachment upload field class.
 *
 * @class Attachment_Upload
 */
class Attachment_Upload extends Field {

	/**
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * File formats.
	 *
	 * @var array
	 */
	protected $formats = [];

	/**
	 * Multiple flag.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Maximum files.
	 *
	 * @var int
	 */
	protected $max_files = 1;

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set caption.
		if ( is_null( $this->caption ) ) {
			if ( $this->multiple ) {
				$this->caption = esc_html__( 'Select Files', 'hivepress' );
			} else {
				$this->caption = esc_html__( 'Select File', 'hivepress' );
			}
		}

		parent::boot();
	}

	/**
	 * Gets file formats.
	 *
	 * @return array
	 */
	final public function get_formats() {
		return $this->formats;
	}

	/**
	 * Checks multiple flag.
	 *
	 * @return bool
	 */
	final public function is_multiple() {
		return $this->multiple;
	}

	/**
	 * Gets maximum files.
	 *
	 * @return int
	 */
	final public function get_max_files() {
		return $this->max_files;
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( $this->multiple && ! is_null( $this->value ) ) {
			if ( [] !== $this->value ) {
				$this->value = (array) $this->value;
			} else {
				$this->value = null;
			}
		} elseif ( ! $this->multiple && is_array( $this->value ) ) {
			if ( $this->value ) {
				$this->value = hp\get_first_array_value( $this->value );
			} else {
				$this->value = null;
			}
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->multiple ) {
			$this->value = array_filter( array_map( 'absint', $this->value ) );
		} else {
			$this->value = absint( $this->value );
		}

		if ( empty( $this->value ) ) {
			$this->value = null;
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			$attachment_ids = Models\Attachment::query()->filter(
				[
					'id__in' => (array) $this->value,
				]
			)->get_ids();

			if ( count( $attachment_ids ) !== count( (array) $this->value ) ) {
				/* translators: %s: field label. */
				$this->add_errors( sprintf( esc_html__( '"%s" field contains an invalid value.', 'hivepress' ), $this->label ) );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Get ID.
		$id = $this->name . '_' . uniqid();

		// Render attachments.
		$output .= '<div class="hp-row" ' . ( $this->multiple ? 'data-component="sortable"' : '' ) . '>';

		if ( ! is_null( $this->value ) ) {

			// Get attachments.
			$attachments = Models\Attachment::query()->filter(
				[
					'id__in' => (array) $this->value,
				]
			)->get();

			// Render attachments.
			foreach ( $attachments as $attachment ) {
				$output .= $this->render_attachment( $attachment );
			}
		}

		$output .= '</div>';

		// Render error messages.
		$output .= '<div class="hp-form__messages hp-form__messages--error" data-component="messages"></div>';

		// Render upload button.
		$output .= '<label for="' . esc_attr( $id ) . '">';

		$output .= ( new Button(
			[
				'label' => $this->caption,
			]
		) )->render();

		// Render upload field.
		$output .= ( new File(
			[
				'name'       => $this->name,
				'multiple'   => $this->multiple,
				'formats'    => $this->formats,
				'disabled'   => true,

				'attributes' => [
					'id'             => $id,
					'data-component' => 'file-upload',
					'data-url'       => esc_url( hivepress()->router->get_url( 'attachment_upload_action' ) ),
				],
			]
		) )->render();

		$output .= '</label>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Renders attachment HTML.
	 *
	 * @param object $attachment Attachment object.
	 * @return string
	 */
	public function render_attachment( $attachment ) {
		$output = '<div class="hp-col-sm-2 hp-col-xs-4" data-url="' . esc_url( hivepress()->router->get_url( 'attachment_update_action', [ 'attachment_id' => $attachment->get_id() ] ) ) . '">';

		// Render attachment.
		if ( strpos( $attachment->get_mime_type(), 'image/' ) === 0 ) {
			$output .= wp_get_attachment_image( $attachment->get_id(), 'thumbnail' );
		} else {
			$output .= '<div><span>' . esc_html( wp_basename( get_attached_file( $attachment->get_id() ) ) ) . '</span></div>';
		}

		// Render delete button.
		$output .= '<a href="#" title="' . esc_attr__( 'Delete', 'hivepress' ) . '" data-component="file-delete" data-url="' . esc_url( hivepress()->router->get_url( 'attachment_delete_action', [ 'attachment_id' => $attachment->get_id() ] ) ) . '"><i class="hp-icon fas fa-times"></i></a>';

		$output .= '</div>';

		return $output;
	}
}
