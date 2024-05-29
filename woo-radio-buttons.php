<?php 
/* 
 * Plugin Name: Woo Radio Buttons 
 * Plugin URI: http://designloud.com/downloads/woo-radio-buttons-3.0.zip 
 * Description: <strong>This is the radio buttons compatible with Woocommerce 4.2+.<br /> 
<strong>If you find this plugin useful please consider <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NUSCJBYCS8UL8" target="_blank">making a donation</a>, because well it wasnt easy getting this puppy goin. Thanks and enjoy!</strong> 
 * Author: DesignLoud 
 * Version: 3.0.0 
 * Author URI: http://designloud.com 
 * Tested up to: 5.5.0
 * WC tested up to: 4.4.0
 * WC requires at least: 4.3.0
 */ 

/**
 * The path of main plugin file
 * 
 * @param array $args Arguments.
 * @since 2.4.0
 * @deprecated 4.0.0
 */
function wooradio_plugin_path() { 
  // gets the absolute path to this plugin directory 
  return untrailingslashit( plugin_dir_path( __FILE__ ) ); 
} 
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

/**
 * Register scripts
 *
 * @param array $args Arguments.
 * @since 2.4.0
 */
function register_woo_radio_button_scripts () { 
	 wp_register_script( 'wc-radio-add-to-cart-variation', plugins_url( 'assets/js/frontend/add-to-cart-variation-radio.js', __FILE__ ), array( 'jquery', 'wc-add-to-cart-variation' ), '4.0.0', true );
   wp_enqueue_style( 'wc-radio-button-styles', plugins_url( 'assets/css/woo-radio-variations.css', __FILE__ ), array(), '4.0.0', 'all' );
} 
add_action( 'wp_enqueue_scripts', 'register_woo_radio_button_scripts' ); 

/**
 * Load scripts on variable product pages.
 *
 * @param array $args Arguments.
 * @since 3.0.0
 */
function woo_radio_button_enqueue_scripts () {
  wp_enqueue_script( 'wc-radio-add-to-cart-variation' );
  add_filter( 'woocommerce_reset_variations_link', '__return_empty_string' );
} 
add_action( 'woocommerce_variable_add_to_cart', 'woo_radio_button_enqueue_scripts' ); 

/**
 * Add reset link after fieldset
 * 
 * @since 4.0.0
 */
function woo_radio_buttons_add_reset_link() {
  remove_filter( 'woocommerce_reset_variations_link', '__return_empty_string' );
  echo wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<p><a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a></p>' ) );
}
add_action( 'woocommerce_after_variations_table', 'woo_radio_buttons_add_reset_link', 1 );

if ( ! function_exists( 'wc_dropdown_variation_attribute_options' ) ) {
  /**
   * Output a list of variation attributes for use in the cart forms.
   *
   * @param array $args Arguments.
   * @since 2.4.0
   */
  function wc_dropdown_variation_attribute_options( $args = array() ) {
    $args = wp_parse_args(
      apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ),
      array(
        'options'          => false,
        'attribute'        => false,
        'product'          => false,
        'selected'         => false,
        'name'             => '',
        'id'               => '',
        'class'            => '',
        'show_option_none' => __( 'None', 'woocommerce' ),
      )
    );

    // Get selected value.
    if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
      $selected_key     = 'attribute_' . sanitize_title( $args['attribute'] );
      $args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
    }

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title( $attribute );
    $id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
    $class                 = $args['class'];
    $show_option_none      = (bool) $args['show_option_none'];
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'None', 'woocommerce' ); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

    if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
      $attributes = $product->get_variation_attributes();
      $options    = $attributes[ $attribute ];
    }

    $html  = '';

    if ( ! empty( $options ) ) {

      $html .= '<fieldset class="radio__variations">';

      $html .= '<legend>' . sprintf( esc_html( __( 'Choose %s', 'woocommerce' ) ), wc_attribute_label( $attribute ) ) . '</legend>';

      $html .= '<ul class="radio__variations--list" data-attribute_name="' . esc_attr( $name ) . '">';

      if( $show_option_none ) {      
        $input_id = uniqid( 'attribute_' );
        $html .= '<li class="radio__variations--item"><input id="' . esc_attr(  $input_id ) . '" type="radio" name="' . esc_attr( $name ) . '" value="" ' . checked( sanitize_title( $args['selected'] ), '', false ) . '/><label for="' . esc_attr(  $input_id ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_none_radio', $show_option_none_text, $product ) ) .'</label></li>'; 
      }

      if ( $product && taxonomy_exists( $attribute ) ) {
        // Get terms if this is a taxonomy - ordered. We need the names too.
        $terms = wc_get_product_terms(
          $product->get_id(),
          $attribute,
          array(
            'fields' => 'all',
          )
        );

        foreach ( $terms as $term ) {
          if ( in_array( $term->slug, $options, true ) ) {
            $input_id = uniqid( 'attribute_' );
            $html .= '<li class="radio__variations--item"><input id="' . esc_attr(  $input_id ) . '" type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( sanitize_title( $args['selected'] ), $term->slug, false ) . '/><label for="' . esc_attr(  $input_id ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product ) ) .'</label></li>';
          }
        }
      } else {
        foreach ( $options as $option ) {
          $input_id = uniqid( 'attribute_' );
          $selected = checked( $args['selected'], sanitize_title( $option ), false );
          $html    .= '<li class="radio__variations--item"><input id="' . esc_attr(  $input_id ) . '" type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $option ) . '" ' . $selected . '><label for="' . esc_attr(  $input_id ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product ) ) .'</label></li>';
        }
      }
    }

    echo '</fieldset>';

    echo apply_filters( 'woocommerce_dropdown_variation_attribute_options_html', $html, $args ); // WPCS: XSS ok.
  }
}


?>