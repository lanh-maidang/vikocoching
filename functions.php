<?php

/**
 *
 * Sets up theme defaults and registers support for various WordPress features.
 */

use ColibriWP\Theme\Core\Hooks;
use ColibriWP\Theme\Core\Utils;
use Kubio\StarterContent\StarterContent;
use ColibriWP\Theme\Defaults;
use ColibriWP\Theme\Translations;
use Kubio\Core\Activation;
use Kubio\Theme\Components\CssOutput;
use Kubio\Theme\Components\Footer;
use Kubio\Theme\Components\FrontHeader\Buttons;
use Kubio\Theme\Components\FrontHeader\Hero as FrontPageHero;
use Kubio\Theme\Components\FrontHeader\Image;
use Kubio\Theme\Components\FrontHeader\Navigation;
use Kubio\Theme\Components\FrontHeader\Subtitle;
use Kubio\Theme\Components\FrontHeader\Title;
use Kubio\Theme\Components\FrontHeader\TopBar;
use Kubio\Theme\Components\FrontHeader\TopBarListIcons;
use Kubio\Theme\Components\FrontHeader\TopBarSocialIcons;
use Kubio\Theme\Components\FrontPageContent;
use Kubio\Theme\Components\Header;
use Kubio\Theme\Components\Header\Logo;
use Kubio\Theme\Components\HeaderMenu;
use Kubio\Theme\Components\InnerHeader\Hero as InnerHero;
use Kubio\Theme\Components\InnerHeader\Navigation as InnerNavigation;
use Kubio\Theme\Components\InnerHeader\Title as InnerTitle;
use Kubio\Theme\Components\InnerHeader\TopBar as InnerTopBar;
use Kubio\Theme\Components\MainContent;
use Kubio\Theme\Components\MainContent\ArchiveLoop;
use Kubio\Theme\Components\MainContent\PostLoop;
use Kubio\Theme\Components\PageContent;
use Kubio\Theme\Components\PageNotFound;
use Kubio\Theme\Components\SingleContent;
use Kubio\Theme\Flags;
use Kubio\Theme\Theme;

require_once get_template_directory() . '/vendor/autoload.php';
require_once __DIR__ . '/inc/safari-polyfills.php';

function vertice_register_components( $components ) {
	$namespace = 'ColibriWP\\Theme\\Components';

	$components = array_merge(
		$components,
		array(

			// header components
			'header'               => Header::class,
			'logo'                 => Logo::class,
			'header-menu'          => HeaderMenu::class,

			// inner page fragments
			'inner-nav-bar'        => InnerNavigation::class,
			'inner-hero'           => InnerHero::class,
			'inner-title'          => InnerTitle::class,
			'inner-top-bar'        => InnerTopBar::class,

			// front page fragments
			'front-hero'           => FrontPageHero::class,
			'front-title'          => Title::class,
			'front-subtitle'       => Subtitle::class,
			'buttons'              => Buttons::class,
			'front-nav-bar'        => Navigation::class,
			'top-bar-list-icons'   => TopBarListIcons::class,
			'top-bar-social-icons' => TopBarSocialIcons::class,
			'front-top-bar'        => TopBar::class,
			'front-image'          => Image::class,

			// footer components
			'front-footer'         => Footer::class,

			// general components
			'css'                  => CssOutput::class,

			// page content
			'main'                 => MainContent::class, // blog loop
			'single'               => SingleContent::class, // single page
			'content'              => PageContent::class, // inner page content
			'front-page-content'   => FrontPageContent::class, // front page content
			'search'               => "{$namespace}\\PageSearch", // search page
			'page-not-found'       => PageNotFound::class, // 404 page

			// inner content fragments

			// main content
			'main-loop'            => ArchiveLoop::class, // no usage found
			'post-loop'            => PostLoop::class, // single page content
			'archive-loop'         => ArchiveLoop::class, // blog page content

		)
	);

	return $components;
}

Hooks::prefixed_add_filter( 'components', 'vertice_register_components', 20 );
Theme::load(
	array(
		'themeBaseRelativePath' => '',
		'themeRelativePath'     => '',
	)
);

/**
 * @return Theme
 */
function vertice_theme() {
	return Theme::getInstance();
}

function vertice_assets() {
	return vertice_theme()->getAssetsManager();
}


vertice_theme()
	->add_theme_support( 'automatic-feed-links' )
	->add_theme_support( 'title-tag' )
	->add_theme_support( 'post-thumbnails' )
	->add_theme_support(
		'custom-logo',
		array(
			'flex-height' => true,
			'flex-width'  => true,
			'width'       => 150,
			'height'      => 70,
		)
	);

add_action('after_setup_theme', function() {
    vertice_theme()->register_menus(
        array(
            'header-menu' => esc_html__( 'Header Menu', 'vertice' ),
            'footer-menu' => esc_html__( 'Footer Menu', 'vertice' ),
        )
    );
}, 1);
if ( ! apply_filters( 'kubio_is_enabled', false ) ) {
	vertice_assets()
		->registerTemplateScript(
			'vertice-theme',
			'/theme/theme.js',
			array( 'jquery', 'jquery-effects-slide', 'jquery-effects-core' )
		)
		->registerStylesheet( 'vertice-theme', '/theme/theme.css' );
	vertice_assets()->loadLocalGoogleFonts();
	//	foreach ( Defaults::get( 'fonts', array() ) as $family => $variants ) {
	//		vertice_assets()->addGoogleFont( $family, $variants );
	//	}

	add_action( 'wp_enqueue_scripts', 'vertice_print_color_scheme', 0 );
} else {
	vertice_assets()
		->registerStyle( 'vertice-theme', vertice_assets()->getBaseURL() . '/theme/fse-base-style.css' );
	Hooks::prefixed_add_filter( 'skip_google_fonts', '__return_true' );
}

function vertice_theme_add_woocommerce_support() {
	add_theme_support(
		'woocommerce',
		array(
			'product_grid' => array(
				'default_rows'    => 3,
				'min_rows'        => 2,
				'max_rows'        => 8,
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);

	add_theme_support( 'kubio-woocommerce' );
}

add_action( 'after_setup_theme', 'vertice_theme_add_woocommerce_support' );

add_filter( 'kubio/activation/after_activation_redirect_url', 'vertice_after_activation_redirect_url' );
function vertice_after_activation_redirect_url( $url ) {
	if ( Flags::get( 'start_source', false ) == 'notice-homepage' ) {
		$url = add_query_arg(
			array(
				'page'                    => 'kubio-get-started',
				'kubio-designed-imported' => intval( ! ! Flags::get( 'import_design', false ) ),
			),
			admin_url( 'admin.php' )
		);
	}
	return $url;
}

Hooks::add_wp_ajax(
	'front_set_predesign',
	function () {
		check_ajax_referer( 'kubio_front_set_predesign_nonce', 'nonce' );
		$with_ai = Utils::pathGet( $_REQUEST, 'AI', 'no' );
		$source  = sanitize_text_field( Utils::pathGet( $_REQUEST, 'source', 'notice' ) );
		$index   = intval( Utils::pathGet( $_REQUEST, 'index', 0 ) );

		if ( $with_ai === 'yes' ) {
			Flags::set( 'start_with_ai', true );
		} else {
			Flags::set( 'import_design', true );
			Flags::set( 'import_design_index', $index );
		}

		// customizer source
		$start_source = $source;

		if ( $source === 'notice' ) {
			$start_source = $with_ai === 'yes' ? "{$source}-ai" : "{$source}-homepage";
		}

		Flags::set( 'start_source', $start_source );

		wp_send_json_success();
	}
);

function vertice_get_builder_plugin_slug() {

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();

	if ( isset( $plugins['kubio-pro/plugin.php'] ) ) {
		return 'kubio-pro';
	}

	return 'kubio';
}

function vertice_theme_plugins( $plugins ) {

	$plugin_slug = vertice_get_builder_plugin_slug();

	$plugins[ $plugin_slug ] = array(
		'name'        => $plugin_slug === 'kubio-pro' ? __( 'Kubio PRO', 'vertice' ) : __( 'Kubio', 'vertice' ),
		'description' => \ColibriWP\Theme\Translations::translate( 'page_builder_plugin_description' ),
		'plugin_path' => $plugin_slug . '/plugin.php',
	);

	return $plugins;
}

Hooks::prefixed_add_filter( 'theme_plugins', 'vertice_theme_plugins' );


add_filter(
	'kubio/remote_data_url',
	function( $url ) {
		if ( strpos( $url, 'with-front' ) !== false && Flags::get( 'import_design_index', 0 ) !== 0 ) {
			$front_slug = 'with-front-' . Flags::get( 'import_design_index' );
			$base_url   = 'https://themes.kubiobuilder.com';
			$file_name  = get_stylesheet() . '__' . get_template() . '__' . $front_slug . '.data';
			return "{$base_url}/{$file_name}";
		}
		return $url;
	}
);


add_action(
	'kubio/after_activation',
	function () {
		$activation_instance = Activation::load();
		if ( ! $activation_instance->isCLI() ) {
			$start_source = Flags::get( 'start_source', 'other' );
			if ( $start_source == 'starter-sites' ) {
				$url = add_query_arg(
					array(
						'page' => 'kubio-get-started',
						'tab'  => 'demo-sites',
					),
					admin_url( 'admin.php' )
				);
				wp_redirect( $url );
				exit();
			}
		}
	}
);

Hooks::prefixed_add_action(
	'after_plugin_activated',
	function ( $slug ) {

		if ( $slug === vertice_get_builder_plugin_slug() ) {
			$hash = uniqid( 'activate-' );

			Flags::set( 'activation-hash', $hash );

			$start_source = Flags::get( 'start_source', 'other' );
			if ( strpos( $start_source, 'customizer-sidebar' ) === 0 ) {
				$url = add_query_arg(
					array(
						'page'                  => 'kubio',
						'kubio-activation-hash' => $hash,
						'source'                => $start_source,
					),
					admin_url( 'admin.php' )
				);
			} else {
				if ( $start_source === 'starter-sites' ) {
					$url = add_query_arg(
						array(
							'page'                  => 'kubio-get-started',
							'tab'                   => 'demo-sites',
							'kubio-activation-hash' => $hash,
						),
						admin_url( 'admin.php' )
					);
				} else {
					$url = add_query_arg(
						array(
							'page'                    => 'kubio-get-started',
							'kubio-activation-hash'   => $hash,
							'kubio-designed-imported' => intval( ! ! Flags::get( 'import_design', false ) ),
						),
						admin_url( 'admin.php' )
					);
				}
			}

			wp_send_json_success(
				array(
					'redirect' => $url,
				)
			);
		}
	}
);

add_action(
	'kubio/admin-page/before-get-started',
	function () {
		if ( isset( $_GET['kubio-designed-imported'] ) && intval( $_GET['kubio-designed-imported'] ) ) : ?>
			<div class="kubio-admin-page-page-section kubio-get-started-section-1 wrap">
				<div class="kubio-admin-row get-started-imported notice notice-success">
					<div>
						<p class="imported-title">
							<?php
							echo esc_html(
								sprintf(
									__( '%s design has been successfully imported!', 'vertice' ),
									vertice_theme()->getName()
								)
							);
							?>
						</p>
						<p class="imported-subtitle">
							<?php
							echo esc_html(
								sprintf(
									__( '%s design has been successfully imported! You can take a look at your new design or start editing it', 'vertice' ),
									vertice_theme()->getName()
								)
							);
							?>
						</p>
					</div>
					<div class="button imported-view-site-button">
						<a href="<?php echo esc_url( site_url() ); ?>">
							<?php echo esc_html( __( 'View site', 'vertice' ) ); ?>
						</a>

					</div>
				</div>
			</div>

			<?php
		endif;
	}
);

add_filter( 'kubio/has_block_templates_support', '__return_true' );
add_filter( 'kubio/enable_try_online', '__return_true' );
add_filter( 'kubio/show-supplementary-upgrade-to-pro', '__return_true' );
add_filter( 'kubio/enable_ai_capabilities', '__return_true' );

function vertice_try_online_url() {
	return 'https://kubiobuilder.com/go/try-theme/' . get_template();
}

add_filter( 'kubio/editor-try-online/url', 'vertice_try_online_url', 10 );

function vertice_render_header_style() {

	?>
	<base target="_top">
	<?php

	vertice_theme()->get( 'css' )->render();
}
add_action( 'wp_head', 'vertice_render_header_style', 100 );


add_filter(
	'body_class',
	function ( $classes ) {
		if ( ! apply_filters( 'kubio_is_enabled', false ) ) {
			$classes[] = 'kubio-theme';
		}

		return $classes;
	}
);

add_filter(
	'admin_body_class',
	function ( $classes ) {

		if ( ! apply_filters( 'kubio_is_enabled', false ) ) {
			return "$classes kubio-theme";
		}

		return $classes;
	}
);

function kubio_onboarding_init() {
	// Onboarding notice remains disabled after switching multiple kubio themes.
	update_option( 'kubio-onboarding-notice-disabled', false );
}

add_action( 'after_switch_theme', 'kubio_onboarding_init' );

Hooks::prefixed_add_filter(
	'translations',
	function( $translations ) {
		$translations['customize_preview_overlay_message']  = __( 'These features are part of the Kubio Page Builder plugin. Using them will install the plugin.', 'vertice' );
		$translations['customize_preview_overlay_button_1'] = __( 'Edit this section', 'vertice' );
		$translations['customize_preview_overlay_button_2'] = __( 'Replace this section', 'vertice' );

		return $translations;
	}
);


add_filter(
	'kubio_starter_content_overlay_style',
	function() {

		return array(
			'background' => 'rgba(0,0,0,0.8)',
			'transition' => 'opacity 0.3s ease',
			'button-1'   => array(
				'normal' => array(
					'background' => 'transparent',
					'color'      => '#fff',
					'border'     => '#fff',
				),
				'hover'  => array(
					'background' => 'rgba(255,255,255,0.2)',
					'color'      => '#fff',
					'border'     => '#fff',
				),
			),
			'button-2'   => array(
				'normal' => array(
					'background' => 'transparent',
					'color'      => '#fff',
					'border'     => '#fff',
				),
				'hover'  => array(
					'background' => 'rgba(255,255,255,0.2)',
					'color'      => '#fff',
					'border'     => '#fff',
				),
			),
		);
	}
);

add_filter(
	'kubio_starter_content_pages',
	function() {
		return array(
			StarterContent::HOME_SLUG  => array(
				'post_title' => __( 'Home', 'vertice' ),
				'in_menu'    => true,
			),

			// StarterContent::ABOUT_SLUG => array(
			// 	'post_title' => __( 'About', 'vertice' ),
			// 	'in_menu'    => true,
			// ),

			StarterContent::BLOG_SLUG  => array(
				'post_title' => __( 'Blog', 'vertice' ),
				'in_menu'    => true,
			),

			// StarterContent::CONTACT    => array(
			// 	'post_title' => __( 'Contact', 'vertice' ),
			// 	'in_menu'    => true,
			// ),

		);
	}
);

StarterContent::init();

//fuction for FAQs page
function enqueue_container_tabs_assets() {
    wp_enqueue_style(
        'container-tabs-style',
        get_template_directory_uri() . '/tab-containers/tab-style.css'
    );

    wp_enqueue_script(
        'container-tabs-script',
        get_template_directory_uri() . '/tab-containers/tab-script.js',
        array(),
        false,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_container_tabs_assets');

function custom_container_tabs_shortcode() {
    ob_start();
    include get_template_directory() . '/tab-containers/tab-html.php';
    return ob_get_clean();
}
add_shortcode('container_tabs', 'custom_container_tabs_shortcode');

