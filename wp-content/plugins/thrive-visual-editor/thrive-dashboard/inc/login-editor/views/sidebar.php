<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<a href="javascript:void(0)" class="sidebar-item click" data-fn="resetDesign" data-position="left" data-tooltip="<?php echo __( 'Reset design', TVE_DASH_TRANSLATE_DOMAIN ); ?>">
	<?php tcb_icon( 'template-reset', false, 'sidebar', '' ); ?>
</a>
<a href="javascript:void(0)" data-position="left" class="sidebar-item click" data-fn="loginCloudTemplates" data-tooltip="<?php echo __( 'Cloud Templates', TVE_DASH_TRANSLATE_DOMAIN ); ?>">
	<?php tcb_icon( 'cloud-download-light' ); ?>
</a>
