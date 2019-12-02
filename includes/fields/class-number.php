<?php
/**
 * Number field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Number field class.
 *
 * @class Number
 */
class Number extends Field {

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
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Decimals number.
	 *
	 * @var int
	 */
	protected $decimals = 0;

	/**
	 * Minimum value.
	 *
	 * @var int
	 */
	protected $min_value;

	/**
	 * Maximum value.
	 *
	 * @var int
	 */
	protected $max_value;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Number', 'hivepress' ),

				'settings' => [
					'placeholder' => [
						'label' => esc_html__( 'Placeholder', 'hivepress' ),
						'type'  => 'text',
						'order' => 10,
					],

					'decimals'    => [
						'label'     => esc_html__( 'Decimals', 'hivepress' ),
						'type'      => 'number',
						'default'   => 0,
						'min_value' => 0,
						'max_value' => 5,
						'order'     => 20,
					],

					'min_value'   => [
						'label' => esc_html__( 'Minimum Value', 'hivepress' ),
						'type'  => 'number',
						'order' => 30,
					],

					'max_value'   => [
						'label' => esc_html__( 'Maximum Value', 'hivepress' ),
						'type'  => 'number',
						'order' => 40,
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
				'filters' => true,
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
		if ( ! is_null( $this->placeholder ) ) {
			$attributes['placeholder'] = $this->placeholder;
		}

		// Set step.
		$attributes['step'] = (float) sprintf( '%f', 1 / pow( 10, $this->decimals ) );

		// Set minimum value.
		if ( ! is_null( $this->min_value ) ) {
			$attributes['min'] = $this->min_value;
		}

		// Set maximum value.
		if ( ! is_null( $this->max_value ) ) {
			$attributes['max'] = $this->max_value;
		}

		// Set required property.
		if ( $this->required ) {
			$attributes['required'] = true;
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
			return number_format_i18n( $this->value, strlen( substr( strrchr( (string) $this->value, '.' ), 1 ) ) );
		}

		return $this->value;
	}

	/**
	 * Adds field filters.
	 */
	protected function add_filters() {
		parent::add_filters();

		$this->filters['type'] = 'NUMERIC';
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_numeric( $this->value ) ) {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->decimals > 0 ) {
			$this->value = round( floatval( $this->value ), $this->decimals );
		} else {
			$this->value = intval( $this->value );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			if ( ! is_null( $this->min_value ) && $this->value < $this->min_value ) {
				$this->add_errors( [ sprintf( esc_html__( '"%1$s" can\'t be lower than %2$s.', 'hivepress' ), $this->label, number_format_i18n( $this->min_value ) ) ] );
			}

			if ( ! is_null( $this->max_value ) && $this->value > $this->max_value ) {
				$this->add_errors( [ sprintf( esc_html__( '"%1$s" can\'t be greater than %2$s.', 'hivepress' ), $this->label, number_format_i18n( $this->max_value ) ) ] );
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
		return '<input type="' . esc_attr( static::get_type() ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
