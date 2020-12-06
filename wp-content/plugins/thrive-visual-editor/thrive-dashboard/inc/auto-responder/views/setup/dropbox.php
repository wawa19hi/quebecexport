<h2 class="tvd-card-title"><?php echo $this->getTitle(); ?></h2>
<div class="tvd-row">
	<form class="tvd-col tvd-s12">
		<input type="hidden" name="api" value="<?php echo $this->getKey(); ?>"/>
		<div class="tvd-input-field">
			<input id="tvd-db-api-client-id" type="text" name="connection[client_id]"
					value="<?php echo $this->param( 'client_id' ) ?>">
			<label for="tvd-db-api-client-id"><?php echo __( 'App key', TVE_DASH_TRANSLATE_DOMAIN ); ?></label>
		</div>
		<div class="tvd-input-field">
			<input id="tvd-db-api-client-secret" type="text" name="connection[client_secret]"
					value="<?php echo $this->param( 'client_secret' ); ?>">
			<label for="tvd-db-api-client-secret"><?php echo __( 'App secret', TVE_DASH_TRANSLATE_DOMAIN ); ?></label>
		</div>
		<?php $this->display_video_link(); ?>
	</form>
</div>
<div class="tvd-card-action">
	<div class="tvd-row tvd-no-margin">
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-cancel tvd-btn-flat tvd-btn-flat-secondary tvd-btn-flat-dark tvd-full-btn tvd-waves-effect"><?php echo __( 'Cancel', TVE_DASH_TRANSLATE_DOMAIN ); ?></a>
		</div>
		<div class="tvd-col tvd-s12 tvd-m6">
			<a class="tvd-api-redirect tvd-waves-effect tvd-waves-light tvd-btn tvd-btn-green tvd-full-btn"><?php echo __( 'Connect', TVE_DASH_TRANSLATE_DOMAIN ); ?></a>
		</div>
	</div>
</div>

