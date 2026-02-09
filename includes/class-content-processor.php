<?php
/**
 * Content Processor class.
 *
 * Processes content to add accessibility features to links.
 *
 * @package WebberZone\Better_External_Links
 * @since 1.0.0
 */

namespace WebberZone\Better_External_Links;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WebberZone\Better_External_Links\Util\Hook_Registry;

/**
 * Content Processor class.
 *
 * @since 1.0.0
 */
class Content_Processor {

	/**
	 * Plugin settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $settings;

	/**
	 * Current site hostname.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $site_host;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->site_host = wp_parse_url( home_url(), PHP_URL_HOST );

		Hook_Registry::add_filter( 'the_content', array( $this, 'process_content' ), 999 );
		Hook_Registry::add_filter( 'the_excerpt', array( $this, 'process_content' ), 999 );
	}

	/**
	 * Process content to add accessibility features.
	 *
	 * @since 1.0.0
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public function process_content( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		// Check if current post type is enabled.
		if ( ! $this->is_post_type_enabled() ) {
			return $content;
		}

		$this->settings = wzbel_get_settings();

		// Use WP_HTML_Tag_Processor to parse links.
		$processor = new \WP_HTML_Tag_Processor( $content );

		while ( $processor->next_tag( array( 'tag_name' => 'a' ) ) ) {
			$href   = $processor->get_attribute( 'href' );
			$target = $processor->get_attribute( 'target' );

			// Skip if no href.
			if ( empty( $href ) ) {
				continue;
			}

			// Determine if link should be processed.
			$is_external    = $this->is_external_link( $href );
			$has_target     = '_blank' === $target;
			$should_process = $this->should_process_link( $is_external, $has_target );

			if ( ! $should_process ) {
				continue;
			}

			// Add data attributes for JavaScript handling.
			if ( in_array( $this->settings['warning_method'], array( 'modal', 'inline_modal', 'redirect', 'inline_redirect' ), true ) ) {
				if ( $is_external ) {
					$processor->set_attribute( 'data-wz-ela-external', 'true' );
					$processor->set_attribute( 'data-wz-ela-url', esc_url( $href ) );
					$processor->set_attribute( 'data-wz-ela-redirect-url', esc_url( Redirect_Handler::get_redirect_url( $href ) ) );
				}
			}

			// Add ARIA attributes for accessibility.
			$aria_label = $this->get_aria_label( $processor->get_attribute( 'aria-label' ) );
			if ( $aria_label ) {
				$processor->set_attribute( 'aria-label', $aria_label );
			}

			// Add class for styling.
			$existing_class = $processor->get_attribute( 'class' );
			$new_class      = trim( $existing_class . ' wz-ela-processed' );
			if ( $is_external ) {
				$new_class .= ' wz-ela-external';
			}
			$processor->set_attribute( 'class', $new_class );
		}

		$processed_content = $processor->get_updated_html();

		// Add visual indicators if inline method is used.
		if ( in_array( $this->settings['warning_method'], array( 'inline', 'inline_modal', 'inline_redirect' ), true ) ) {
			$processed_content = $this->add_visual_indicators( $processed_content );
		}

		return $processed_content;
	}

	/**
	 * Add visual indicators to processed links.
	 *
	 * @since 1.0.0
	 * @param string $content Processed content.
	 * @return string Content with visual indicators.
	 */
	private function add_visual_indicators( $content ) {
		// Use regex to find processed links and add indicators before closing tag.
		$pattern = '/<a\s+[^>]*class="[^"]*wz-ela-processed[^"]*"[^>]*>(.*?)<\/a>/is';

		$content = preg_replace_callback(
			$pattern,
			array( $this, 'add_indicator_to_link' ),
			$content
		);

		return $content;
	}

	/**
	 * Add indicator to a single link.
	 *
	 * @since 1.0.0
	 * @param array $matches Regex matches.
	 * @return string Modified link HTML.
	 */
	private function add_indicator_to_link( $matches ) {
		$link_html = $matches[0];
		$indicator = $this->get_visual_indicator();

		if ( empty( $indicator ) ) {
			return $link_html;
		}

		// Insert indicator before closing </a> tag.
		$link_html = str_replace( '</a>', $indicator . '</a>', $link_html );

		return $link_html;
	}

	/**
	 * Get visual indicator HTML.
	 *
	 * @since 1.0.0
	 * @return string Indicator HTML.
	 */
	private function get_visual_indicator() {
		$visual = isset( $this->settings['visual_indicator'] ) ? $this->settings['visual_indicator'] : 'icon';

		if ( 'none' === $visual ) {
			return $this->get_screen_reader_text();
		}

		$indicator = '';

		// Add screen reader text.
		$indicator .= $this->get_screen_reader_text();

		// Add visual elements.
		if ( 'icon' === $visual || 'both' === $visual ) {
			$indicator .= '<span class="wz-ela-icon" aria-hidden="true">↗</span>';
		}

		if ( 'text' === $visual || 'both' === $visual ) {
			$text       = isset( $this->settings['indicator_text'] ) ? $this->settings['indicator_text'] : __( '(opens in new window)', 'better-external-links' );
			$indicator .= '<span class="wz-ela-text" aria-hidden="true">' . esc_html( $text ) . '</span>';
		}

		return $indicator;
	}

	/**
	 * Get screen reader text.
	 *
	 * @since 1.0.0
	 * @return string Screen reader HTML.
	 */
	private function get_screen_reader_text() {
		$text = isset( $this->settings['screen_reader_text'] ) ? $this->settings['screen_reader_text'] : __( 'Opens in a new window', 'better-external-links' );
		return '<span class="screen-reader-text">' . esc_html( $text ) . '</span>';
	}

	/**
	 * Get ARIA label for link.
	 *
	 * @since 1.0.0
	 * @param string|null $existing_label Existing ARIA label.
	 * @return string|null ARIA label.
	 */
	private function get_aria_label( $existing_label ) {
		$screen_reader_text = $this->settings['screen_reader_text'] ?? __( 'Opens in a new window', 'better-external-links' );

		if ( $existing_label ) {
			return $existing_label . ', ' . $screen_reader_text;
		}

		return null; // Let the screen reader text span handle it.
	}

	/**
	 * Check if link is external.
	 *
	 * @since 1.0.0
	 * @param string $url URL to check.
	 * @return bool True if external.
	 */
	private function is_external_link( $url ) {
		// Handle relative URLs.
		if ( 0 === strpos( $url, '/' ) || 0 === strpos( $url, '#' ) || 0 === strpos( $url, '?' ) ) {
			return false;
		}

		// Parse URL.
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$link_host = $parsed_url['host'];

		// Check if it's the same as site host.
		if ( $link_host === $this->site_host ) {
			return false;
		}

		// Check excluded domains.
		$excluded_domains = $this->settings['excluded_domains'] ?? '';

		if ( is_string( $excluded_domains ) ) {
			$excluded_domains = array_filter( array_map( 'trim', explode( "\n", $excluded_domains ) ) );
		}

		/**
		 * Filter the excluded domains.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $excluded_domains Array of excluded domains.
		 * @param string $link_host        The link host being checked.
		 */
		$excluded_domains = apply_filters( 'wz_bel_excluded_domains', $excluded_domains, $link_host );

		foreach ( $excluded_domains as $domain ) {
			if ( false !== strpos( $link_host, $domain ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if link should be processed.
	 *
	 * @since 1.0.0
	 * @param bool $is_external Whether link is external.
	 * @param bool $has_target  Whether link has target="_blank".
	 * @return bool True if should be processed.
	 */
	private function should_process_link( $is_external, $has_target ) {
		$scope = isset( $this->settings['scope'] ) ? $this->settings['scope'] : 'external';

		switch ( $scope ) {
			case 'external':
				return $is_external;

			case 'both':
				return $is_external || $has_target;

			default:
				return $is_external;
		}
	}

	/**
	 * Check if current post type is enabled.
	 *
	 * @since 1.0.0
	 * @return bool True if enabled.
	 */
	private function is_post_type_enabled() {
		if ( ! is_singular() ) {
			return false;
		}

		$settings = wzbel_get_settings();
		$enabled  = $settings['enabled_post_types'] ?? array( 'post', 'page' );

		if ( is_string( $enabled ) ) {
			$enabled = array_filter( array_map( 'trim', explode( ',', $enabled ) ) );
		}
		$current_type = get_post_type();

		return in_array( $current_type, $enabled, true );
	}
}
