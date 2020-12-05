<?php $mts_options = get_option(MTS_THEME_NAME); ?>
<div class="featuredBox">
    <?php $i = 1;
    // prevent implode error
    if ( empty( $mts_options['mts_featured_post_cat'] ) || !is_array( $mts_options['mts_featured_post_cat'] ) ) {
        $mts_options['mts_featured_post_cat'] = array('0');
    }
    $slider_cat = implode( ",", $mts_options['mts_featured_post_cat'] );
    $slider_query = new WP_Query('cat='.$slider_cat.'&posts_per_page=4&ignore_sticky_posts=1'); 
    while ($slider_query->have_posts()) : $slider_query->the_post();
        if($i == 1){ ?> 
            <div class="firstpost excerpt">
                <a href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" id="first-thumbnail" class="featuredPost">
                    <div class="featured-thumbnail"><?php the_post_thumbnail('bridge-bigfeatured',array('title' => '')); ?></div> 
                </a>
                <header>                        
                    <h2 class="title front-view-title"><a href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_title(); ?></a></h2>
                    <div class="theauthor"><?php _e( 'By: ', 'bridge' ); ?><span><?php the_author_posts_link(); ?></span></div>
                </header>
            </div><!--.post excerpt-->
        <?php } elseif($i >= 2) { ?>
            <div class="featured-Post <?php echo 'post-' . $i; ?>">
                <a href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" class="featuredPost">
                    <div class="featured-thumbnail"><?php the_post_thumbnail('bridge-smallfeatured',array('title' => '')); ?></div> 
                </a>
                <header>
                    <h2 class="title front-view-title"><a href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_title(); ?></a></h2>
                </header>
                <?php $category = get_the_category();
                    $name = $category[0]->cat_name;
                    $cat_id = get_cat_ID( $name );
                    $link = get_category_link( $cat_id );
                    echo '<div class="featuredbox-category cat-' . $cat_id . '"><a href="'. esc_url( $link ) .'" style="color:">'. $name .'</a></div>'; 
                ?>
            </div><!--.post excerpt-->
        <?php } 
    $i++; endwhile; wp_reset_query(); ?> 
</div>