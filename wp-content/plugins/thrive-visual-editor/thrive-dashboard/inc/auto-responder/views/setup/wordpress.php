<h2 class="tvd-card-title"><?php echo $this->getTitle() ?></h2>
<div class="tvd-row tvd-center tvd-wordpress-helper">
	<# if (item.registration_disabled) { #>
	<?php esc_html_e( 'Enabling this connection will allow Thrive registration forms to accept new signups.', TVE_DASH_TRANSLATE_DOMAIN ); ?>
	<# } else { #>
	<?php esc_html_e( 'Disabling this connection will prevent Thrive registration forms from accepting new signups.', TVE_DASH_TRANSLATE_DOMAIN ); ?>
	<# } #>
</div>
<?php $this->display_video_link(); ?>
<br>
<br>
<form class="tvd-hide">
	<input type="hidden" name="api" value="<?php echo $this->getKey(); ?>">
</form>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-waves-effect tvd-btn-compact tvd-waves-light tvd-btn tvd-btn-<#=item.registration_disabled ? 'green' : 'red'#> tvd-full-btn"
			   href="javascript:void(0)" data-custom-action="wordpressToggleState">
				<# if (item.registration_disabled) { #>
				<?php esc_html_e( 'YES, ENABLE', TVE_DASH_TRANSLATE_DOMAIN ); ?>
				<# } else { #>
				<?php esc_html_e( 'YES, DISABLE', TVE_DASH_TRANSLATE_DOMAIN ); ?>
				<# } #>
			</a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-compact tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect">
				<# if (item.registration_disabled) { #>
				<?php esc_html_e( 'CANCEL', TVE_DASH_TRANSLATE_DOMAIN ); ?>
				<# } else { #>
				<?php esc_html_e( 'NO, KEEP IT', TVE_DASH_TRANSLATE_DOMAIN ); ?>
				<# } #>
			</a>
		</div>
	</div>
</div>
