<?php
/**
 * Template file for the layout2.
 *
 * @package WC_AC_POPUP_NOTIFICATION
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$cart = WC()->cart->get_cart();

$cart_item = $cart[ $cart_item_key ];

$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );

$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

$product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;';

$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );

$product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );

$image = wp_get_attachment_url( $_product->get_image_id(), 'large' );

if ( empty( $image ) ) {
	$image = esc_url( WCACN_URL . '/assets/images/placeholder.png' );
}
$stlye = "background: linear-gradient(0deg, rgb(0 0 0 / 40%), rgb(0 0 0 / 60%)), url('" . $image . "');background-repeat: no-repeat; background-size: 350px 150px;";
?>

<div class="wcacn-cp-pdetails clearfix" style="<?php echo esc_attr( $stlye ); ?>">
	<div data-wcacn_cp_key="<?php echo $cart_item_key; // phpcs:ignore ?>">
		<div style="text-align:center;" class="wcacn_layout2_content wcacn-cp-ptitle">
			<a href="<?php echo $product_permalink; // phpcs:ignore ?>"><?php echo $product_name; // phpcs:ignore ?></a>
			<br>
			<?php echo 'Price: ' . $product_price; // phpcs:ignore ?>
		</div>
	</div>
	<?php do_action( 'wcacn_cp_before_btns' ); ?>	
	<div class="wcacn-cp-btns">
		<a class="button wcacn-cp-btn-vc wcacn-btn" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View Cart', 'wc-ac-popup' ); ?></a>
	</div>
	<?php do_action( 'wcacn_cp_after_btns' ); ?>
</div>
