<?php

namespace WebberZone\Link_Warnings\Admin {
	function flush_rewrite_rules() {}
	function set_transient( ...$args ) {}
}

namespace {
	$core = $argv[1] ?? getenv( 'WP_CORE_DIR' );
	if ( ! $core || ! is_file( rtrim( $core, '/\\' ) . '/wp-includes/functions.php' ) ) {
		fwrite( STDERR, "Usage: php phpunit/regression.php /path/to/wordpress\n" );
		exit( 1 );
	}

	define( 'ABSPATH', rtrim( $core, '/\\' ) . '/' );
	define( 'WPINC', 'wp-includes' );
	define( 'HOUR_IN_SECONDS', 3600 );
	foreach ( array( 'compat', 'plugin', 'functions', 'formatting', 'http', 'kses', 'shortcodes', 'version' ) as $file ) {
		require ABSPATH . WPINC . '/' . $file . '.php';
	}
	if ( is_file( ABSPATH . WPINC . '/utf8.php' ) ) {
		require ABSPATH . WPINC . '/utf8.php';
	}
	// Attribute values containing "&" force named-character-reference decoding.
	require ABSPATH . WPINC . '/class-wp-token-map.php';
	require ABSPATH . WPINC . '/html-api/html5-named-character-references.php';
	spl_autoload_register(
		function ( $class ) {
			if ( 0 === strpos( $class, 'WP_HTML_' ) ) {
				require ABSPATH . WPINC . '/html-api/class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
			}
		}
	);

	function home_url( $path = '' ) {
		return 'https://site.test/' . ltrim( $path, '/' );
	}
	function wp_salt( $scheme = 'auth' ) {
		return 'link-warnings-regression-key';
	}
	function __( $text, $domain = '' ) {
		return $text;
	}
	function is_multisite() {
		return false;
	}
	function is_admin() {
		return ! empty( $GLOBALS['wzlw_test_is_admin'] );
	}
	function is_user_logged_in() {
		return ! empty( $GLOBALS['wzlw_test_roles'] );
	}
	function wp_get_current_user() {
		return (object) array( 'roles' => $GLOBALS['wzlw_test_roles'] ?? array() );
	}
	function is_singular() {
		return true;
	}
	function get_post_type() {
		return 'post';
	}

	require dirname( __DIR__ ) . '/includes/autoloader.php';
	require dirname( __DIR__ ) . '/includes/options-api.php';

	use WebberZone\Link_Warnings\Admin\Activator;
	use WebberZone\Link_Warnings\Content_Processor;
	use WebberZone\Link_Warnings\Link_Rules;
	use WebberZone\Link_Warnings\Options_API;
	use WebberZone\Link_Warnings\Redirect_Handler;

	$GLOBALS['wzlw_test_settings'] = array();
	$GLOBALS['wzlw_test_updates']  = array();
	$GLOBALS['wp_rewrite']         = new class() {
		public function init() {}
	};
	add_filter(
		'pre_option_blog_charset',
		static function () {
			return 'UTF-8';
		}
	);
	add_filter(
		'pre_option_wzlw_settings',
		static function () {
			return $GLOBALS['wzlw_test_settings'];
		}
	);
	add_filter(
		'pre_update_option_wzlw_settings',
		static function ( $value, $old ) {
			$GLOBALS['wzlw_test_updates'][] = $value;
			return $old;
		},
		10,
		2
	);
	new Content_Processor();
	add_filter( 'the_content', 'wpautop', 10 );
	add_filter( 'the_content', 'do_shortcode', 11 );
	add_shortcode(
		'wzlw_test_form',
		static function () {
			return '<textarea>text</textarea>';
		}
	);

	function settings( array $settings = array() ) {
		$GLOBALS['wzlw_test_settings'] = array_merge(
			array(
				'warning_method'     => 'inline_modal',
				'scope'              => 'external',
				'enabled_post_types' => 'post,page',
			),
			$settings
		);
		$GLOBALS['wzlw_test_updates']  = array();
		$GLOBALS['wzlw_test_is_admin'] = false;
		$GLOBALS['wzlw_test_roles']    = array();
		$GLOBALS['wzlw_test_is_rest']  = false;
		Options_API::flush_cache();
	}
	function is_valid_url( $url ) {
		static $method = null;
		if ( null === $method ) {
			$method = new \ReflectionMethod( Redirect_Handler::class, 'is_valid_url' );
			$method->setAccessible( true );
		}
		return $method->invoke( ( new \ReflectionClass( Redirect_Handler::class ) )->newInstanceWithoutConstructor(), $url );
	}
	function expect( $condition, $message ) {
		if ( ! $condition ) {
			throw new \RuntimeException( $message );
		}
	}
	function process( $content ) {
		return apply_filters( 'the_content', $content );
	}
	function link_attributes( $content, $href ) {
		$processor = new \WP_HTML_Tag_Processor( $content );
		while ( $processor->next_tag( 'A' ) ) {
			if ( $href === $processor->get_attribute( 'href' ) ) {
				$result = array();
				foreach ( array( 'class', 'rel', 'aria-label', 'data-wzlw-url', 'data-wzlw-external', 'data-wzlw-blank', 'data-wzlw-download', 'data-wzlw-excluded', 'data-wzlw-rule', 'data-wzlw-method' ) as $name ) {
					$result[ $name ] = $processor->get_attribute( $name );
				}
				return $result;
			}
		}
		throw new \RuntimeException( 'Expected link not found: ' . $href );
	}

	$tests = array();
	$tests['reactivation leaves existing settings untouched']       = static function () {
		settings(
			array(
				'warning_method'     => 'redirect',
				'excluded_domains'   => 'trusted.test',
				'enabled_post_types' => 'page',
				'redirect_countdown' => 12,
			)
		);
		Activator::activate( false );
		expect( array() === $GLOBALS['wzlw_test_updates'], 'Activation attempted to update existing settings.' );
	};
	$tests['ordinary merged updates still replace supplied values'] = static function () {
		settings( array( 'excluded_domains' => 'trusted.test' ) );
		Options_API::update_settings( array( 'warning_method' => 'redirect' ), true );
		$written = $GLOBALS['wzlw_test_updates'][0];
		expect( 'redirect' === $written['warning_method'] && 'trusted.test' === $written['excluded_domains'], 'Normal update semantics changed.' );
	};
	foreach ( array( 'https://outside.test/page', 'https://outside.test/?a=1&b=2', 'https://outside.test/page#section', 'https://outside.test/?q=a+b', 'https://outside.test/path%20name', 'https://outside.test/?next=%2Faccount', '/go/product/?a=1&b=2#details', '//outside.test/page', '//outside.test/?a=1&b=2' ) as $destination ) {
		$tests[ 'signed URL round trip: ' . $destination ] = static function () use ( $destination ) {
			$url = Redirect_Handler::get_redirect_url( $destination );
			parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
			$received = esc_url_raw( $query['url'] ?? '' );
			expect( $destination === $received, 'Destination changed during request decoding.' );
			expect( hash_equals( hash_hmac( 'sha256', $received, wp_salt( 'auth' ) ), $query['wzlw_sig'] ?? '' ), 'Signature no longer matches.' );
			expect( is_valid_url( $received ), 'Redirect endpoint rejects a URL it signed itself.' );
		};
	}
	$tests['protocol-relative links use their hostname'] = static function () {
		settings();
		$html = process( '<a href="//outside.test/">external</a><a href="//site.test/">internal</a><a href="/go/">relative</a>' );
		expect( 'true' === link_attributes( $html, '//outside.test/' )['data-wzlw-external'], 'External network-path reference was ignored.' );
		expect( null === link_attributes( $html, '//site.test/' )['data-wzlw-external'], 'Same-site URL marked external.' );
		expect( null === link_attributes( $html, '/go/' )['data-wzlw-external'], 'Root-relative URL marked external.' );
	};
	$tests['configured download links are processed regardless of host'] = static function () {
		settings();
		$html = process(
			'<a href="/files/report.pdf">internal PDF</a>'
			. '<a href="https://outside.test/archive.ZIP?download=1#top">external ZIP</a>'
			. '<a href="/download?file=report.pdf">not a file path</a>'
		);
		$internal = link_attributes( $html, '/files/report.pdf' );
		$external = link_attributes( $html, 'https://outside.test/archive.ZIP?download=1#top' );
		$other    = link_attributes( $html, '/download?file=report.pdf' );
		expect( 'true' === $internal['data-wzlw-download'] && null === $internal['data-wzlw-external'], 'Internal download link was not classified as a download.' );
		expect( 'true' === $external['data-wzlw-download'] && 'true' === $external['data-wzlw-external'], 'External download link was not classified as both external and downloadable.' );
		expect( null === $other['data-wzlw-download'] && null === $other['data-wzlw-url'], 'A file extension in a query string was treated as a download.' );
	};
	$tests['download extension settings are normalized and respected'] = static function () {
		settings( array( 'download_extensions' => ' .PDF, csv ' ) );
		$html = process( '<a href="/files/report.pdf">PDF</a><a href="/files/data.CSV">CSV</a><a href="/files/archive.zip">ZIP</a>' );
		expect( 'true' === link_attributes( $html, '/files/report.pdf' )['data-wzlw-download'], 'Configured PDF extension was not matched.' );
		expect( 'true' === link_attributes( $html, '/files/data.CSV' )['data-wzlw-download'], 'Configured CSV extension was not matched.' );
		expect( null === link_attributes( $html, '/files/archive.zip' )['data-wzlw-download'], 'Unconfigured extension was matched.' );
	};
	$tests['excluded domains still suppress configured downloads'] = static function () {
		settings( array( 'excluded_domains' => 'trusted.test' ) );
		$html = process( '<a href="https://trusted.test/report.pdf">PDF</a>' );
		$link = link_attributes( $html, 'https://trusted.test/report.pdf' );
		expect( 'true' === $link['data-wzlw-excluded'] && null === $link['data-wzlw-download'], 'Excluded download link was processed.' );
	};
	$tests['download scope external skips files on the site'] = static function () {
		settings( array( 'download_scope' => 'external' ) );
		$html = process(
			'<a href="/wp-content/uploads/a.pdf">root-relative</a>'
			. '<a href="https://site.test/wp-content/uploads/b.pdf">absolute</a>'
			. '<a href="https://outside.test/c.pdf">external</a>'
			. '<a class="wzlw-affiliate" href="/go/d.pdf">affiliate</a>'
			. '<a class="wzlw-force-external" href="/e.pdf">forced</a>'
		);
		expect( false === strpos( (string) link_attributes( $html, '/wp-content/uploads/a.pdf' )['class'], 'wzlw-processed' ), 'Root-relative file link was processed.' );
		expect( false === strpos( (string) link_attributes( $html, 'https://site.test/wp-content/uploads/b.pdf' )['class'], 'wzlw-processed' ), 'Same-site file link was processed.' );
		expect( 'true' === link_attributes( $html, 'https://outside.test/c.pdf' )['data-wzlw-download'], 'External file link lost its download warning.' );
		expect( 'true' === link_attributes( $html, '/go/d.pdf' )['data-wzlw-download'], 'Affiliate file link lost its download warning.' );
		expect( 'true' === link_attributes( $html, '/e.pdf' )['data-wzlw-download'], 'Force-external file link lost its download warning.' );

		settings( array( 'download_scope' => 'all' ) );
		$html = process( '<a href="/wp-content/uploads/a.pdf">root-relative</a>' );
		expect( 'true' === link_attributes( $html, '/wp-content/uploads/a.pdf' )['data-wzlw-download'], 'Download scope "all" skipped a site file.' );
	};
	$tests['warnings can be disabled for logged-in users and roles'] = static function () {
		$content = '<a href="https://outside.test/role/" target="_blank">go</a>';
		$cases   = array(
			array( 'logged_in', array(), false ),
			array( 'logged_in', array( 'subscriber' ), true ),
			array( 'role:administrator', array( 'subscriber' ), false ),
			array( 'role:administrator', array( 'editor', 'administrator' ), true ),
			array( '', array( 'administrator' ), false ),
		);
		foreach ( $cases as $case ) {
			list( $disable_for, $roles, $disabled ) = $case;
			settings(
				array(
					'disable_for'              => $disable_for,
					'link_attributes_external' => array( 'nofollow' ),
				)
			);
			$GLOBALS['wzlw_test_roles'] = $roles;
			$html                       = process( $content );
			$attrs                      = link_attributes( $html, 'https://outside.test/role/' );
			$label                      = '"' . $disable_for . '" with roles [' . implode( ',', $roles ) . ']';
			expect( $disabled === ( null === $attrs['data-wzlw-external'] ), 'Wrong warning state for ' . $label . '.' );
			expect( $disabled === ( false === strpos( $html, 'wzlw-icon' ) ), 'Wrong indicator state for ' . $label . '.' );
			expect( 'nofollow' === $attrs['rel'], 'Link attributes were not applied for ' . $label . '.' );
		}
	};
	function rules( array $rows ) {
		$rules = array();
		foreach ( $rows as $index => $row ) {
			$rules[] = array(
				'row_id' => 'rule' . $index,
				'fields' => array(
					'pattern' => $row[0],
					'action'  => $row[1],
					'message' => $row[2] ?? '',
				),
			);
		}
		return $rules;
	}
	$tests['link rule patterns normalize and match as in the shared fixture'] = static function () {
		$cases = json_decode( (string) file_get_contents( __DIR__ . '/fixtures/link-rules.json' ), true );
		expect( is_array( $cases ) && count( $cases ) > 40, 'Link rule fixture is missing.' );
		foreach ( $cases as $case ) {
			$rules = Link_Rules::get_rules( array( 'link_rules' => rules( array( array( $case['pattern'], 'modal' ) ) ) ) );
			expect( 1 === count( $rules ), 'Rule was dropped: ' . $case['pattern'] );
			expect( $case['normalized'] === $rules[0]['pattern'] && $case['hostOnly'] === $rules[0]['host_only'], 'Normalized form changed for ' . $case['pattern'] . ': ' . $rules[0]['pattern'] );
			$start   = microtime( true );
			$matched = null !== Link_Rules::match( $case['href'], 'site.test', $rules );
			expect( $case['matches'] === $matched, 'Pattern ' . $case['pattern'] . ' gave the wrong result for ' . substr( $case['href'], 0, 60 ) . '.' );
			expect( microtime( true ) - $start < 0.5, 'Matching was slow for ' . $case['pattern'] . '.' );
		}
	};
	$tests['invalid link rules are dropped'] = static function () {
		$rules = Link_Rules::get_rules( array( 'link_rules' => rules( array( array( '', 'modal' ), array( 'example.com', 'bogus' ), array( '  ', 'exclude' ), array( 'ok.test', 'exclude' ) ) ) ) );
		expect( 1 === count( $rules ) && 'ok.test' === $rules[0]['pattern'], 'Invalid rules were kept.' );
		expect( array() === Link_Rules::get_rules( array( 'link_rules' => 'not an array' ) ), 'A malformed rules setting broke get_rules().' );

		$filter = static function () {
			return array( 'junk', array( 'id' => 'x', 'action' => 'modal' ), array( 'id' => array(), 'pattern' => 'a.test', 'action' => 'modal' ), array( 'id' => 'ok', 'pattern' => '/go/*', 'action' => 'modal' ) );
		};
		add_filter( 'wzlw_link_rules', $filter );
		$rules = Link_Rules::get_rules( array() );
		remove_filter( 'wzlw_link_rules', $filter );
		expect( 1 === count( $rules ) && 'site.test/go/*' === $rules[0]['pattern'] && '' === $rules[0]['message'], 'The wzlw_link_rules filter output was not validated.' );
		expect( null === Link_Rules::match( '/internal/', 'site.test', $rules ), 'A filtered rule matched an unrelated link.' );
	};
	$tests['link rules set the method per link and the first match wins'] = static function () {
		settings(
			array(
				'warning_method'   => 'inline',
				'excluded_domains' => 'trusted.test',
				'link_rules'       => rules(
					array(
						array( 'outside.test/first/*', 'modal', 'First rule' ),
						array( 'outside.test', 'redirect' ),
						array( '/go/*', 'inline_modal' ),
						array( 'trusted.test', 'modal' ),
						array( 'skip.test', 'exclude' ),
					)
				),
			)
		);
		$html = process(
			'<a href="https://outside.test/first/a">first</a>'
			. '<a href="https://outside.test/other">second</a>'
			. '<a href="/go/product/">cloaked</a>'
			. '<a href="https://trusted.test/">trusted</a>'
			. '<a href="https://skip.test/">skipped</a>'
			. '<a class="wzlw-force-external" href="https://skip.test/forced">forced</a>'
			. '<a href="https://plain.test/">plain</a>'
		);
		$first   = link_attributes( $html, 'https://outside.test/first/a' );
		$second  = link_attributes( $html, 'https://outside.test/other' );
		$cloaked = link_attributes( $html, '/go/product/' );
		$trusted = link_attributes( $html, 'https://trusted.test/' );
		$skipped = link_attributes( $html, 'https://skip.test/' );
		$forced  = link_attributes( $html, 'https://skip.test/forced' );
		$plain   = link_attributes( $html, 'https://plain.test/' );

		expect( 'modal' === $first['data-wzlw-method'] && 'rule0' === $first['data-wzlw-rule'], 'First matching rule did not win.' );
		expect( 'redirect' === $second['data-wzlw-method'], 'Domain rule did not apply.' );
		expect( 'inline_modal' === $cloaked['data-wzlw-method'] && '/go/product/' === $cloaked['data-wzlw-url'], 'Root-relative rule did not process an internal link.' );
		expect( 'modal' === $trusted['data-wzlw-method'] && null === $trusted['data-wzlw-excluded'], 'A rule did not override the excluded domains list.' );
		expect( 'true' === $trusted['data-wzlw-external'], 'A rule-matched excluded domain was not treated as external.' );
		expect( 'true' === $skipped['data-wzlw-excluded'] && null === $skipped['data-wzlw-url'], 'Exclude rule did not exclude the link.' );
		expect( null === $forced['data-wzlw-excluded'], 'Force-external class did not override an exclude rule.' );
		expect( null === $plain['data-wzlw-method'] && null === $plain['data-wzlw-url'], 'An unmatched link did not use the global inline method.' );

		$markup = static function ( $href ) use ( $html ) {
			preg_match( '#<a[^>]*href="' . preg_quote( $href, '#' ) . '"[^>]*>.*?</a>#s', $html, $match );
			return $match[0] ?? '';
		};
		expect( false === strpos( $markup( 'https://outside.test/first/a' ), 'wzlw-icon' ), 'A modal-only rule link got an inline icon.' );
		expect( false !== strpos( $markup( '/go/product/' ), 'wzlw-icon' ), 'An inline rule link got no icon.' );
		expect( false !== strpos( $markup( 'https://plain.test/' ), 'wzlw-icon' ), 'The global inline method stopped adding icons.' );
	};
	$tests['inline rules add indicators when the global method has none'] = static function () {
		settings(
			array(
				'warning_method' => 'modal',
				'link_rules'     => rules( array( array( 'inline.test', 'inline' ) ) ),
			)
		);
		$html = process( '<a href="https://inline.test/">rule</a><a href="https://modal.test/">global</a>' );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'Inline rule and global modal produced the wrong number of icons.' );
		expect( 'true' === link_attributes( $html, 'https://modal.test/' )['data-wzlw-external'], 'Global modal link lost its attributes.' );

		$second = process( $html );
		expect( 1 === substr_count( $second, 'class="wzlw-icon"' ), 'A second pass duplicated the rule indicator.' );
	};
	$tests['the redirect screen takes its rule from the signed destination only'] = static function () {
		settings(
			array(
				'warning_method' => 'inline',
				'link_rules'     => rules( array( array( 'msg.test', 'redirect', 'Custom message' ), array( 'modal.test', 'modal', 'Modal message' ) ) ),
			)
		);
		$handler = ( new \ReflectionClass( Redirect_Handler::class ) )->newInstanceWithoutConstructor();
		$method  = new \ReflectionMethod( Redirect_Handler::class, 'get_redirect_rule' );
		$method->setAccessible( true );
		expect( 'Custom message' === ( $method->invoke( $handler, 'https://msg.test/page' )['message'] ?? null ), 'Redirect rule was not found for its destination.' );
		expect( null === $method->invoke( $handler, 'https://modal.test/' ), 'A non-redirect rule enabled the redirect screen.' );
		expect( null === $method->invoke( $handler, 'https://evil.example/phish' ), 'An unmatched destination enabled the redirect screen.' );

		$html = process( '<a href="https://msg.test/">msg</a>' );
		expect( false === strpos( $html, 'wzlw_rule' ), 'The redirect URL still carries a rule parameter.' );
	};
	$tests['backslash and control characters cannot hide an external link'] = static function () {
		settings( array( 'excluded_domains' => 'trusted.test' ) );
		$html = process( '<a href="/\\evil.test/x">a</a><a href="https:\\\\evil.test/y">b</a><a href="https://evil.test\\@trusted.test/z">c</a>' );
		expect( 3 === substr_count( $html, 'data-wzlw-external="true"' ), 'A backslash URL was not treated as external: ' . $html );
		expect( false === strpos( $html, 'data-wzlw-excluded' ), 'A backslash URL matched an excluded domain.' );
	};
	$tests['indicators read target and method from the opening tag only'] = static function () {
		settings( array( 'warning_method' => 'inline' ) );
		$html = process( '<a href="https://outside.test/?target=_blank">same tab</a><a href="https://outside.test/b">set target=_blank here</a>' );
		expect( false === strpos( $html, 'screen-reader-text' ), 'Text resembling target=_blank added new-window screen reader text.' );
		$html = process( '<a data-x-data-wzlw-method="modal" href="https://outside.test/c">c</a>' );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'A look-alike attribute changed the link method.' );
	};
	$tests['author-written rule attributes are removed'] = static function () {
		settings( array( 'warning_method' => 'modal' ) );
		$attrs = link_attributes( process( '<a data-wzlw-method="inline" data-wzlw-rule="x" href="https://outside.test/">go</a>' ), 'https://outside.test/' );
		expect( null === $attrs['data-wzlw-method'] && null === $attrs['data-wzlw-rule'] && 'true' === $attrs['data-wzlw-external'], 'Author-written rule attributes survived.' );

		settings( array( 'disable_for' => 'logged_in', 'link_rules' => rules( array( array( 'outside.test', 'modal' ) ) ) ) );
		$GLOBALS['wzlw_test_roles'] = array( 'subscriber' );
		$attrs = link_attributes( process( '<a data-wzlw-method="modal" data-wzlw-rule="rule0" href="https://outside.test/">go</a>' ), 'https://outside.test/' );
		expect( null === $attrs['data-wzlw-method'] && null === $attrs['data-wzlw-rule'], 'Rule attributes survived for a user with warnings disabled.' );
	};
	$tests['download indicators use a distinct icon class'] = static function () {
		settings();
		$html = process( '<a href="/files/report.pdf">PDF</a><a href="https://outside.test/page">External</a>' );
		expect( 1 === substr_count( $html, 'wzlw-download-icon' ), 'Download link did not receive its distinct icon.' );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'External link icon markup changed unexpectedly.' );
	};
	$tests['external content filters reuse the content processor'] = static function () {
		settings();
		$content = '<a href="https://outside.test/source/">external link</a>';
		$results = array(
			'widget_block_content'       => apply_filters( 'widget_block_content', $content, array(), null ),
			'widget_text'                => apply_filters( 'widget_text', $content, array( 'visual' => false, 'filter' => false ), (object) array( 'id_base' => 'text' ) ),
			'widget_text_content'        => apply_filters( 'widget_text_content', $content, array(), null ),
			'widget_custom_html_content' => apply_filters( 'widget_custom_html_content', $content, array(), (object) array( 'id_base' => 'custom_html' ) ),
			'wp_nav_menu'                => apply_filters( 'wp_nav_menu', $content, (object) array() ),
			'comment_text'               => apply_filters( 'comment_text', $content, null, array() ),
			'render_block'               => apply_filters( 'render_block', $content, array( 'blockName' => 'core/template-part' ) ),
		);

		foreach ( $results as $hook => $result ) {
			expect( 'true' === link_attributes( $result, 'https://outside.test/source/' )['data-wzlw-external'], $hook . ' did not process the link.' );
		}

		$ordinary_block = apply_filters( 'render_block', $content, array( 'blockName' => 'core/paragraph' ) );
		expect( $content === $ordinary_block, 'render_block processed a non-template-part block.' );
	};
	$tests['external content toggles disable their individual filters'] = static function () {
		$cases = array(
			'process_widgets'        => array( 'widget_block_content', array(), null ),
			'process_nav_menus'      => array( 'wp_nav_menu', (object) array() ),
			'process_comments'       => array( 'comment_text', null, array() ),
			'process_template_parts' => array( 'render_block', array( 'blockName' => 'core/template-part' ) ),
		);

		foreach ( $cases as $setting_id => $arguments ) {
			settings( array( $setting_id => 0 ) );
			$content = '<a href="https://outside.test/disabled/">external link</a>';
			$hook    = array_shift( $arguments );
			$result  = apply_filters( $hook, $content, ...$arguments );
			expect( $content === $result, $setting_id . ' did not disable ' . $hook . '.' );
		}
	};
	$tests['external content processing is idempotent'] = static function () {
		settings();
		$content = '<a href="https://outside.test/repeated/">external link</a>';
		$block   = array( 'blockName' => 'core/template-part' );
		$first   = apply_filters( 'render_block', $content, $block );
		$second  = apply_filters( 'render_block', $first, $block );
		expect( 1 === substr_count( $second, 'wzlw-processed' ), 'A processed link was classified more than once.' );
		expect( 1 === substr_count( $second, 'class="wzlw-icon"' ), 'A processed link received duplicate indicators.' );

		settings( array( 'visual_indicator' => 'none' ) );
		$content = '<a href="https://outside.test/repeated/" target="_blank">external link</a>';
		$first   = apply_filters( 'render_block', $content, $block );
		$second = apply_filters( 'render_block', $first, $block );
		expect( 1 === substr_count( $second, 'screen-reader-text' ), 'A screen-reader-only indicator was duplicated.' );
	};
	$tests['the marker class is not an opt-out for content authors'] = static function () {
		settings( array( 'link_attributes_external' => array( 'nofollow', 'target_blank', 'noopener' ) ) );
		$html  = process( '<a class="wzlw-processed" href="https://outside.test/marker/">go</a>' );
		$attrs = link_attributes( $html, 'https://outside.test/marker/' );
		expect( 'true' === $attrs['data-wzlw-external'], 'An author-supplied marker class suppressed the warning attributes.' );
		expect( 'nofollow noopener' === $attrs['rel'], 'An author-supplied marker class suppressed the configured rel attributes.' );
		expect( 1 === substr_count( (string) $attrs['class'], 'wzlw-processed' ), 'The marker class was duplicated.' );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'An author-supplied marker class changed the indicator markup.' );
	};
	$tests['repeated classification does not duplicate classes or ARIA labels'] = static function () {
		settings();
		$content = '<a aria-label="Product page" href="https://outside.test/repeat/" target="_blank">go</a>';
		$block   = array( 'blockName' => 'core/template-part' );
		$first   = apply_filters( 'render_block', $content, $block );
		$second  = apply_filters( 'render_block', $first, $block );
		$attrs   = link_attributes( $second, 'https://outside.test/repeat/' );
		expect( 'wzlw-processed wzlw-external' === $attrs['class'], 'Classes were duplicated on a second pass: ' . $attrs['class'] );
		expect( 'Product page, Opens in a new window' === $attrs['aria-label'], 'The ARIA label was appended twice: ' . $attrs['aria-label'] );
	};
	function expect_external_content_untouched( $context ) {
		$content = '<a href="https://outside.test/context/">external link</a>';
		expect( $content === apply_filters( 'comment_text', $content, null, array() ), 'comment_text was processed during ' . $context . '.' );
		expect( $content === apply_filters( 'render_block', $content, array( 'blockName' => 'core/template-part' ) ), 'render_block was processed during ' . $context . '.' );
		expect( $content === apply_filters( 'widget_block_content', $content, array(), null ), 'widget_block_content was processed during ' . $context . '.' );
		expect( $content === apply_filters( 'wp_nav_menu', $content, (object) array() ), 'wp_nav_menu was processed during ' . $context . '.' );
	}
	$tests['external content filters are skipped in admin requests'] = static function () {
		settings();
		$GLOBALS['wzlw_test_is_admin'] = true;
		expect_external_content_untouched( 'an admin request' );
	};
	$tests['existing screen-reader markup does not suppress visual indicators'] = static function () {
		settings();
		$content = '<a href="https://outside.test/custom/">external link<span class="screen-reader-text">Existing context</span></a>';
		$html    = process( $content );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'Existing screen-reader markup suppressed the plugin indicator.' );
	};
	foreach ( array( 'external', 'both' ) as $scope ) {
		$tests[ 'filtered exclusions survive PHP processing: ' . $scope ] = static function () use ( $scope ) {
			settings( array( 'scope' => $scope ) );
			$filter = static function ( $domains, $host ) {
				return 'outside.test' === $host ? array( 'outside.test' ) : $domains;
			};
			add_filter( 'wzlw_excluded_domains', $filter, 10, 2 );
			try {
				$html = process( '<a href="https://outside.test/" target="_blank" aria-label="Example">test</a>' );
				$link = link_attributes( $html, 'https://outside.test/' );
				expect( 'true' === $link['data-wzlw-excluded'], 'PHP exclusion decision was not preserved.' );
				expect( null === $link['data-wzlw-url'] && null === $link['data-wzlw-blank'], 'Excluded link still has warning attributes.' );
			} finally {
				remove_filter( 'wzlw_excluded_domains', $filter, 10 );
			}
		};
	}
	$tests['wildcard exclusions and forced affiliate precedence'] = static function () {
		settings(
			array(
				'scope'                     => 'both',
				'excluded_domains'          => '*.outside.test',
				'link_attributes_affiliate' => array( 'sponsored' ),
			)
		);
		$html = process( '<a href="//sub.outside.test/" target="_blank">excluded</a><a href="//outside.test/">base</a><a class="wzlw-affiliate" href="https://sub.outside.test/">affiliate</a>' );
		expect( 'true' === link_attributes( $html, '//sub.outside.test/' )['data-wzlw-excluded'], 'Wildcard exclusion failed.' );
		expect( 'true' === link_attributes( $html, '//outside.test/' )['data-wzlw-external'], 'Wildcard unexpectedly excluded its base domain.' );
		$affiliate = link_attributes( $html, 'https://sub.outside.test/' );
		expect( 'true' === $affiliate['data-wzlw-external'] && 'sponsored' === $affiliate['rel'], 'Affiliate did not override exclusion.' );
	};
	foreach ( array( 'script', 'style', 'textarea', 'title', 'iframe', 'noembed', 'noframes', 'xmp' ) as $tag ) {
		foreach ( array( 'wzlw-no-icon-wrapper', 'wzlw-force-external-wrapper', 'wzlw-affiliate-wrapper' ) as $wrapper ) {
			$tests[ 'atomic element does not leak wrapper state: ' . $tag . '/' . $wrapper ] = static function () use ( $tag, $wrapper ) {
				settings( array( 'link_attributes_affiliate' => array( 'sponsored' ) ) );
				$html = process( '<div class="' . $wrapper . '"><' . $tag . '>text</' . $tag . '><p><a href="/inside/">inside</a></p></div><p><a href="/after/">after</a><a href="https://outside.test/">external</a></p>' );
				expect( null === link_attributes( $html, '/after/' )['data-wzlw-external'], 'Wrapper classification leaked to a following link.' );
				$outside = link_attributes( $html, 'https://outside.test/' );
				expect( false === strpos( (string) $outside['class'], 'wzlw-no-icon' ), 'Suppression leaked past the wrapper.' );
				expect( null === $outside['rel'], 'Affiliate attributes leaked past the wrapper.' );
				$atomic_wrapper = process( '<' . $tag . ' class="' . $wrapper . '">text</' . $tag . '><a href="https://outside.test/">external</a>' );
				$outside        = link_attributes( $atomic_wrapper, 'https://outside.test/' );
				expect( false === strpos( (string) $outside['class'], 'wzlw-no-icon' ) && null === $outside['rel'], 'An atomic wrapper affected later links.' );
			};
		}
	}
	$tests['nested ordinary wrappers still work']                              = static function () {
		settings();
		$html = process( '<div class="wzlw-no-icon-wrapper"><section><img src="image.png"><a href="https://inside.test/">inside</a></section></div><a href="https://outside.test/">outside</a>' );
		expect( false !== strpos( (string) link_attributes( $html, 'https://inside.test/' )['class'], 'wzlw-no-icon' ), 'Nested suppression stopped working.' );
		expect( false === strpos( (string) link_attributes( $html, 'https://outside.test/' )['class'], 'wzlw-no-icon' ), 'Nested suppression leaked.' );
	};
	$tests['shortcode-generated atomic content does not leak suppression']     = static function () {
		settings();
		$html = process( '<div class="wzlw-no-icon-wrapper">[wzlw_test_form]</div><a href="https://outside.test/">outside</a>' );
		expect( false === strpos( (string) link_attributes( $html, 'https://outside.test/' )['class'], 'wzlw-no-icon' ), 'Shortcode content leaked suppression.' );
	};
	$tests['redirect endpoint URL validation'] = static function () {
		$cases = array(
			'//outside.test/page'      => true,
			'//outside.test/'          => true,
			'//site.test/page'         => true,
			'//SITE.test/page'         => true,
			'/go/product/'             => true,
			'https://outside.test/'    => true,
			'https://site.test/'       => true,
			'http:evil.test'           => false,
			'javascript:alert(1)'      => false,
			'http://site.test/forced/' => true,
			'mailto:a@site.test'       => false,
		);
		foreach ( $cases as $url => $expected ) {
			expect( $expected === is_valid_url( $url ), 'Wrong validity for ' . $url . ' (expected ' . var_export( $expected, true ) . ').' );
		}
	};
	$tests['protocol-relative links survive the full redirect round trip'] = static function () {
		settings( array( 'warning_method' => 'redirect', 'scope' => 'both' ) );
		$html = process( '<a href="//outside.test/page?a=1&b=2">external</a>' );
		$link = link_attributes( $html, '//outside.test/page?a=1&b=2' );
		expect( 'true' === $link['data-wzlw-external'], 'Network-path reference was not marked external.' );
		$signed = Redirect_Handler::get_redirect_url( '//outside.test/page?a=1&b=2' );
		parse_str( (string) wp_parse_url( $signed, PHP_URL_QUERY ), $query );
		$received = esc_url_raw( $query['url'] ?? '' );
		expect( is_valid_url( $received ), 'Signed network-path destination was rejected by the endpoint.' );
		expect( hash_equals( hash_hmac( 'sha256', $received, wp_salt( 'auth' ) ), $query['wzlw_sig'] ?? '' ), 'Signature mismatch for network-path destination.' );
	};
	$tests['every signed link in redirect mode is accepted by the endpoint'] = static function () {
		settings( array( 'warning_method' => 'redirect', 'scope' => 'both' ) );
		$html = process(
			'<a class="wzlw-force-external" href="https://site.test/forced/">forced internal</a>'
			. '<div class="wzlw-force-external-wrapper"><a href="https://site.test/in-wrapper/">wrapped internal</a></div>'
			. '<a href="https://site.test/blank/" target="_blank">internal blank</a>'
			. '<a href="//outside.test/proto">proto external</a>'
			. '<a href="https://outside.test/?a=1&b=2">external query</a>'
		);
		$processor = new \WP_HTML_Tag_Processor( $html );
		$checked   = 0;
		while ( $processor->next_tag( 'A' ) ) {
			$signed = $processor->get_attribute( 'data-wzlw-redirect-url' );
			if ( null === $signed ) {
				continue;
			}
			++$checked;
			parse_str( (string) wp_parse_url( $signed, PHP_URL_QUERY ), $query );
			$received = esc_url_raw( $query['url'] ?? '' );
			expect( is_valid_url( $received ), 'Endpoint rejects a URL it signed: ' . $processor->get_attribute( 'href' ) );
			expect( hash_equals( hash_hmac( 'sha256', $received, wp_salt( 'auth' ) ), $query['wzlw_sig'] ?? '' ), 'Signature mismatch for ' . $processor->get_attribute( 'href' ) );
		}
		expect( 5 === $checked, 'Expected 5 signed links, saw ' . $checked . '.' );
	};
	$tests['uppercase closing tags receive indicators and screen-reader text'] = static function () {
		settings();
		$html = process( wp_kses_post( '<A href="https://outside.test/" target="_blank">test</A>' ) );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'Uppercase link has no icon.' );
		expect( 1 === substr_count( $html, 'class="screen-reader-text"' ), 'Uppercase link has no screen-reader text.' );
		$html = process( wp_kses_post( '<A class="wzlw-no-icon" href="https://outside.test/" target="_blank">test</A>' ) );
		expect( false === strpos( $html, 'class="wzlw-icon"' ) && 1 === substr_count( $html, 'class="screen-reader-text"' ), 'Suppressed uppercase link lost its screen-reader text.' );
	};
	$tests['same-tab links get no new-window screen reader text'] = static function () {
		foreach ( array( 'icon', 'text', 'both', 'none' ) as $visual ) {
			settings( array( 'visual_indicator' => $visual ) );
			$html = process( '<a aria-label="Docs" href="https://outside.test/same/">same</a><a href="https://outside.test/icon/"><svg></svg></a><a class="wzlw-no-icon" href="https://outside.test/quiet/">quiet</a>' );
			expect( false === strpos( $html, 'screen-reader-text' ), 'Same-tab link received new-window screen reader text with the ' . $visual . ' indicator.' );
			expect( 'Docs' === link_attributes( $html, 'https://outside.test/same/' )['aria-label'], 'Same-tab link ARIA label was changed with the ' . $visual . ' indicator.' );
			expect( 'true' === link_attributes( $html, 'https://outside.test/same/' )['data-wzlw-external'], 'Same-tab link lost its warning attributes.' );
		}
		settings();
		$html = process( '<a href="https://outside.test/same/">same</a>' );
		expect( 1 === substr_count( $html, 'class="wzlw-icon"' ), 'Same-tab external link lost its icon.' );
		$html = process( '<a href="https://outside.test/new/" target="_blank">new</a><a href="https://outside.test/icon/" target="_blank"><svg></svg></a>' );
		expect( 2 === substr_count( $html, 'class="screen-reader-text"' ), 'New-tab links lost their screen reader text.' );
	};
	$tests['no-icon target detection accepts single-quoted attributes'] = static function () {
		settings();
		$html = process( "<a class='wzlw-no-icon' href='https://outside.test/' target='_blank'>test</a>" );
		expect( false === strpos( $html, 'class="wzlw-icon"' ) && 1 === substr_count( $html, 'class="screen-reader-text"' ), 'Single-quoted no-icon link lost its screen-reader text.' );
	};

	// Registered last: REST_REQUEST is a constant and cannot be unset for later tests.
	$tests['external content filters are skipped in REST requests'] = static function () {
		settings();
		define( 'REST_REQUEST', true );
		expect_external_content_untouched( 'a REST request' );
	};

	$failed = 0;
	foreach ( $tests as $name => $test ) {
		try {
			$test();
			echo 'PASS ', $name, PHP_EOL;
		} catch ( \Throwable $error ) {
			++$failed;
			echo 'FAIL ', $name, ': ', $error->getMessage(), PHP_EOL;
		}
	}
	echo count( $tests ) - $failed, '/', count( $tests ), ' passed', PHP_EOL;
	exit( $failed ? 1 : 0 );
}
