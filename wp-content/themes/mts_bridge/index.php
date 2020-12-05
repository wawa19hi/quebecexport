<?php
/**
 * The main template file.
 *
 * Used to display the homepage when home.php doesn't exist.
 */
?>
<?php $mts_options = get_option(MTS_THEME_NAME); ?>
<?php get_header(); ?>
<div id="page">
	<?php if ( !is_paged() && $mts_options['mts_featured_post'] == '1' ) { //Featured Area Section
		get_template_part('home/section', 'featured-area' );
	} ?>
	<?php if ( !is_paged() && $mts_options['mts_show_secondary_nav'] == '1' ) { ?>
		<div id="primary-navigation" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
		<?php if ( $mts_options['mts_show_primary_nav'] !== '1' ) {?><a href="#" id="pull" class="toggle-mobile-menu"><?php _e('Menu', 'bridge' ); ?></a><?php } ?>
			<nav class="navigation clearfix<?php if ( $mts_options['mts_show_primary_nav'] !== '1' ) echo ' mobile-menu-wrapper'; ?>">
				<?php if ( has_nav_menu( 'secondary-menu' ) ) { ?>
					<?php wp_nav_menu( array( 'theme_location' => 'secondary-menu', 'menu_class' => 'menu clearfix', 'container' => '', 'walker' => new mts_menu_walker ) ); ?>
				<?php } else { ?>
					<ul class="menu clearfix">
						<?php wp_list_pages('title_li='); ?>
					</ul>
				<?php } ?>
			</nav>
		</div>
	<?php } ?>
	<div class="article">
		<div id="content_box">
			<?php if ( !is_paged() ) { ?>

				<?php $featured_categories = array();
				if ( !empty( $mts_options['mts_featured_categories'] ) ) {
					foreach ( $mts_options['mts_featured_categories'] as $section ) {
						$category_id = $section['mts_featured_category'];
						$featured_categories[] = $category_id;
						$posts_num = $section['mts_featured_category_postsnum'];
						if ( 'latest' == $category_id ) { ?>
							<?php $j = 1; if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
								<?php mts_archive_post($j); ?>
							<?php ++$j; endwhile; endif; ?>
							
							<?php if ( $j !== 0 ) { // No pagination if there is no posts ?>
								<?php mts_pagination(); ?>
							<?php } ?>
							
						<?php } else { // if $category_id != 'latest': ?>
							<div class="category-section">
								<h3 class="featured-category-title"><a href="<?php echo esc_url( get_category_link( $category_id ) ); ?>" title="<?php echo esc_attr( get_cat_name( $category_id ) ); ?>"><?php echo esc_html( get_cat_name( $category_id ) ); ?></a></h3>
								<?php $j = 1; $cat_query = new WP_Query('cat='.$category_id.'&posts_per_page='.$posts_num);
								if ( $cat_query->have_posts() ) : while ( $cat_query->have_posts() ) : $cat_query->the_post(); ?>
									<?php mts_archive_post($j); ?>
								<?php ++$j; endwhile; endif; wp_reset_postdata(); ?>
							</div>
						<?php }
					}
				} ?>

			<?php } else { //Paged ?>

				<?php $j = 1; if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
					<?php mts_archive_post($j); ?>
				<?php ++$j; endwhile; endif; ?>

				<?php if ( $j !== 0 ) { // No pagination if there is no posts ?>
					<?php mts_pagination(); ?>
				<?php } ?>

			<?php } ?>
		</div>
	</div>
<?php get_footer(); ?>