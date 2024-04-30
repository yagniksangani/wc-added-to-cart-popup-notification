<?php
/**
 * Popup template.
 *
 * @package WC_AC_POPUP_NOTIFICATION
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

?>

<div class="wcacn-cp-opac"></div>
<div class="wcacn-cp-modal">
	<div class="wcacn-cp-container <?php echo esc_attr( $position ); ?>">
		<div class="wcacn-cp-outer">
			<div class="wcacn-cp-cont-opac"></div>
			<span class="wcacn-cp-preloader wcacn-cp-icon-spinner"></span>
		</div>
		<span class="wcacn-cp-close wcacn-cp-icon-cross"></span>
		<div class="wcacn-cp-content"></div>
	</div>
</div>
