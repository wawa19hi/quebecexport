<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-lead_generation_radio-component" class="tve-component" data-view="LeadGenerationRadio">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>

	<div class="dropdown-content">
		<div class="tve-control" data-view="ShowLabel"></div>
		<div class="tve-control" data-key="Required" data-view="Checkbox"></div>
		<div class="tve-control hide-mobile hide-tablet" data-view="ColumnNumber"></div>
		<div class="tve-control" data-view="VerticalSpace"></div>
		<div class="tve-control hide-tablet hide-mobile" data-view="HorizontalSpace"></div>
		<hr>
		<div class="control-grid">
				<span class="label">
					<?php echo __( 'Options', 'thrive-cb' ); ?>
				</span>
			<button class="tve-button blue click" data-fn="addOption"><?php echo __( 'Add', 'thrive-cb' ); ?></button>
		</div>
		<div id="option-list" class="no-space"></div>
		<div class="tve-control" data-view="AnswerTag"></div>
	</div>
</div>
