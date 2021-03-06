<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
add_action( 'wp_enqueue_scripts', 'awooc_enqueue_script_style', 100 );
function awooc_enqueue_script_style() {
	wp_enqueue_script( 'awooc-scripts', AWOOC_PLUGIN_URI .
	                                    'assets/js/awooc-scripts.js', array( 'jquery' ), AWOOC_PLUGIN_VER, true );
	wp_enqueue_style( 'awooc-styles', AWOOC_PLUGIN_URI . 'assets/css/awooc-styles.css', array(), AWOOC_PLUGIN_VER );
	wp_localize_script( 'awooc-scripts', 'awooc_scrpts', array(
		'url'   => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'awooc-nonce' ),
	) );
}

add_action( 'wp_ajax_awooc_ajax_variant_order', 'awooc_ajax_scripts_callback' );
add_action( 'wp_ajax_nopriv_awooc_ajax_variant_order', 'awooc_ajax_scrpts_callback' );
function awooc_ajax_scripts_callback() {
	
	if ( ! wp_verify_nonce( $_POST['nonce'], 'awooc-nonce' ) ) {
		wp_die( 'Данные отправлены с левого адреса' );
	}
	$product_var_id = $_POST['id'] ? esc_attr( $_POST['id'] ) : 0;
	if ( 0 == $product_var_id ) {
		wp_die();
	}
	$product    = wc_get_product( $product_var_id );
	$attributes = $product->get_attributes();
	$attr_name  = array();
	foreach ( $attributes as $attr => $value ) {
		$attr_label = wc_attribute_label( $attr );
		$meta       = get_post_meta( $product_var_id, wc_variation_attribute_name( $attr ), true );
		$term       = get_term_by( 'slug', $meta, $attr );
		if ( false != $term ) {
			$attr_name[] = $attr_label . ': ' . $term->name;
		} else {
			$attr_name[] = $attr_label . ': ' . $meta;
		}
	}

	$product_var_attr = esc_html(implode( '; ', $attr_name ));
	wp_send_json( $product_var_attr );
	wp_die();
}

add_filter( 'woocommerce_is_purchasable', 'awooc_disable_add_to_cart' );
function awooc_disable_add_to_cart() {
	if ( is_product() ) {
		return true;
	}
	
	return false;
}

add_action( 'woocommerce_after_add_to_cart_button', 'awooc_add_custom_button' );
function awooc_add_custom_button() {
	global $product;
	?>
	<a href="#awooc-form-custom-order" data-value-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
		class="awooc-custom-order button alt">Заказать</a>
	<?php
}

add_action( 'wp_footer', 'awooc_form_custom_order' );
function awooc_form_custom_order() {
	global $product;
	if ( ! is_product() ) {
		return;
	}
	?>
	<div id="awooc-form-custom-order" class="awooc-form-custom-order awooc-hide">
		<div class="awooc-custom-order-wrap">
			<div class="awooc-col">
				<?php
				$post_thumbnail_id = get_post_thumbnail_id( $product->get_id() );
				$full_size_image   = wp_get_attachment_image_src( $post_thumbnail_id, 'shop_single' );
				?>
				<div class="awooc-form-custom-order-img">
					<img src="<?php echo esc_url( $full_size_image[0] ) ?>" alt="">
				</div>
				<div class="awooc-form-custom-order-price"></div>
			</div>
			<div class="awooc-col">
				<h2 class="awooc-form-custom-order-title"><?php echo esc_html( $product->get_title() ); ?></h2>
				<div class="awooc-form-custom-order-attr"></div>
				<?php
				if (!empty(get_option('woocommerce_awooc_select_form')))
				echo do_shortcode( '[contact-form-7 id="' . esc_html(get_option('woocommerce_awooc_select_form')) . '"]' );
				?>
			</div>
		</div>
	</div>
	<?php
}
