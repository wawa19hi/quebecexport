<?php

define( 'RMU_PLUGIN_FILE', 'seo-by-rank-math/rank-math.php' );
define( 'RMU_PLUGIN_URL', 'https://downloads.wordpress.org/plugin/seo-by-rank-math.latest-stable.zip' );
define( 'RMU_PLUGIN_SLUG', 'seo-by-rank-math' );

$active_plugins = get_option( 'active_plugins' );
$rm_installed   = in_array( RMU_PLUGIN_FILE, $active_plugins, true );
define( 'RMU_INSTALLED', $rm_installed );

/**
 * Suggest Rank Math SEO in notices.
 */
class MTS_RMU {

    /**
     * Instance.
     *
     * @var [type]
     */
    private static $instance;

    /**
     * Configuration.
     *
     * @var array
     */
    public $config = array();

    /**
     * Plugin.
     *
     * @var string
     */
    public $plugin;

    /**
     * Constructor
     *
     * @param array $config Configuration.
     */
    private function __construct( $config = array() ) {
        $config_defaults = array(

            // Auto install RM on theme/plugin activation.
            'auto_install'            => true,

            // Auto activate RM on theme/plugin activation.
            'auto_activate'           => true,

            // Don't show wizard when RM is auto-activated.
            'suppress_wizard'         => true,

            'link_label_install'      => __( 'Try it for FREE!', 'bridge' ),
            'link_label_activate'     => __( 'Click here to activate it.', 'bridge' ),

            // Add tab in MTS Theme Options.
            'add_theme_options_tab'   => true,
            'theme_options_notice'    => '<span style="display:block;line-height:1.8;margin-bottom:20px;padding-left:3%;margin-top:-40px;">' . sprintf( __( '%1$s is a revolutionary SEO product that combines the features of many SEO tools. Its features give you the power of an entire SEO team with just a few clicks.', 'bridge' ), '<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank">Rank Math SEO</a>' ) . ' @CTA' . '</span>',

            'show_metabox_notice'     => true,

            'add_dashboard_widget'    => false,

            /* Translators: %s is CTA, e.g. "Try it now!" */
            'metabox_notice_install'  => sprintf( __( 'The new %1$s plugin will help you rank better in the search results.', 'bridge' ), '<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank">Rank Math SEO</a>' ) . ' @CTA',

            /* Translators: %s is CTA, e.g. "Try it now!" */
            'metabox_notice_activate' => sprintf( __( 'The %1$s plugin is installed but not activated.', 'bridge' ), '<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank">Rank Math SEO</a>' ) . ' @CTA',

            // Add a message in Yoast & AIO metaboxes.
            'show_competitor_notice'  => true,
            'competitor_notice'       =>
                '<span class="dashicons dashicons-lightbulb"></span>
                <span class="mts-ctad-question">' . __( 'Did you know?', 'bridge' ) . ' </span>
                <span class="mts-ctad">' . sprintf( __( 'The new %1$s plugin can make your site load faster, offers more features, and can import your current SEO settings with one click.', 'bridge' ), '<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank">Rank Math SEO</a>' ) . '</span>' . '@CTA',
        );

        $this->config = $config_defaults;

        // Apply constructor config.
        $this->config( $config );

        $this->add_hooks();
    }

    /**
     * Add Hooks.
     */
    public function add_hooks() {
        // This needs to run even if RM is installed already.
        // We just suppress the wizard whenever current theme/plugin is activated.
        add_action( 'after_switch_theme', array( $this, 'suppress_redirect' ), 1 );
        $plugin_file = $this->get_plugin_file();
        if ( $plugin_file ) {
            register_activation_hook( $plugin_file, array( $this, 'suppress_redirect' ) );
        }

        // Also, we redirect to RM's Setup Wizard when it is installed from the Recommended Plugins page.
        add_action( 'rank_math_activate', array( $this, 'rm_install_redirect' ) );

        // The rest doesn't need to run when RM is installed already
        // Or if user doesn't have the capability to install plugins.
        if ( RMU_INSTALLED || ! current_user_can( 'install_plugins' ) ) {
                return;
        }
        add_action( 'wp_ajax_rmu_dismiss', array( $this, 'ajax_dismiss_notice' ) );

        if ( $this->get_setting( 'show_competitor_notice' ) ) {
            $active_plugins = get_option( 'active_plugins' );
            if ( in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true ) ) {
                // Add message in Yoast meta box.
                add_action( 'admin_print_footer_scripts-post-new.php', array( $this, 'inject_yoast_notice' ) );
                add_action( 'admin_print_footer_scripts-post.php', array( $this, 'inject_yoast_notice' ) );
            } elseif ( in_array( 'all-in-one-seo-pack/all_in_one_seo_pack.php', $active_plugins, true ) ) {
                // Add message in AIOSEO meta box.
                add_action( 'admin_print_footer_scripts-post-new.php', array( $this, 'inject_aioseo_notice' ) );
                add_action( 'admin_print_footer_scripts-post.php', array( $this, 'inject_aioseo_notice' ) );
            }
        }

        if ( $this->get_setting( 'show_metabox_notice' ) ) {
            $active_plugins = get_option( 'active_plugins' );
            if ( ! in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true ) && ! in_array( 'all-in-one-seo-pack/all_in_one_seo_pack.php', $active_plugins, true ) ) {
                // Add dummy SEO meta box with link to install/activate RM.
                add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            }
        }

        if ( $this->get_setting( 'add_theme_options_tab' ) ) {
            // Add new tab in Theme Options.
            add_filter( 'nhp-opts-sections', array( $this, 'add_theme_options_seo_tab' ) );
        }

        if ( $this->get_setting( 'auto_install' ) ) {
            if ( ! get_option( 'rm_autoinstall', false ) ) {
                add_action( 'after_setup_theme', array( $this, 'autoinstall' ) );
            }
        }

        if ( $this->get_setting( 'add_dashboard_widget' ) ) {
            // Add new tab in Theme Options.
            add_filter( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ), 99 );
        }
    }

    /**
     * Dashboard Widget.
     */
    public function add_dashboard_widget() {
        if ( $this->is_dismissed( 'dashboard_panel' ) ) {
            return;
        }

        wp_add_dashboard_widget( 'rmu_dashboard_widget', __( 'Rank Math SEO' ), array( $this, 'dashboard_widget_output' ) );
    }

    /**
     * Dashboard Widget Output.
     *
     * @param  array $post          Post.
     * @param  array $callback_args Callback Arguments.
     */
    public function dashboard_widget_output( $post, $callback_args ) {
        $action          = '';
        $url             = '';
        $classes         = '';
        $classic_action  = '';
        $classic_url     = '';
        $classic_classes = '';
        ?>
        <div class="rmu-dashboard-panel">
            <a class="rmu-dashboard-panel-close" id="rmu-dashboard-dismiss" href="<?php echo esc_url( add_query_arg( 'rmu-dashboard', '1' ) ); ?>"><?php echo esc_attr( 'Dismiss', 'bridge' ); ?></a>
            <div class="rmu-dashboard-panel-content">
                <p>
                    <?php
                    $plugins      = array_keys( get_plugins() );
                    $rm_installed = in_array( RMU_PLUGIN_FILE, $plugins, true );

                    if ( $rm_installed ) {
                        echo strtr( $this->get_setting( 'metabox_notice_activate' ), array( '@CTA' => $this->get_activate_link() ) );
                    } else {
                        echo strtr( $this->get_setting( 'metabox_notice_install' ), array( '@CTA' => $this->get_install_link() ) );
                    }
                    ?>
                </p>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#rmu_dashboard_widget').insertAfter('.wrap > h1');
                $( '#rmu-dashboard-dismiss' ).click(function(event) {
                    event.preventDefault();
                    $( '#rmu_dashboard_widget' ).slideUp();
                    $.ajax({
                        url: ajaxurl,
                        type: 'GET',
                        data: { action: 'rmu_dismiss', n: 'dashboard_panel' },
                    });
                });
            });
        </script>
        <style type="text/css">
            #rmu_dashboard_widget {
                margin-top: 20px;
            }
            #rmu_dashboard_widget .inside {
                margin: 0;
                padding: 0;
            }
            #rmu_dashboard_widget .hndle {
                display: none;
            }
            .rmu-dashboard-panel .rmu-dashboard-panel-close:before {
                background: 0 0;
                color: #72777c;
                content: "\f153";
                display: block;
                font: 400 16px/20px dashicons;
                speak: none;
                height: 20px;
                text-align: center;
                width: 20px;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale
            }

            #rmu_dashboard_widget {
                position: relative;
                overflow: auto;
                border-left: 4px solid #ffba00;
                background: #fffbee;
                padding: 0;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                margin: 10px 0;
                line-height: 1.8;
            }

            .rmu-dashboard-panel h2 {
                margin: 0;
                font-size: 21px;
                font-weight: 400;
                line-height: 1.2
            }

            .rmu-dashboard-panel h3 {
                margin: 17px 0 0;
                font-size: 16px;
                line-height: 1.4
            }

            .rmu-dashboard-panel li {
                font-size: 14px
            }

            .rmu-dashboard-panel p {
                color: #72777c
            }

            .rmu-dashboard-action a {
                text-decoration: none
            }

            .rmu-dashboard-panel .about-description {
                font-size: 16px;
                margin: 0
            }

            .rmu-dashboard-panel-content hr {
                margin: 20px -23px 0;
                border-top: 1px solid #f3f4f5;
                border-bottom: none
            }

            .rmu-dashboard-panel .rmu-dashboard-panel-close {
                position: absolute;
                z-index: 10;
                top: 0;
                right: 10px;
                padding: 0 15px 10px 21px;
                font-size: 13px;
                line-height: 1.23076923;
                text-decoration: none
            }

            .rmu-dashboard-panel .rmu-dashboard-panel-close:before {
                position: absolute;
                top: 0;
                left: 0;
                transition: all .1s ease-in-out
            }

            .rmu-dashboard-panel-content {
                margin: 0 13px;
                max-width: 1500px
            }

            .mts-ctad-question {
                font-weight: bold;
            }
        </style>
        <?php
    }

    /**
     * Get Plugin File.
     */
    public function get_plugin_file() {
        if ( ! function_exists( 'debug_backtrace' ) ) {
            return '';
        }

        $backtrace      = debug_backtrace();
        $plugins_folder = trailingslashit( WP_PLUGIN_DIR );
        foreach ( $backtrace as $i => $trace_data ) {
            if ( isset( $trace_data['file'] ) && strpos( $trace_data['file'], $plugins_folder ) === 0 ) {
                // Look for "wp-content/plugins/xx/yy.php".
                $file = str_replace( $plugins_folder, '', $trace_data['file'] );

                if ( preg_match( '#^[^/]+/[^/]+\.php$#i', $file ) ) {
                    return $trace_data['file'];
                }
            } else {
                // Backtrace says we're not in a plugin.
                return '';
            }
        }
        return '';
    }

    /**
     * Supress Redirection.
     */
    public function suppress_redirect() {
        if ( get_option( 'rmu_suppress_redirect', false ) ) {
            update_option( 'rank_math_redirect_about', 0 );
            delete_option( 'rmu_suppress_redirect' );
        }
    }

    /**
     * Auto-install
     */
    public function autoinstall() {
        // Only run auto-install once, ever.
        update_option( 'rm_autoinstall', '1' );

        // Check if plugin exists.
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();

        if ( array_key_exists( RMU_PLUGIN_FILE, $plugins ) ) {
            return;
        }

        // Only install if there isn't another SEO plugin installed already.
        $seo_plugins = array(
            'wordpress-seo',
            'all-in-one-seo-pack',
            'wp-seopress',
            'autodescription',
            'slim-seo',
            'squirrly-seo',
            'platinum-seo-pack',
            'premium-seo-pack',
            'wp-meta-seo',
            'seo-by-10web',
        );

        foreach ( $plugins as $plugin_name => $plugin_data ) {
            foreach ( $seo_plugins as $seo_plugin ) {
                if ( strpos( $plugin_name, $seo_plugin ) !== false ) {
                    // Found a plugin, don't auto-install.
                    return;
                }
            }
        }

        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        $skin     = new Automatic_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );

        $result = $upgrader->install( RMU_PLUGIN_URL );

        // @todo: Add notice about auto-install?
        if ( $this->get_setting( 'auto_activate' ) ) {
            $this->autoactivate();
        }
    }

    /**
     * Auto-activate.
     */
    public function autoactivate() {
        if ( $this->get_setting( 'suppress_wizard' ) ) {
            update_option( 'rank_math_wizard_completed', true );
            update_option( 'rank_math_registration_skip', true );
            update_option( 'rmu_suppress_redirect', '1' );
        }

        $activate = activate_plugin( RMU_PLUGIN_FILE );
        // @todo: Add notice about auto-activate?
    }

    /**
     * Add Meta Boxes.
     */
    public function add_meta_boxes() {
        if ( $this->is_dismissed( 'seo_meta_box' ) ) {
            return;
        }

        if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
            return;
        }

        $post_types = get_post_types( array( 'public' => true ) );
        add_meta_box( 'rm-upsell-metabox', 'SEO', array( $this, 'meta_box_content' ), $post_types, 'advanced', 'high' );
    }

    /**
     * Meta Box Content.
     */
    public function meta_box_content() {
        $plugins      = array_keys( get_plugins() );
        $rm_installed = in_array( RMU_PLUGIN_FILE, $plugins, true );
        ?>
        <div id="mts-rm-upsell-metabox">
            <?php
            if ( $rm_installed ) {
                echo strtr( $this->get_setting( 'metabox_notice_activate' ), array( '@CTA' => $this->get_activate_link() ) );
            } else {
                echo strtr( $this->get_setting( 'metabox_notice_install' ), array( '@CTA' => $this->get_install_link() ) );
            }
            ?>
            <a href="#" id="mts-rm-upsell-dismiss"><span class="dashicons dashicons-no-alt"></span></a>
        </div>
        <script type="text/javascript">
            jQuery(window).load(function() {
                var $ = jQuery;
                $( '#mts-rm-upsell-dismiss' ).click(function(event) {
                    event.preventDefault();
                    $( '#rm-upsell-metabox' ).fadeOut( '400' );
                    $.ajax({
                        url: ajaxurl,
                        type: 'GET',
                        data: { action: 'rmu_dismiss', n: 'seo_meta_box' },
                    });
                });
            });
        </script>
        <style type="text/css">
            #mts-rm-upsell-metabox {
                border-left: 4px solid #ffba00;
                background: #fffbee;
                padding: 12px 24px 12px 12px;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                margin: 10px 0 0;
                line-height: 1.8;
                position: relative;
                z-index: 1;
            }
            #mts-rm-upsell-dismiss {
                display: block;
                position: absolute;
                right: 12px;
                top: 24px;
                top: calc(50% - 12px);
                text-decoration: none;
                color: #444;
            }
            .mts-ctad-question {
                font-weight: bold;
            }
        </style>
        <?php
    }

    /**
     * Init
     *
     * @param array $config Congiruation.
     */
    public static function init( $config = array() ) {
        if ( self::$instance === null ) {
            self::$instance = new MTS_RMU( $config );
        } else {
            self::$instance->config( $config );
        }

        return self::$instance;
    }

    /**
     * Configure.
     *
     * @param  string $configuration Configuraiton.
     * @param  string $value         Value.
     */
    public function config( $configuration, $value = null ) {
        if ( is_string( $configuration ) && $value !== null ) {
            $this->config[ $configuration ] = $value;
            return;
        }

        $this->config = array_merge( $this->config, $configuration );
    }

    /**
     * Get Settings.
     *
     * @param array $setting Settings.
     */
    public function get_setting( $setting ) {
        if ( isset( $this->config[ $setting ] ) ) {
            return $this->config[ $setting ];
        }
        return null;
    }

    /**
     * Dismiss Notice.
     *
     * @param string $notice Notice.
     */
    public function dismiss_notice( $notice ) {
            $current            = (array) get_user_meta( get_current_user_id(), 'rmu_dismiss', true );
            $current[ $notice ] = '1';
            update_user_meta( get_current_user_id(), 'rmu_dismiss', $current );
    }

    /**
     * Is Dismissable?
     *
     * @param string $notice Notice.
     */
    public function is_dismissed( $notice ) {
        $current = (array) get_user_meta( get_current_user_id(), 'rmu_dismiss', true );
        return ( ! empty( $current[ $notice ] ) );
    }

    /**
     * Ajax Dismiss Notice.
     */
    public function ajax_dismiss_notice() {
        $notice = sanitize_title( wp_unslash( $_GET['n'] ) );
        $this->dismiss_notice( $notice );
        exit;
    }

    /**
     * Inject Metabox Notice.
     *
     * @param  string $plugin_name        Plugin Name.
     * @param  string $selector           Selector.
     * @param  string $metabox_dependency Dependency.
     */
    public function inject_metabox_notice( $plugin_name, $selector, $metabox_dependency ) {
        $plugin = sanitize_title( $plugin_name );
        if ( $this->is_dismissed( $plugin ) ) {
            return;
        }

        if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
            return;
        }
        ?>
        <div style="display: none;" id="mts-rm-upsell-notice">
            <?php echo $this->get_competitor_notice( $plugin_name ); ?>
            <a href="#" id="mts-rm-upsell-dismiss"><span class="dashicons dashicons-no-alt"></span></a>
        </div>
        <script type="text/javascript">
            jQuery(window).load(function() {
                var $ = jQuery;
                if ( $( '<?php echo $metabox_dependency; ?>' ).length ) {
                    $( '#mts-rm-upsell-notice' ).<?php echo $selector; ?>.show();
                    $( '#mts-rm-upsell-dismiss' ).click(function(event) {
                        event.preventDefault();
                        $( '#mts-rm-upsell-notice' ).fadeOut( '400' );
                        $.ajax({
                            url: ajaxurl,
                            type: 'GET',
                            data: { action: 'rmu_dismiss', n: '<?php echo $plugin; ?>' },
                        });
                    });
                }
            });
        </script>
        <?php echo $this->get_notice_css(); ?>
        <?php
    }

    /**
     * Get Competitor Notice.
     *
     * @param  string  $utm_source UTM Source.
     * @param  boolean $cta        Show CTA.
     */
    public function get_competitor_notice( $utm_source, $cta = true ) {
        return strtr(
            $this->get_setting( 'competitor_notice' ),
            array(
                '@CTA'    => $cta ? $this->get_install_or_activate_link() : '',
                '@SOURCE' => $utm_source,
            )
        );
    }

    /**
     * Get Notice CSS.
     */
    public function get_notice_css() {
        return '<style type="text/css">
            #mts-rm-upsell-notice {
                border-left: 4px solid #ffba00;
                background: #fffbee;
                padding: 12px 24px 12px 12px;
                box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                margin: 10px 0;
                line-height: 1.8;
                position: relative;
                z-index: 1;
            }
            #mts-rm-upsell-dismiss {
                display: block;
                position: absolute;
                right: 4px;
                top: 5px;
                text-decoration: none;
                color: rgba(82, 65, 0, 0.16);
            }
            .mts-ctad-question {
                font-weight: bold;
            }
            .nhp-opts-info-field {
                width: 94%;
            }
        </style>';
    }

    /**
     * Enqueue Install Scripts.
     *
     * @param  string $hook_suffix Hook Suffix.
     */
    public function enqueue_install_scripts( $hook_suffix ) {
        // Avoid conflict with theme install script.
        if ( wp_script_is( 'theme', 'enqueued' ) ) {
            return;
        }
        add_thickbox();
        wp_enqueue_script( 'plugin-install' );
        wp_enqueue_script( 'updates' );
    }

    /**
     * Get Install Link.
     *
     * @param  string $class Class.
     * @param  string $label Label.
     */
    public function get_install_link( $class = '', $label = '' ) {
        if ( ! $label ) {
            $label = '<strong>' . $this->get_setting( 'link_label_install' ) . '</strong>';
        }
        $action       = 'install-plugin';
        $slug         = RMU_PLUGIN_SLUG;
        $install_link = add_query_arg(
            array(
                'tab'       => 'plugin-information',
                'plugin'    => $slug,
                'TB_iframe' => 'true',
                'width'     => '600',
                'height'    => '550',
            ),
            admin_url( 'plugin-install.php' )
        );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_install_scripts' ), 11, 1 );

        return '<a href="' . $install_link . '" class="thickbox ' . esc_attr( $class ) . '" title="' . esc_attr__( 'Rank Math SEO', 'bridge' ) . '">' . $label . '</a>';
    }

    /**
     * Get Activation Link.
     *
     * @param  string $class Class.
     * @param  string $label Label.
     */
    public function get_activate_link( $class = '', $label = '' ) {
        if ( ! $label ) {
            $label = '<strong>' . $this->get_setting( 'link_label_activate' ) . '</strong>';
        }
        $activate_link = wp_nonce_url( 'plugins.php?action=activate&plugin=' . rawurlencode( RMU_PLUGIN_FILE ), 'activate-plugin_' . RMU_PLUGIN_FILE );
        return '<a href="' . $activate_link . '" class="' . esc_attr( $class ) . '">' . $label . '</a>';
    }

    /**
     * Get Install or Activation Link.
     *
     * @param  string $class          Class.
     * @param  string $label_install  Label Install.
     * @param  string $label_activate Label Activate.
     */
    public function get_install_or_activate_link( $class = '', $label_install = '', $label_activate = '' ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugins      = array_keys( get_plugins() );
        $rm_installed = in_array( RMU_PLUGIN_FILE, $plugins, true );

        if ( ! $rm_installed ) {
            return $this->get_install_link( $class, $label_install );
        } else {
            return $this->get_activate_link( $class, $label_activate );
        }
    }

    /**
     * Inject Yoast Notice.
     */
    public function inject_yoast_notice() {
        $this->inject_metabox_notice( 'Yoast+SEO', 'insertBefore("#wpseo_meta")', '#wpseo_meta' );
    }

    /**
     * Inject AIO SEO Notice.
     */
    public function inject_aioseo_notice() {
        $this->inject_metabox_notice( 'AIO+SEO', 'insertBefore("#aiosp")', '#aiosp' );
    }

    /**
     * Add SEO Tab in Theme Options.
     *
     * @param array $sections Sections.
     */
    public function add_theme_options_seo_tab( $sections ) {
        $didyouknow     = '';
        $active_plugins = get_option( 'active_plugins' );
        if ( in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true ) ) {
            $didyouknow = '<div id="mts-rm-upsell-notice">' . $this->get_competitor_notice( 'Theme+Options+Panel', false ) . '</div>';
        } elseif ( in_array( 'all-in-one-seo-pack/all_in_one_seo_pack.php', $active_plugins, true ) ) {
            $didyouknow = '<div id="mts-rm-upsell-notice">' . $this->get_competitor_notice( 'Theme+Options+Panel', false ) . '</div>';
        }
        $sections[] = array(
            'icon'   => 'fa fa-line-chart',
            'title'  => __( 'SEO', 'bridge' ),
            'desc'   => '<p class="description">' . __( 'Fix all your SEO issues with just a few clicks. Comply with all the latest Google’s guidelines.', 'bridge' ) . '</p>',
            'fields' => array(
                'seo_message' => array(
                    'id'    => 'mts_seo_message',
                    'type'  => 'info',
                    'title' => '<a href="https://rankmath.com/wordpress/plugin/seo-suite/" target="_blank" class="rm-logo">' . $this->get_logo_svg() . '</a>',
                    'desc'  => strtr( $this->get_setting( 'theme_options_notice' ), array( '@CTA' => $this->get_install_or_activate_link() ) ) . $didyouknow . $this->get_install_or_activate_link( 'button button-primary', __( 'Install Now', 'bridge' ), __( 'Activate It', 'bridge' ) ) . $this->get_notice_css(),
                ),
            ),
        );
        return $sections;
    }

    /**
     * Get RM Logo SVG.
     */
    public function get_logo_svg() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 630 101.88"><title>Asset 1</title><path d="M235.33,89.2l-12-17.2a21.43,21.43,0,0,1-2.27.09H207.76V89.2H196.83V30.36H221c15.46,0,25.05,7.9,25.05,20.93,0,8.91-4.54,15.47-12.53,18.58L247.1,89.2Zm-14.8-49.59H207.76V63.06h12.77c9.59,0,14.54-4.37,14.54-11.77S230.12,39.61,220.53,39.61Z" style="fill:#636eb9"/><path d="M294.41,62.89V89.2h-9.92V83.74c-2.52,3.87-7.4,6.05-14.12,6.05-10.25,0-16.73-5.63-16.73-13.45,0-7.48,5.05-13.36,18.66-13.36h11.6V62.3c0-6.13-3.7-9.75-11.18-9.75-5,0-10.25,1.68-13.61,4.46L255,49.36c4.79-3.7,11.77-5.55,19-5.55C286.93,43.81,294.41,50,294.41,62.89ZM283.9,75.17V70H273.06c-7.14,0-9.08,2.69-9.08,6,0,3.78,3.2,6.22,8.58,6.22C277.68,82.14,282.14,79.79,283.9,75.17Z" style="fill:#636eb9"/><path d="M352.07,63.48V89.2H341.56V64.83c0-8-3.95-11.86-10.68-11.86-7.48,0-12.52,4.54-12.52,13.54V89.2H307.85V44.32h10v5.8c3.45-4.12,8.92-6.31,15.47-6.31C344.08,43.81,352.07,50,352.07,63.48Z" style="fill:#636eb9"/><path d="M383.75,70l-7.82,7.4V89.2H365.42V26.83h10.51V64.49L397.7,44.32h12.61L391.56,63.15,412.07,89.2H399.3Z" style="fill:#636eb9"/><path d="M473.93,89.2l-.08-39L454.51,82.48h-4.87L430.31,50.7V89.2H419.88V30.36h9l23.36,39,22.95-39h9l.08,58.84Z" style="fill:#31344c"/><path d="M536.71,62.89V89.2h-9.92V83.74c-2.52,3.87-7.4,6.05-14.12,6.05-10.25,0-16.73-5.63-16.73-13.45C495.94,68.86,501,63,514.6,63h11.6v-.7c0-6.13-3.7-9.75-11.18-9.75-5,0-10.25,1.68-13.61,4.46l-4.12-7.65c4.79-3.7,11.77-5.55,19-5.55C529.23,43.81,536.71,50,536.71,62.89ZM526.2,75.17V70H515.36c-7.15,0-9.08,2.69-9.08,6,0,3.78,3.2,6.22,8.58,6.22C520,82.14,524.44,79.79,526.2,75.17Z" style="fill:#31344c"/><path d="M577.13,86.76c-2.52,2-6.3,3-10.08,3-9.84,0-15.47-5.21-15.47-15.13V53.06h-7.4V44.65h7.4V34.4h10.51V44.65h12v8.41h-12V74.41c0,4.37,2.18,6.72,6.22,6.72a9.42,9.42,0,0,0,5.88-1.85Z" style="fill:#31344c"/><path d="M630,63.48V89.2H619.49V64.83c0-8-4-11.86-10.67-11.86-7.48,0-12.53,4.54-12.53,13.54V89.2h-10.5V26.83h10.5v22.7c3.45-3.7,8.75-5.72,15-5.72C622,43.81,630,50,630,63.48Z" style="fill:#31344c"/><path d="M166,52.31l-27.36,1.23,4.82,7.55L97.8,90.25l-45.25-19L0,93.13l3.64,8.75L52.53,81.51l46.18,19.4,49.82-31.84,4.83,7.55Z" style="fill:#31344c"/><path d="M19.43,80.31l33.11-13.8,1.62.68V47.58H19.43ZM95.19,0V84.43l2.61,1.1L129.92,65V0ZM57.31,68.52,92,83.1V23.79H57.31Z" style="fill:#636eb9"/></svg>';
    }

    /**
     * Plugin Description.
     */
    public function get_plugin_description() {
            return '    <p><strong>SEO Plugin For WordPress</strong><br>
            ★★★★★</p>
            <p><strong>SEO is the most consistent source of traffic for any website.</strong> We created <a href="https://s.rankmath.com/seo" rel="nofollow ugc"><strong>Rank Math, a WordPress SEO plugin</strong></a>, to help every website owner get access to the SEO tools they need to improve their SEO and attract more traffic to their website.</p>
            <h3><a href="https://s.rankmath.com/demo" rel="nofollow ugc">Try The Demo</a></h3>
            <span class="embed-youtube" style="text-align:center; display: block;"><iframe class="youtube-player" type="text/html" width="640" height="360" src="https://www.youtube.com/embed/NgeaqIy2OB0?version=3&amp;rel=1&amp;fs=1&amp;autohide=2&amp;showsearch=0&amp;showinfo=1&amp;iv_load_policy=1&amp;wmode=transparent" allowfullscreen="true" style="border:0;"></iframe></span>
            <p><a href="https://s.rankmath.com/suitefeatures" rel="nofollow ugc">Features</a> | <a href="https://s.rankmath.com/whyseosuite" rel="nofollow ugc">Why Choose Rank Math?</a> | <a href="https://s.rankmath.com/compare" rel="nofollow ugc">Compare</a> | <a href="https://s.rankmath.com/screenshots" rel="nofollow ugc">Screenshots</a> | <a href="https://s.rankmath.com/benefits" rel="nofollow ugc">Benefits</a></p>
            <p>SEO might be the best and most <strong>consistent source of traffic</strong> for one’s website, but it’s not without its quirks. The constant process of optimizing your posts can sometimes take more time than actually writing the content. <strong>If you always feel that you can do more on the SEO</strong> front for your website but don’t have the time, *then Rank Math is what you’re looking for.</p>
            <p>Its host of <strong>intelligent features</strong> brings top SEO capabilities in your hands that were previously out of reach. The <strong>smart automation</strong> features give you the <strong>power of an entire SEO team</strong> with just a few clicks. A <strong>well thought out design, powerful features, and years of development</strong> by the MyThemeShop squad make Rank Math a game-changing SEO plugin that will level the SEO playing field in your favor.</p>
            <h3>Rank Math beats the competition with hands tied behind its back.</h3>
            <p>See the features which are exclusive to Rank Math SEO plugin and to understand why Rank Math is possibly the <strong>Best SEO Plugin for WordPress</strong>.</p>
            <ul>
            <li>
            <p><strong>Setup Wizard (Easy to follow)</strong><br>
            Rank Math practically configures itself. Rank Math features a step-by-step installation and configuration wizard that sets up SEO for WordPress perfectly.</p>
            </li>
            <li>
            <p><strong>Google Schema Markup aka Rich Snippets Integrated</strong><br>
            Configuring Google Schema Markup, aka Rich Snippets, is now easy, thanks to Rank Math. With support for 13+ types of Schema Markups, aka Rich Snippets, including the Rating Schema, you’ll be able to optimize your posts in just a few clicks. It also includes the <a href="https://rankmath.com/blog/faq-schema/" rel="nofollow ugc">FAQ Schema aka FAQPage Schema Block in the plugin</a>.</p>
            </li>
            <li>
            <p><strong>Optimize Unlimited Keywords</strong><br>
            Unlike other plugins, Rank Math lets you optimize your posts for unlimited focus keywords per post. (5 by default. Increase by adding this <a href="https://rankmath.com/kb/filters-hooks-api-developer/#unlimited-keywords" rel="nofollow ugc">filter</a>)</p>
            </li>
            <li>
            <p><strong>Google Search Console Integration</strong><br>
            Rank Math saves you a ton of time by integrating with Google Search Console and displaying important information about your website right inside WordPress.</p>
            </li>
            <li>
            <p><strong>Google Keyword Ranking</strong><br>
            With Rank Math SEO Plugin, you can track your keyword rankings in Google.</p>
            </li>
            <li>
            <p><strong>Optimal Settings Pre-Selected</strong><br>
            Configuring an SEO plugin takes time, and can be confusing. Rank Math saves you the trouble with its optimal default settings, which are ideal for most websites, and if needed, can be changed.</p>
            </li>
            <li>
            <p><strong>LSI Keyword Tool Integrated</strong><br>
            Rank Math’s integrated LSI keyword tool gives you multiple keyword variations of your focus keyword, which helps you attract more traffic to your posts.</p>
            </li>
            <li>
            <p><strong>Add Overlay Icons On Social Images</strong><br>
            Rank Math makes social thumbnails click magnets by giving you the option of overlaying a GIF or a video icon on the thumbnail.</p>
            </li>
            <li>
            <p><strong>Advanced SEO Analysis Tool</strong><br>
            With just a single click, Rank Math can perform an SEO audit of your website.</p>
            </li>
            <li>
            <p><strong>34 Detailed SEO Tests</strong><br>
            Rank Math is designed to completely supercharge your website’s SEO with its 34 detailed SEO tests.</p>
            </li>
            <li>
            <p><strong>Module Based System</strong><br>
            Rank Math has been designed with a module-based system, each of which can be enabled or disabled as per your needs, giving you extra speed and control.</p>
            </li>
            <li>
            <p><strong>Smart Redirection Manager</strong><br>
            Rank Math’s built-in smart redirection manager will help you create, manage, delete, enable, or disable redirects at scale.</p>
            </li>
            <li>
            <p><strong>Local Business SEO</strong><br>
            Rank Math is designed to be used by Global websites and local websites alike. With its local SEO features, local sites can stand out in the SERPs and attract more traffic.</p>
            </li>
            <li>
            <p><strong>SEO Optimized Breadcrumbs</strong><br>
            Rank Math can display SEO optimized Breadcrumbs on all websites, even if the theme doesn’t support Schema.org coding.</p>
            </li>
            <li>
            <p><strong>404 Monitor</strong><br>
            Rank Math has a built-in 404 error monitor that helps you find and resolve 404 errors for better user experience.</p>
            </li>
            <li>
            <p><strong>Deep Content Analysis Tests</strong><br>
            On-Page SEO is no longer a mystery with Rank Math’s deep content analysis and precise SEO recommendations.</p>
            </li>
            <li>
            <p><strong>Internal Linking Suggestions</strong><br>
            Rank Math intelligently suggests other posts from your website for internal linking from your current posts, improving the chances of ranking higher in the SERPs.</p>
            </li>
            <li>
            <p><strong>Role Manager</strong><br>
            Even if you have multiple employees manage your website, you can precisely control what each of them has to access to in Rank Math with its role manager.</p>
            </li>
            <li>
            <p><strong>Multisite Ready</strong><br>
            Whether you run a single WordPress website or an entire network of sites – we are ready for you. Rank Math fully supports the WordPress Multiuser project (WPMU).</p>
            </li>
            <li>
            <p><strong>and only 30k Lines of Code compared to over 50k in other plugins.</strong><br>
            Even with significantly more features than other plugins, Rank Math still consists of just 30 thousand lines of code.</p>
            </li>
            </ul>
            <h3>Why is Rank Math such a game-changer?</h3>
            <ul>
            <li>
            <p><strong>Auto Configuration</strong> — All you have to do is set a few options, and Rank Math will configure itself perfectly for your website.</p>
            </li>
            <li>
            <p><strong>Super Fast SEO Plugin</strong> — Even after packing so many features, Rank Math has a negligible load on your server.</p>
            </li>
            <li>
            <p><strong>New SEO Analyzer</strong> — Rank Math’s built-in SEO analysis will give you SEO recommendations that you’d normally spend hundreds of dollars to get.</p>
            </li>
            <li>
            <p><strong><a href="https://rankmath.com/blog/elementor-seo/" rel="nofollow ugc">Elementor SEO</a></strong> – Deep integration with the Elementor Page builder. Now, you don’t need to go back and forth between tabs to configure your page’s SEO. Everything related to SEO for Elementor can be configured in the visual editor.</p>
            </li>
            <li>
            <p><strong>Optimize UNLIMITED Keywords At Once</strong> — You can optimize your post for up to 5 different keywords by default with the Rank Math plugin and can use a filter to optimize for unlimited keywords.</p>
            </li>
            <li>
            <p><strong>1-Click Import From Yoast</strong> — With a single click of your mouse, Rank Math can import all your settings from Yoast SEO to itself. The transfer is instant, and you don’t lose any SERP rankings as a result.</p>
            </li>
            <li>
            <p><strong>1-Click Import From AIO SEO</strong> — Rank Math can also import all your settings from AIO SEO in a single click. The transfer is instant, and you don’t lose any SERP rankings as a result.</p>
            </li>
            <li>
            <p><strong>1-Click Import From All In One Schema Rich Snippets</strong> — Rank Math can also import all of AIO’s Rich Snippet settings in a few clicks, which help preserve your rich rankings when moving to Rank Math.</p>
            </li>
            <li>
            <p><strong>1-Click Import From SEOPress SEO</strong> — With a single click of your mouse, Rank Math can import all your settings from SEOPress &amp; SEOPress Pro SEO plugin to itself. The transfer is instant, and you don’t lose any SERP rankings as a result.</p>
            </li>
            <li>
            <p><strong>1-Click Import From Redirection</strong> — Moving all your redirects shouldn’t be a hassle. That’s why we have made importing redirections from the popular Redirection plugin as simple as clicking a button.</p>
            </li>
            <li>
            <p><strong>Google Keyword Suggestion</strong> — When deciding on focus keywords, Rank Math can help you discover more keywords by pulling in keyword suggestions from Google.</p>
            </li>
            </ul>
            <h3>Who Can Benefit From Rank Math?</h3>
            <h4>Rank Math SEO Plugin is perfect for:</h4>
            <p>✔ Bloggers<br>
            ✔ eCommerce Store Owners<br>
            ✔ Niche Sites<br>
            ✔ Businesses<br>
            ✔ Local Businesses<br>
            ✔ Startups<br>
            ✔ The Real Estate<br>
            ✔ Artists &amp; Photographers<br>
            ✔ The Solution Offerer<br>
            ✔ Directories<br>
            ✔ Or any WordPress Website</p>
            <h3>Take a sneak peek into Rank Math’s features</h3>
            <h4>Detailed Setup Tutorial</h4>
            <span class="embed-youtube" style="text-align:center; display: block;"><iframe class="youtube-player" type="text/html" width="640" height="360" src="https://www.youtube.com/embed/dTvZuMRzW4w?version=3&amp;rel=1&amp;fs=1&amp;autohide=2&amp;showsearch=0&amp;showinfo=1&amp;iv_load_policy=1&amp;wmode=transparent" allowfullscreen="true" style="border:0;"></iframe></span>
            <h3>List of Best Rank Math SEO Features</h3>
            <ul>
            <li>Clean, &amp; Simple User Interface</li>
            <li><strong>Optimal Settings Pre-Selected</strong></li>
            <li>Simple Setup Wizard
            <ul>
            <li>Compatibility Check</li>
            </ul>
            </li>
            <li><strong>Auto Canonical URLs</strong></li>
            <li><strong>LSI Keyword Tool Integrated</strong></li>
            <li><strong>Google Search Console Integration</strong></li>
            <li><strong>Google Keyword Ranking</strong></li>
            <li><strong>Import Other Plugin Settings</strong>
            <ul>
            <li>1 Click Import From <strong>Yoast SEO Plugin</strong></li>
            <li>1 Click Import From <strong>AIO SEO</strong></li>
            <li>1 Click Import From All In One Schema Rich Snippets</li>
            <li>1 Click Import From SEOPress</li>
            <li>1 Click Import From Redirection Plugin</li>
            </ul>
            </li>
            <li><strong>Role Manager</strong></li>
            <li><strong>ACF Support</strong></li>
            <li><strong>AMP Ready</strong></li>
            <li><strong>bbPress &amp; BuddyPress Modules</strong></li>
            <li><strong>Google Schema Markup Integrated</strong>
            <ul>
            <li><strong>Article Rich Snippet</strong></li>
            <li><strong>Review Rich Snippet</strong></li>
            <li>Book Rich Snippet</li>
            <li>Course Rich Snippet</li>
            <li>Event Rich Snippet</li>
            <li>Job Posting Rich Snippet</li>
            <li><strong>Local Business Rich Snippet</strong></li>
            <li><strong>193 Local Business Types</strong></li>
            <li>Music Rich Snippet</li>
            <li>Person Rich Snippet</li>
            <li>Product Rich Snippet</li>
            <li>Recipe Rich Snippet</li>
            <li>Restaurant Rich Snippet</li>
            <li>Service Rich Snippet</li>
            <li>Software Application Rich Snippet</li>
            <li>Video Rich Snippet</li>
            </ul>
            </li>
            <li><strong>Social Media Optimization</strong>
            <ul>
            <li><strong>Add Overlay Icons On Social Images</strong></li>
            <li>Default OpenGraph Thumbnail</li>
            <li><strong>Auto Facebook Open Graph</strong></li>
            <li>Facebook Authorship</li>
            <li>Facebook Open Graph for Homepage</li>
            <li><strong>Automatic Twitter Meta Cards</strong></li>
            <li>Twitter Card for Homepage</li>
            <li>Default Twitter Card Type</li>
            <li><strong>Social Previews</strong></li>
            </ul>
            </li>
            <li>More <strong>Website Verification Options</strong>
            <ul>
            <li>Google+ Page URL for SERPs</li>
            <li>LinkedIn Page URL for SERPs</li>
            <li>Instagram Page URL for SERPs</li>
            <li><strong>YouTube Page URL for SERPs</strong></li>
            <li>Pinterest Page URL for SERPs</li>
            <li>SoundCloud Page URL for SERPs</li>
            <li>Tumblr Page URL for SERPs</li>
            <li>Myspace Page URL for SERPs</li>
            </ul>
            </li>
            <li>Add <strong>Knowledge Graph</strong>
            <ul>
            <li>Represent site as a Person</li>
            <li>Represent site as a Company</li>
            <li>Set a Site Logo</li>
            </ul>
            </li>
            <li><strong>Advanced SEO Analysis Tool</strong>
            <ul>
            <li><strong>34 Detailed SEO Tests</strong></li>
            <li>SEO Analysis Score</li>
            </ul>
            </li>
            <li><strong>Automated Image SEO</strong></li>
            <li><strong>Powerful Post Optimization</strong>
            <ul>
            <li><strong>Add SEO Meta Box to all post types</strong></li>
            <li><strong>Bulk Edit Titles &amp; Descriptions</strong></li>
            <li><strong>Post Preview on Google</strong></li>
            <li>Content Analysis</li>
            <li>Control SEO For Single Pages</li>
            <li>Control The Title</li>
            <li>Control Meta Description</li>
            <li><strong>Auto Add Additional Meta Data</strong></li>
            <li>Control ROBOTS Meta</li>
            <li><strong>Choose a Primary Category</strong></li>
            </ul>
            </li>
            <li>Single Post/page Optimization
            <ul>
            <li><strong>Focus Keyword</strong></li>
            <li><strong>Google Keyword Suggestion</strong></li>
            <li><strong>Optimize UNLIMITED Keywords (5 by default)</strong></li>
            <li>Choose Pillar Posts &amp; Pages</li>
            <li>Internal Linking Suggestions</li>
            <li>Capitalize Titles</li>
            <li>SEO Failed Tests</li>
            <li>SEO Warnings</li>
            </ul>
            </li>
            <li><strong></strong><strong>XML Sitemap (New!)</strong></li>
            <li>Module Based System</li>
            <li>Choose Any Separator Character</li>
            <li>Modify Global Meta</li>
            <li><strong>Search Engine Verification Tools</strong>
            <ul>
            <li>Bing Site Verification</li>
            <li>Baidu Site Verification</li>
            <li>Alexa Site Verification</li>
            <li>Yandex Site Verification</li>
            <li><strong>Google Site Verification</strong></li>
            <li>Pinterest Site Verification</li>
            <li>Norton Safe Web Site Verification</li>
            </ul>
            </li>
            <li>Advanced <strong>Redirection Manager</strong>
            <ul>
            <li><strong>Smart &amp; Automatic Post Redirects</strong></li>
            <li>301 Redirection Type</li>
            <li>302 Redirection Type</li>
            <li>307 Redirection Type</li>
            <li>410 Redirection Type</li>
            <li>451 Redirection Type</li>
            <li><strong>Support for REGEX</strong></li>
            <li>Debug Redirections</li>
            </ul>
            </li>
            <li>Simple <strong>404 Monitor</strong>
            <ul>
            <li>Advanced 404 Monitor</li>
            </ul>
            </li>
            <li>Advanced SEO Breadcrumbs
            <ul>
            <li>Auto Show SEO Breadcrumbs</li>
            </ul>
            </li>
            <li><strong>Advanced Link Options</strong>
            <ul>
            <li>Nofollow All External Image Links</li>
            <li><strong>Nofollow All External Links</strong></li>
            <li>Open External Links in New Tab/Window</li>
            <li>Redirect Attachments to Parent</li>
            <li><strong>Strip Category Base</strong></li>
            </ul>
            </li>
            <li>Remove Stopwords from Permalinks</li>
            <li><strong>Ping Search Engines</strong></li>
            <li><strong>Local SEO Optimization</strong>
            <ul>
            <li>Contact Info Shortcode</li>
            </ul>
            </li>
            <li><strong>Deep Support For WooCommerce SEO</strong></li>
            <li><strong>Compatible for EDD SEO</strong></li>
            <li><strong>Only 30k Lines of Code</strong>
            <ul>
            <li>PSR-4 Coding Standards (wherever possible)</li>
            </ul>
            </li>
            <li><strong>Optimize Different Archives</strong>
            <ul>
            <li>Optimize Author Archive Pages</li>
            <li>Optimize Date Archive Pages</li>
            <li>Optimize Archive Pages</li>
            </ul>
            </li>
            <li><strong>.htaccess Editor</strong></li>
            <li><strong>Robots.txt Editor</strong></li>
            <li><strong>Import/Export Settings</strong></li>
            <li>Import/Export Redirections</li>
            <li>Add Content Before the RSS Feed</li>
            <li>Add Content After the RSS Feed</li>
            <li><strong>Detailed Documentation</strong>
            <ul>
            <li>Contextual Help</li>
            </ul>
            </li>
            </ul>
';
    }

    /**
     * Redirect on Install.
     */
    public function rm_install_redirect() {
        if ( ( isset( $_GET['tgmpa-activate'] ) && $_GET['tgmpa-activate'] == 'activate-plugin' ) || ( isset( $_POST['action'] ) && $_POST['action'] == 'tgmpa-bulk-activate' && isset( $_POST['plugin'] ) && is_array( $_POST['plugin'] ) && in_array( 'seo-by-rank-math', $_POST['plugin'] ) ) ) {
            add_action( 'admin_footer', array( $this, 'rm_redirect_late' ), 99 );
        }
    }

    /**
     * Add Redirect Delay.
     */
    public function rm_redirect_late() {
        $options           = get_option( 'mts_connect_data', false );
        $invalid           = empty( $options );
        $skip_registration = get_option( 'rank_math_registration_skip' );

        if ( true === boolval( $skip_registration ) ) {
            $invalid = false;
        }

        $url = 'wizard';
        if ( $invalid ) {
            $url = 'registration';
        } elseif ( get_option( 'rank_math_wizard_completed' ) ) {
            $url = '';
        }

        if ( $url ) {
            $url = add_query_arg( array( 'page' => 'rank-math-' . $url ), admin_url( 'admin.php' ) );
            echo '<div class="rm-redirect-wrap" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.4); z-index: 111112;"><div class="rm-redirect-notice" style="position: absolute; top: 20%; left: 50%; width: 400px; margin-left: -200px; text-align: center; background: #fff; box-shadow: 0 10px 100px rgba(0, 0, 0, 0.3); padding-top: 35px; padding-bottom: 40px;">' . __( 'Redirecting to Rank Math Setup Wizard...', 'bridge' ) . '</div></div>';
            echo '<meta http-equiv="refresh" content="0;URL=\'' . $url . '\'" />';
        }
    }
}

define( 'RMU_ACTIVE', true );