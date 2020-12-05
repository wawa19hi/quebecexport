<?php
/**
 * The template for displaying all single posts.
 */
$mts_options = get_option(MTS_THEME_NAME);

get_header(); ?>

<div id="page" class="<?php mts_single_page_class(); ?>">

	<?php $check_sidebar = mts_custom_sidebar(); ?>

		<div id="content_box" >
			<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
				<div id="post-<?php the_ID(); ?>" <?php post_class('g post'); ?>>
					<div class="single-post-wrap">
						<article class="<?php mts_article_class(); ?>">
							<div class="single_post">
								<?php if ($mts_options['mts_breadcrumb'] == '1') {
									mts_the_breadcrumb();
								} ?>
								<header>
									<?php if ( $mts_options['mts_single_post_category'] == '1' ) { ?>
					                    <div class="single-post-category">
						                    <?php $category = get_the_category();
											    $name = $category[0]->cat_name;
										        $cat_id = get_cat_ID( $name );
										        $link = get_category_link( $cat_id );
										        echo '<div class="thecategory cat-' . $cat_id . '"><a href="'. esc_url( $link ) .'">'. $name .'<span><i class="fa fa-file-image-o"></i></span></a></div>';
										    ?>
									    </div>
									<?php } ?>
									<h1 class="title single-title entry-title"><?php the_title(); ?></h1>
									<div class="post-info">
								   		<?php
								   			$author = isset($mts_options['mts_single_meta_info_enable']['author']);
								   			$time = isset($mts_options['mts_single_meta_info_enable']['time']);
								   			$comment = isset($mts_options['mts_single_meta_info_enable']['comment']);
								   			$category = isset($mts_options['mts_single_meta_info_enable']['category']);
								   		?>
								   		<?php if( $author == '1' || $category == '1' || $time == '1' ) { ?>
											<div class="post-info-left">
												<?php if ( $author == '1' ) : ?>
													<div class="theauthorimage"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="fn"><?php echo get_avatar( get_the_author_meta('email'), 40 ); ?></a></div>
													<div class="theauthor"><?php _e( 'By: ', 'bridge' ); ?><span><?php the_author_posts_link(); ?></span></div>
												<?php endif; ?>
												<?php if( $category == '1' ) { ?>
						                   			<div class="thecategories"><?php _e( 'In: ', 'bridge' ); ?><?php mts_the_category(', '); ?></div>
												<?php } ?>
												<?php if ( $time == '1' ) : ?>
													<div class="thetime date updated"><span><?php the_time( get_option( 'date_format' ) ); ?></span></div>
												<?php endif; ?>
											</div>
										<?php } ?>
										<?php if( !empty($mts_options['mts_single_like_dislike']) || $comment == '1' ) { ?>
											<div class="post-info-right">
												<?php if ( !empty($mts_options['mts_single_like_dislike']) ) : ?>
													<?php mts_like_dislike(); ?>
												<?php endif; ?>
												<?php if ( $comment == '1' ) : ?>
													<span class="thecomment"><i class="fa fa-comment"></i> <span itemprop="interactionCount"><?php comments_number( '0', '1', '%' );?></span></span>
												<?php endif; ?>
											</div>
										<?php } ?>
									</div>
								</header><!--.headline_area-->
								<?php // Top Social Share ?>
									<?php if (isset($mts_options['mts_social_button_position']) && ($mts_options['mts_social_button_position'] == 'top' || $mts_options['mts_social_button_position'] == 'top_and_bottom')) mts_social_buttons(); ?>
								<?php $header_animation = mts_get_post_header_effect(); ?>
								<?php if ( 'parallax' === $header_animation ) {?>
									<?php if (mts_get_thumbnail_url()) : ?>
										<div id="parallax" <?php echo 'style="background-image: url('.mts_get_thumbnail_url().');"'; ?>></div>
									<?php endif; ?>
								<?php } else if ( 'zoomout' === $header_animation ) {?>
									 <?php if (mts_get_thumbnail_url()) : ?>
										<div id="zoom-out-effect"><div id="zoom-out-bg" <?php echo 'style="background-image: url('.mts_get_thumbnail_url().');"'; ?>></div></div>
									<?php endif; ?>
								<?php } else if ( has_post_thumbnail() && $mts_options['mts_single_featured_image'] == 1 ) : ?>
									<div class="featured-thumbnail">
										<?php the_post_thumbnail('bridge-featuredfull',array('title' => '')); ?>
									</div>
								<?php endif; ?>
								<div class="post-single-content box mark-links entry-content">
									<?php // Top Ad Code ?>
									<?php if ($mts_options['mts_posttop_adcode'] != '') { ?>
										<?php $toptime = $mts_options['mts_posttop_adcode_time']; if (strcmp( date("Y-m-d", strtotime( "-$toptime day")), get_the_time("Y-m-d") ) >= 0) { ?>
											<div class="topad">
												<?php echo do_shortcode($mts_options['mts_posttop_adcode']); ?>
											</div>
										<?php } ?>
									<?php } ?>

									<?php // Content ?>
									<div class="thecontent">
										<?php the_content(); ?>
									</div>

									<?php // Single Pagination ?>
									<?php wp_link_pages(array('before' => '<div class="pagination">', 'after' => '</div>', 'link_before'  => '<span class="current"><span class="currenttext">', 'link_after' => '</span></span>', 'next_or_number' => 'next_and_number', 'nextpagelink' => __('Next', 'bridge' ), 'previouspagelink' => __('Previous', 'bridge' ), 'pagelink' => '%','echo' => 1 )); ?>

									<?php // Bottom Ad Code ?>
									<?php if ($mts_options['mts_postend_adcode'] != '') { ?>
										<?php $endtime = $mts_options['mts_postend_adcode_time']; if (strcmp( date("Y-m-d", strtotime( "-$endtime day")), get_the_time("Y-m-d") ) >= 0) { ?>
											<div class="bottomad">
												<?php echo do_shortcode($mts_options['mts_postend_adcode']); ?>
											</div>
										<?php } ?>
									<?php } ?>

									<?php // Bottom Social Share ?>
									<?php if (isset($mts_options['mts_social_button_position']) && ($mts_options['mts_social_button_position'] !== 'top'|| $mts_options['mts_social_button_position'] == 'top_and_bottom')) mts_social_buttons(); ?>
								</div><!--.post-single-content-->
							</div>
						</article><!--.single_post-->
						<?php get_sidebar(); ?>
					</div>

					<!-- Single post parts ordering -->
					<?php if ( isset( $mts_options['mts_single_post_layout'] ) && is_array( $mts_options['mts_single_post_layout'] ) && array_key_exists( 'enabled', $mts_options['mts_single_post_layout'] ) ) {
						$single_post_parts = $mts_options['mts_single_post_layout']['enabled'];
					} else {
						$single_post_parts = array( 'related' => 'related', 'author' => 'author' );
					}
					foreach( $single_post_parts as $part => $label ) {
						switch ($part) {

							case 'tags':
								?>
								<?php mts_the_tags('<div class="tags"><span class="tagtext"></span>') ?>
								<?php
							break;

							case 'related':
								mts_related_posts();
							break;

							case 'author':
								?>
								<div class="postauthor">
									<h4><?php _e('About The Author', 'bridge' ); ?></h4>
									<?php if(function_exists('get_avatar')) { echo get_avatar( get_the_author_meta('email'), '80' );  } ?>
									<h5 class="vcard author"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="fn"><?php the_author_meta( 'display_name' ); ?></a></h5>
									<p><?php the_author_meta('description') ?></p>
								</div>
								<?php
							break;
						}
					} ?>
				</div><!--.g post-->
				<?php comments_template( '', true ); ?>
			<?php endwhile; /* end loop */ ?>
		</div>
<?php get_footer(); ?>
