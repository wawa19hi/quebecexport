<?php
 
// Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
die ('Please do not load this page directly. Thanks!');
 
if ( post_password_required() ) { ?>
<p class="nocomments"><?php _e('This post is password protected. Enter the password to view comments.','bridge'); ?></p>
<?php return; } ?>
<?php $mts_options = get_option(MTS_THEME_NAME); ?>
<!-- You can start editing here. -->

<?php if (comments_open()) : ?>
<?php
if ( !(isset( $mts_options['mts_comments'] ) && is_array( $mts_options['mts_comments'] ) && array_key_exists( 'enabled', $mts_options['mts_comments'] ) ) ) {
}
?>
<div id="comments" class="clearfix">
	<div class="cd-tabs">
		<nav>
			<?php if (!empty($mts_options['mts_comments']['enabled'])) { ?>
			<ul class="cd-tabs-navigation">
				<?php $i = 0; foreach ($mts_options['mts_comments']['enabled'] as $key => $value) { $i++;
					$class = '';
					if ($i == 1) {
						$class = ' class="selected"';
						$selected = $key;
					}
					switch ($key) {
						case 'comments': ?>
							<li><h4><a data-content="comments"<?php echo $class; ?> href="#0"><?php comments_number(__('No comments yet','bridge'), __('One Response','bridge'),  __('Comments (%)','bridge') );?></h4></a></li>
						<?php 
						break;

						case 'fb_comments': ?>
							<li><h4><a data-content="fbcomments"<?php echo $class; ?> href="#0"><?php _e( 'Facebook Comments','bridge'); ?></a></h4></li>
						<?php 
						break;
					}
				} ?>				
			</ul> <!-- cd-tabs-navigation -->
			<?php } ?>
		</nav>

		<ul class="cd-tabs-content">
			<?php if (!empty($mts_options['mts_comments']['enabled']['comments'])) { ?>
			<li data-content="comments" <?php echo $selected == 'comments' ? 'class="selected"' : ''; ?>>
				<?php if ( have_comments() ) : ?>
					<div id="comments">
						<ol class="commentlist">
							<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) { // are there comments to navigate through ?>
								<div class="navigation">
									<div class="alignleft"><?php previous_comments_link() ?></div>
									<div class="alignright"><?php next_comments_link() ?></div>
								</div>
							<?php }
							
							wp_list_comments('callback=mts_comments');
							
							if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) { // are there comments to navigate through ?>
								<div class="navigation">
									<div class="alignleft"><?php previous_comments_link() ?></div>
									<div class="alignright"><?php next_comments_link() ?></div>
								</div>
							<?php } ?>
						</ol>
					</div>
				<?php endif; ?>

				<?php if ( comments_open() ) : ?>
					<div id="commentsAdd">
						<div id="respond" class="box m-t-6">
							<?php global $aria_req;
							$consent  = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';
							$comments_args = array(
									'title_reply'=>'<h4><span>'.__('Leave a Comment', 'bridge' ).'</span></h4>',
									'comment_notes_before' => '',
									'comment_notes_after' => '',
									'label_submit' => __( 'Submit Comment', 'bridge' ),
									'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="'.__('Comment Text*', 'bridge' ).'"></textarea></p>',
									'fields' => apply_filters( 'comment_form_default_fields',
										array(
											'author' => '<p class="comment-form-author">'
											.( $req ? '' : '' ).'<input id="author" name="author" type="text" placeholder="'.__('Name*', 'bridge' ).'" value="'.esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
											'email' => '<p class="comment-form-email">'
											.($req ? '' : '' ) . '<input id="email" name="email" type="text" placeholder="'.__('Email*', 'bridge' ).'" value="' . esc_attr(  $commenter['comment_author_email'] ).'" size="30"'.$aria_req.' /></p>',
											'url' => '<p class="comment-form-url"><input id="url" name="url" type="text" placeholder="'.__('Website', 'bridge' ).'" value="' . esc_url( $commenter['comment_author_url'] ) . '" size="30" /></p>',
										) 
									)
								); 
							comment_form($comments_args); ?>
						</div>
					</div>
				<?php endif; // if you delete this the sky will fall on your head ?>
			</li>
			<?php } ?>

			<?php if (!empty($mts_options['mts_comments']['enabled']['fb_comments'])) { ?>
			<li data-content="fbcomments" <?php echo $selected == 'fb_comments' ? 'class="selected"' : ''; ?>>
				<?php if ( post_password_required() ) : ?>
			    	    <p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'bridge' ); ?></p>
			        </div>
			        <?php return; ?>
			    <?php endif; ?>
			 
			    <?php if ( comments_open() ) : ?>
			        <div class="fb-comments" data-href="<?php the_permalink(); ?>" data-numposts="5" data-colorscheme="light" data-width="100%"></div>
			    <?php endif; ?>

			    <?php if ( ! comments_open() ) : ?>
					<p class="nocomments"></p>
			    <?php endif; ?>
			</li>
			<?php } ?>
		</ul> <!-- cd-tabs-content -->
	</div> <!-- cd-tabs -->
</div> <!-- cd-tabs -->
<?php endif; ?>