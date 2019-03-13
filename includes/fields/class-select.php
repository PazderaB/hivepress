<?php
/**
 * Select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Select field class.
 *
 * @class Select
 */
class Select extends Field {

	/**
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Field title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Field settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Select options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Select', 'hivepress' ),
				'settings' => [
					'multiple' => [
						'label'   => esc_html__( 'Multiple', 'hivepress' ),
						'caption' => 'todo check if multiple',
						'type'    => 'checkbox',
						'order'   => 10,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			if ( $this->multiple ) {
				$this->value = array_map( 'sanitize_text_field', $this->value );
			} else {
				$this->value = sanitize_text_field( $this->value );
			}
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && count( array_intersect( array_map( 'strval', (array) $this->value ), array_map( 'strval', array_keys( $this->options ) ) ) ) === 0 ) {
			if ( $this->multiple ) {
				$this->add_errors( [ sprintf( esc_html__( '%s are invalid', 'hivepress' ), $this->label ) ] );
			} else {
				$this->add_errors( [ sprintf( esc_html__( '%s is invalid', 'hivepress' ), $this->label ) ] );
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
		$output = '<select name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
