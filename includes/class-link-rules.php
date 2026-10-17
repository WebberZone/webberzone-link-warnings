<?php
/**
 * Link Rules class.
 *
 * Matches links against the URL pattern rules set under Settings > Rules.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.7.0
 */

namespace WebberZone\Link_Warnings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Link Rules class.
 *
 * Patterns use `*` as a wildcard and nothing else. Matching uses a plain wildcard
 * matcher rather than regular expressions, so it cannot backtrack catastrophically,
 * and every step is mirrored exactly in modal.js: normalize_href() and
 * normalizeRuleHref(), get_subject() and getRuleSubject(), wildcard_match() and
 * wildcardMatch(). Patterns and subjects are lowercased in ASCII only, so PHP and
 * JavaScript always fold case the same way.
 *
 * @since 1.7.0
 */
class Link_Rules {

	/**
	 * Actions a rule can take.
	 *
	 * @since 1.7.0
	 * @var string[]
	 */
	const ACTIONS = array( 'inline', 'modal', 'inline_modal', 'redirect', 'inline_redirect', 'exclude' );

	/**
	 * Get the normalized rules from the settings.
	 *
	 * @since 1.7.0
	 * @param array|null $settings Plugin settings. Defaults to the saved settings.
	 * @return array[] Rules in order, each with id, pattern, action, message and host_only keys.
	 */
	public static function get_rules( $settings = null ) {
		if ( null === $settings ) {
			$settings = wzlw_get_settings();
		}

		$rows  = isset( $settings['link_rules'] ) && is_array( $settings['link_rules'] ) ? $settings['link_rules'] : array();
		$rules = array();

		foreach ( array_values( $rows ) as $index => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$fields  = isset( $row['fields'] ) && is_array( $row['fields'] ) ? $row['fields'] : array();
			$rules[] = array(
				'id'      => (string) ( $row['row_id'] ?? 'rule_' . $index ),
				'pattern' => (string) ( $fields['pattern'] ?? '' ),
				'action'  => (string) ( $fields['action'] ?? '' ),
				'message' => (string) ( $fields['message'] ?? '' ),
			);
		}

		/**
		 * Filter the link rules before they are validated.
		 *
		 * Each rule is an array with id, pattern, action and optional message keys.
		 * Patterns use the same syntax as the Rules tab. Invalid rules are dropped.
		 *
		 * @since 1.7.0
		 *
		 * @param array[] $rules    Rules in order. First match wins.
		 * @param array   $settings Plugin settings.
		 */
		$rules = apply_filters( 'wzlw_link_rules', $rules, $settings );

		return self::validate( is_array( $rules ) ? $rules : array() );
	}

	/**
	 * Validate and normalize rules.
	 *
	 * @since 1.7.0
	 * @param array $rules Raw rules.
	 * @return array[] Valid rules.
	 */
	private static function validate( array $rules ) {
		$site_host = self::ascii_lower( rtrim( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.' ) );
		$valid     = array();

		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) || ! is_scalar( $rule['pattern'] ?? null ) || ! is_scalar( $rule['action'] ?? null ) ) {
				continue;
			}

			$pattern = self::normalize_pattern( (string) $rule['pattern'], $site_host );
			$action  = (string) $rule['action'];
			$id      = sanitize_key( is_scalar( $rule['id'] ?? null ) ? (string) $rule['id'] : '' );

			if ( '' === $pattern || '' === $id || ! in_array( $action, self::ACTIONS, true ) ) {
				continue;
			}

			$valid[] = array(
				'id'        => $id,
				'pattern'   => $pattern,
				'action'    => $action,
				'message'   => is_scalar( $rule['message'] ?? null ) ? trim( (string) $rule['message'], " \t\n\r\0\x0B" ) : '',
				'host_only' => false === strpos( $pattern, '/' ),
			);
		}

		return $valid;
	}

	/**
	 * Find the first rule that matches a link.
	 *
	 * @since 1.7.0
	 * @param string  $href      Link href.
	 * @param string  $site_host Site hostname, used to resolve root-relative links.
	 * @param array[] $rules     Rules from get_rules().
	 * @return array|null The matching rule, or null.
	 */
	public static function match( $href, $site_host, array $rules ) {
		if ( empty( $rules ) ) {
			return null;
		}

		$subject = self::get_subject( (string) $href, (string) $site_host );
		if ( null === $subject ) {
			return null;
		}

		foreach ( $rules as $rule ) {
			$target = $rule['host_only'] ? $subject['host'] : $subject['host'] . $subject['path'];
			if ( self::wildcard_match( $rule['pattern'], $target ) ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * Check whether any rule uses one of the given actions.
	 *
	 * @since 1.7.0
	 * @param string[]     $actions Actions to look for.
	 * @param array[]|null $rules   Rules from get_rules(). Defaults to the saved rules.
	 * @return bool True if a rule uses one of the actions.
	 */
	public static function uses_action( array $actions, $rules = null ) {
		if ( null === $rules ) {
			$rules = self::get_rules();
		}

		foreach ( $rules as $rule ) {
			if ( in_array( $rule['action'], $actions, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the rules in the shape the JavaScript scan expects.
	 *
	 * @since 1.7.0
	 * @param array[] $rules Rules from get_rules().
	 * @return array[] Rules for wp_localize_script().
	 */
	public static function to_js( array $rules ) {
		return array_values(
			array_map(
				static function ( $rule ) {
					return array(
						'id'       => $rule['id'],
						'pattern'  => $rule['pattern'],
						'hostOnly' => $rule['host_only'],
						'action'   => $rule['action'],
						'message'  => $rule['message'],
					);
				},
				$rules
			)
		);
	}

	/**
	 * Normalize a pattern as entered in the settings.
	 *
	 * Strips whitespace, any http(s) scheme, a port and a fragment, collapses repeated
	 * wildcards and lowercases ASCII letters. A pattern starting with `/` is
	 * root-relative and gets the site host. A query without a path gets one, so
	 * `example.com?ref=*` matches `example.com/?ref=...`.
	 *
	 * @since 1.7.0
	 * @param string $pattern   Pattern.
	 * @param string $site_host Site hostname, lowercased.
	 * @return string Normalized pattern, or an empty string.
	 */
	public static function normalize_pattern( $pattern, $site_host ) {
		$pattern = (string) preg_replace( '/\s+/', '', $pattern );
		$pattern = (string) preg_replace( '#^(?:https?:)?//#i', '', $pattern );
		$pattern = (string) preg_replace( '/\*+/', '*', $pattern );
		$pattern = self::ascii_lower( $pattern );

		$hash = strpos( $pattern, '#' );
		if ( false !== $hash ) {
			$pattern = substr( $pattern, 0, $hash );
		}

		if ( '' === $pattern ) {
			return '';
		}

		if ( '/' === $pattern[0] ) {
			$pattern = $site_host . $pattern;
		}

		$split = strcspn( $pattern, '/?' );
		$host  = rtrim( (string) preg_replace( '/:\d*$/', '', substr( $pattern, 0, $split ) ), '.' );
		$rest  = (string) substr( $pattern, $split );

		if ( '' === $host ) {
			return '';
		}

		if ( '' !== $rest && '?' === $rest[0] ) {
			$rest = '/' . $rest;
		}

		return $host . $rest;
	}

	/**
	 * Normalize an href the way browsers do before it is classified.
	 *
	 * Removes tabs and newlines, trims control characters and spaces, and turns
	 * backslashes before the query into slashes, so `/\evil.test` and
	 * `https:\\evil.test` are seen as the external links browsers open.
	 *
	 * @since 1.7.0
	 * @param string $href Link href.
	 * @return string Normalized href.
	 */
	public static function normalize_href( $href ) {
		$href = str_replace( array( "\t", "\n", "\r" ), '', (string) $href );
		$href = trim( $href, "\x00..\x20" );
		$cut  = strcspn( $href, '?#' );

		return str_replace( '\\', '/', substr( $href, 0, $cut ) ) . substr( $href, $cut );
	}

	/**
	 * Build the strings a link is matched against.
	 *
	 * Relative links without a leading slash, fragments, query-only links and
	 * non-http schemes are not matched.
	 *
	 * @since 1.7.0
	 * @param string $href      Link href.
	 * @param string $site_host Site hostname.
	 * @return array|null Array with lowercased host and path keys, or null when the link cannot be matched.
	 */
	public static function get_subject( $href, $site_host ) {
		$href = self::normalize_href( $href );

		if ( 0 === strpos( $href, '//' ) ) {
			$rest = substr( $href, 2 );
		} elseif ( preg_match( '#^https?://#i', $href ) ) {
			$rest = (string) preg_replace( '#^https?://#i', '', $href );
		} elseif ( 0 === strpos( $href, '/' ) ) {
			$rest = $site_host . $href;
		} else {
			return null;
		}

		$hash = strpos( $rest, '#' );
		if ( false !== $hash ) {
			$rest = substr( $rest, 0, $hash );
		}

		$split = strcspn( $rest, '/?' );
		$host  = substr( $rest, 0, $split );
		$path  = (string) substr( $rest, $split );

		$at = strrpos( $host, '@' );
		if ( false !== $at ) {
			$host = substr( $host, $at + 1 );
		}
		$host = self::ascii_lower( rtrim( (string) preg_replace( '/:\d*$/', '', $host ), '.' ) );

		if ( '' === $host ) {
			return null;
		}

		if ( '' === $path || '?' === $path[0] ) {
			$path = '/' . $path;
		}

		return array(
			'host' => $host,
			'path' => self::ascii_lower( $path ),
		);
	}

	/**
	 * Match a subject against a pattern where `*` matches any characters.
	 *
	 * Greedy matching with a single backtrack point, so it runs in
	 * O(pattern × subject) time at worst.
	 *
	 * @since 1.7.0
	 * @param string $pattern Normalized pattern.
	 * @param string $subject Subject.
	 * @return bool True if the whole subject matches.
	 */
	public static function wildcard_match( $pattern, $subject ) {
		$p_len = strlen( $pattern );
		$s_len = strlen( $subject );
		$p     = 0;
		$s     = 0;
		$star  = -1;
		$mark  = 0;

		while ( $s < $s_len ) {
			if ( $p < $p_len && '*' !== $pattern[ $p ] && $pattern[ $p ] === $subject[ $s ] ) {
				++$p;
				++$s;
			} elseif ( $p < $p_len && '*' === $pattern[ $p ] ) {
				$star = $p++;
				$mark = $s;
			} elseif ( -1 !== $star ) {
				$p = $star + 1;
				$s = ++$mark;
			} else {
				return false;
			}
		}

		while ( $p < $p_len && '*' === $pattern[ $p ] ) {
			++$p;
		}

		return $p === $p_len;
	}

	/**
	 * Lowercase ASCII letters only, whatever the locale.
	 *
	 * @since 1.7.0
	 * @param string $text Text.
	 * @return string Lowercased text.
	 */
	private static function ascii_lower( $text ) {
		return strtr( $text, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz' );
	}
}
