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
	 * Field meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Field options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Multiple flag.
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
				'meta' => [
					'label'      => esc_html__( 'Select', 'hivepress' ),
					'filterable' => true,

					'settings'   => [
						'multiple' => [
							'label'   => esc_html__( 'Multiple', 'hivepress' ),
							'caption' => esc_html__( 'Allow multiple selection', 'hivepress' ),
							'type'    => 'checkbox',
							'_order'  => 10,
						],

						'options'  => [
							'label'    => esc_html__( 'Options', 'hivepress' ),
							'type'     => 'select',
							'multiple' => true,
							'_order'   => 20,
						],
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'placeholder' => '&mdash;',
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set placeholder.
		if ( ! is_null( $this->placeholder ) && ! $this->multiple ) {
			$this->options = [ '' => $this->placeholder ] + $this->options;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set multiple flag.
		if ( $this->multiple ) {
			$attributes['multiple'] = true;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Gets field display value.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			$options = $this->options;

			$labels = array_filter(
				array_map(
					function( $value ) use ( $options ) {
						return hp\get_array_value( $options, $value );
					},
					(array) $this->value
				),
				'strlen'
			);

			if ( $labels ) {
				return implode( ', ', $labels );
			}
		}
	}

	/**
	 * Adds field filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		if ( $this->multiple ) {
			$this->filter['operator'] = 'AND';
		} else {
			$this->filter['operator'] = 'IN';
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<select name="' . esc_attr( $this->name ) . ( $this->multiple ? '[]' : '' ) . '" ' . hp\html_attributes( $this->attributes ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= '<option value="' . esc_attr( $value ) . '" ' . ( in_array( (string) $value, (array) $this->value, true ) ? 'selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
