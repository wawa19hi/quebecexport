<h2 class="tcb-modal-title"><?php echo __( 'Set error messages', 'thrive-cb' ); ?></h2>

<div class="tcb-fields-error control-grid wrap"></div>

<div class="control-grid">
	<button type="button" class="tve-button text-only click" data-fn="restore_defaults">
		<?php tcb_icon( 'close' ) ?>
		<?php echo __( 'Restore errors to default', 'thrive-cb' ) ?>
	</button>
</div>

<div class="tcb-gray" id="tcb-signup-error-wrapper" style="display: none">
	<div class="control-grid">
		<label class="tcb-checkbox pb-10">
			<input type="checkbox" id="tcb-sign-up-error-enabled">
			<span><?php echo __( "Add 'Signup failed' error message", 'thrive-cb' ) ?></span>
		</label>
	</div>
	<div class="control-grid">
		<p><?php echo __( "This error message is shown in the rare case that the signup fails. This can happen when your connected email marketing service can't be reached.", 'thrive-cb' ) ?></p>
	</div>
	<div class="control-grid" id="tcb-lg-signup-error-editor" style="display: none;">
		<div>
			<?php wp_editor( '', 'tcb_lg_error', array( 'quicktags' => false, 'media_buttons' => false ) ); ?>
		</div>
	</div>
</div>

<div class="tcb-modal-footer control-grid">
	<button type="button" class="tcb-left tve-button text-only tcb-modal-cancel">
		<?php echo __( 'Cancel', 'thrive-cb' ) ?>
	</button>
	<button type="button" class="tcb-right tve-button medium tcb-modal-save">
		<?php echo __( 'Save', 'thrive-cb' ) ?>
	</button>
</div>
