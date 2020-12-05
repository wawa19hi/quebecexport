<?php
/**
 * The template for displaying the header.
 *
 * Displays everything from the doctype declaration down to the navigation.
 */
?>
<!DOCTYPE html>
<?php $mts_options = get_option(MTS_THEME_NAME); ?>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
	<!--[if IE ]>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<![endif]-->
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<?php mts_meta(); ?>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>
</head>
<body id="blog" <?php body_class('main'); ?>>
	<div class="main-container">
		<header id="site-header" role="banner" itemscope itemtype="http://schema.org/WPHeader">
		<?php if ( isset( $mts_options['mts_header_layout'] ) && is_array( $mts_options['mts_header_layout'] ) && array_key_exists( 'enabled', $mts_options['mts_header_layout'] ) ) {
            $header_parts = $mts_options['mts_header_layout']['enabled'];
		} else {
		    $header_parts = array( 'top-header' => 'top-header', 'bottom-header' => 'bottom-header' );
		}
		foreach( $header_parts as $part => $label ) {
		    switch ($part) {
		        case 'top-header': ?>
		        <div id="header">
					<div class="container clearfix">
						<div class="logo-wrap">
							<?php if ( $mts_options['mts_logo'] != '' && $mts_logo = wp_get_attachment_image_src( $mts_options['mts_logo'], 'full' ) ) { ?>
								<?php if ( is_front_page() || is_home() || is_404() ) { ?>
									<h1 id="logo" class="image-logo" itemprop="headline">
										<a href="<?php echo esc_url( home_url() ); ?>">
											<img src="<?php echo esc_url( $mts_logo[0] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="<?php echo esc_attr( $mts_logo[1] ); ?>" height="<?php echo esc_attr( $mts_logo[2] ); ?>">
										</a>
									</h1><!-- END #logo -->
								<?php } else { ?>
									<h2 id="logo" class="image-logo" itemprop="headline">
										<a href="<?php echo esc_url( home_url() ); ?>">
											<img src="<?php echo esc_url( $mts_logo[0] ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="<?php echo esc_attr( $mts_logo[1] ); ?>" height="<?php echo esc_attr( $mts_logo[2] ); ?>">
										</a>
									</h2><!-- END #logo -->
								<?php } ?>

							<?php } else { ?>

								<?php if ( is_front_page() || is_home() || is_404() ) { ?>
									<h1 id="logo" class="text-logo" itemprop="headline">
										<a href="<?php echo esc_url( home_url() ); ?>"><?php bloginfo( 'name' ); ?></a>
									</h1><!-- END #logo -->
								<?php } else { ?>
									<h2 id="logo" class="text-logo" itemprop="headline">
										<a href="<?php echo esc_url( home_url() ); ?>"><?php bloginfo( 'name' ); ?></a>
									</h2><!-- END #logo -->
								<?php } ?>
								<span class="site-description" itemprop="description">
									<?php bloginfo( 'description' ); ?>
								</span>
							<?php } ?>
						</div>
						<?php if ( !empty($mts_options['mts_header_social']) && is_array($mts_options['mts_header_social']) && !empty($mts_options['mts_social_icon_head'])) { ?>
							<div class="header-social">
								<?php foreach( $mts_options['mts_header_social'] as $header_icons ) : ?>
									<?php if( ! empty( $header_icons['mts_header_icon'] ) && isset( $header_icons['mts_header_icon'] ) && ! empty( $header_icons['mts_header_icon_link'] )) : ?>
										<a href="<?php print $header_icons['mts_header_icon_link'] ?>" class="header-<?php print $header_icons['mts_header_icon'] ?>" target="_blank"><span class="fa fa-<?php print $header_icons['mts_header_icon'] ?>"></span></a>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						<?php } ?>
					</div><!--#header-->
				</div>
		    	<?php break;
			    case 'bottom-header':
					if( $mts_options['mts_sticky_nav'] == '1' ) { ?>
					<div id="catcher" class="clear" ></div>
					<div class="sticky-navigation navigation-header" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
					<?php } else { ?>
						<div class="navigation-header">
					<?php } ?>
					<div class="container clearfix">
						<?php if ( $mts_options['mts_show_primary_nav'] == '1' ) { ?>
							<div id="secondary-navigation" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
							<a href="#" id="pull" class="toggle-mobile-menu"><?php _e('Menu', 'bridge' ); ?></a>
							<?php if ( has_nav_menu( 'mobile' ) ) { ?>
								<nav class="navigation clearfix">
									<?php if ( has_nav_menu( 'primary-menu' ) ) { ?>
										<?php wp_nav_menu( array( 'theme_location' => 'primary-menu', 'menu_class' => 'menu clearfix', 'container' => '', 'walker' => new mts_menu_walker ) ); ?>
									<?php } else { ?>
										<ul class="menu clearfix">
											<?php wp_list_categories('title_li='); ?>
										</ul>
									<?php } ?>
								</nav>
								<nav class="navigation mobile-only clearfix mobile-menu-wrapper">
									<?php wp_nav_menu( array( 'theme_location' => 'mobile', 'menu_class' => 'menu clearfix', 'container' => '', 'walker' => new mts_menu_walker ) ); ?>
								</nav>
							<?php } else { ?>
								<nav class="navigation clearfix mobile-menu-wrapper">
									<?php if ( has_nav_menu( 'primary-menu' ) ) { ?>
										<?php wp_nav_menu( array( 'theme_location' => 'primary-menu', 'menu_class' => 'menu clearfix', 'container' => '', 'walker' => new mts_menu_walker ) ); ?>
									<?php } else { ?>
										<ul class="menu clearfix">
											<?php wp_list_categories('title_li='); ?>
										</ul>
									<?php } ?>
								</nav>
							<?php } ?>
							</div>
						<?php } ?>
						<?php if ( !empty($mts_options['mts_header_search']) ) { ?>
							<div id="search-6" class="widget widget_search">
								<?php get_search_form(); ?>
							</div><!-- END #search-6 -->
						<?php } ?>
					</div>
				</div><!--.container-->
			    <?php break;
			    }
		} ?>
		</header>

		<?php if ( is_active_sidebar( 'widget-header' ) ) { ?>
			<div class="ad-header">
				<div class="container">
					<?php dynamic_sidebar('widget-header'); ?>
				</div>	
			</div>
		<?php } ?>
