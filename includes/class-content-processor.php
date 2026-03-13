<?php
/**
 * Content Processor class.
 *
 * Processes content to add accessibility features to links.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.0.0
 */

namespace WebberZone\Link_Warnings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WebberZone\Link_Warnings\Util\Hook_Registry;
use WebberZone\Link_Warnings\Util\Icon_Helper;

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

		// Load fresh settings on each content processing.
		$this->settings = wzlw_get_settings();

		// Use WP_HTML_Tag_Processor to parse links.
		$processor  = new \WP_HTML_Tag_Processor( $content );
		$skip_depth = 0;

		while ( $processor->next_tag() ) {
			if ( $skip_depth > 0 ) {
				$skip_depth += $this->get_skip_depth_delta( $processor );

				if ( 0 >= $skip_depth ) {
					$skip_depth = 0;
				}

				continue;
			}

			if ( $this->is_skip_wrapper_tag( $processor ) ) {
				$skip_depth = 1;
				continue;
			}

			if ( 'A' !== $processor->get_tag() ) {
				continue;
			}

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
			if ( in_array( $this->settings['warning_method'] ?? 'none', array( 'modal', 'inline_modal', 'redirect', 'inline_redirect' ), true ) ) {
				if ( $is_external ) {
					$processor->set_attribute( 'data-wzlw-external', 'true' );
					$processor->set_attribute( 'data-wzlw-url', esc_url( $href ) );
					$processor->set_attribute( 'data-wzlw-redirect-url', esc_url( Redirect_Handler::get_redirect_url( $href ) ) );
				}
			}

			// Add ARIA attributes for accessibility.
			$aria_label = $this->get_aria_label( $processor->get_attribute( 'aria-label' ) );
			if ( $aria_label ) {
				$processor->set_attribute( 'aria-label', $aria_label );
			}

			// Add class for styling.
			$existing_class = $processor->get_attribute( 'class' );
			$new_class      = trim( $existing_class . ' wzlw-processed' );
			if ( $is_external ) {
				$new_class .= ' wzlw-external';
			}
			$processor->set_attribute( 'class', $new_class );
		}

		$processed_content = $processor->get_updated_html();

		// Add visual indicators if inline method is used.
		if ( in_array( $this->settings['warning_method'] ?? 'none', array( 'inline', 'inline_modal', 'inline_redirect' ), true ) ) {
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
		$pattern = '/<a\s+[^>]*class="[^"]*wzlw-processed[^"]*"[^>]*>(.*?)<\/a>/is';

		$content = preg_replace_callback(
			$pattern,
			array( $this, 'add_indicator_to_link' ),
			$content
		);

		return $content;
	}

	/**
	 * Check if the current tag starts a wrapper that should skip processing.
	 *
	 * @since 1.1.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return bool True if the tag is a skip wrapper.
	 */
	private function is_skip_wrapper_tag( \WP_HTML_Tag_Processor $processor ) {
		if ( $processor->is_tag_closer() ) {
			return false;
		}

		$class_name = $processor->get_attribute( 'class' );

		if ( ! is_string( $class_name ) || '' === $class_name ) {
			return false;
		}

		return $this->has_skip_wrapper_class( $class_name );
	}

	/**
	 * Check if a class attribute contains the skip wrapper class.
	 *
	 * @since 1.1.0
	 * @param string $class_name Class attribute value.
	 * @return bool True if the class is present.
	 */
	private function has_skip_wrapper_class( $class_name ) {
		$classes = preg_split( '/\s+/', trim( $class_name ) );

		if ( ! is_array( $classes ) ) {
			return false;
		}

		return in_array( 'wzlw-no-icon-wrapper', $classes, true );
	}

	/**
	 * Get the nesting delta for skipped wrapper traversal.
	 *
	 * @since 1.1.0
	 * @param \WP_HTML_Tag_Processor $processor HTML tag processor instance.
	 * @return int Nesting delta.
	 */
	private function get_skip_depth_delta( \WP_HTML_Tag_Processor $processor ) {
		if ( $processor->is_tag_closer() ) {
			return -1;
		}

		if ( $this->tag_is_void( $processor->get_tag() ) ) {
			return 0;
		}

		return 1;
	}

	/**
	 * Check whether a tag is a void element.
	 *
	 * @since 1.1.0
	 * @param string|null $tag_name Tag name.
	 * @return bool True if the tag is a void element.
	 */
	private function tag_is_void( $tag_name ) {
		if ( ! is_string( $tag_name ) || '' === $tag_name ) {
			return false;
		}

		return in_array(
			strtoupper( $tag_name ),
			array( 'AREA', 'BASE', 'BR', 'COL', 'EMBED', 'HR', 'IMG', 'INPUT', 'LINK', 'META', 'SOURCE', 'TRACK', 'WBR' ),
			true
		);
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

		// Check if link has wzlw-no-icon class.
		if ( strpos( $link_html, 'wzlw-no-icon' ) !== false ) {
			return $link_html;
		}

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
		$visual = $this->settings['visual_indicator'] ?? 'icon';

		if ( 'none' === $visual ) {
			return $this->get_screen_reader_text();
		}

		$indicator = '';

		// Add screen reader text.
		$indicator .= $this->get_screen_reader_text();

		// Add visual elements.
		if ( 'icon' === $visual || 'both' === $visual ) {
			// Icon is added via CSS ::before pseudo-element using CSS variable.
			$indicator .= '<span class="wzlw-icon" aria-hidden="true"></span>';
		}

		if ( 'text' === $visual || 'both' === $visual ) {
			$text       = $this->settings['indicator_text'] ?? __( '(opens in new window)', 'webberzone-link-warnings' );
			$indicator .= '<span class="wzlw-text" aria-hidden="true">' . esc_html( $text ) . '</span>';
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
		$text = $this->settings['screen_reader_text'] ?? __( 'Opens in a new window', 'webberzone-link-warnings' );
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
		$screen_reader_text = $this->settings['screen_reader_text'] ?? __( 'Opens in a new window', 'webberzone-link-warnings' );

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
		$excluded_domains = apply_filters( 'wzlw_excluded_domains', $excluded_domains, $link_host ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

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

		$settings = wzlw_get_settings();
		$enabled  = $settings['enabled_post_types'] ?? array( 'post', 'page' );

		if ( is_string( $enabled ) ) {
			$enabled = array_filter( array_map( 'trim', explode( ',', $enabled ) ) );
		}
		$current_type = get_post_type();

		return in_array( $current_type, $enabled, true );
	}
}
