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
 * Patterns use `*` as a wildcard and nothing else. Each pattern is compiled into a
 * regular expression built only from escaped literal characters and `.*`, which
 * is valid and behaves identically in PCRE and JavaScript. The JavaScript scan
 * receives the compiled expressions rather than recompiling the patterns, so both
 * layers always match the same links.
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
	 * A pattern starting with `/` is root-relative and is matched against this site only.
	 *
	 * @since 1.7.0
	 * @param array|null $settings Plugin settings. Defaults to the saved settings.
	 * @return array[] Rules in order, each with id, pattern, action, message, regex and host_only keys.
	 */
	public static function get_rules( $settings = null ) {
		if ( null === $settings ) {
			$settings = wzlw_get_settings();
		}

		$site_host = strtolower( rtrim( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.' ) );

		$rows  = isset( $settings['link_rules'] ) && is_array( $settings['link_rules'] ) ? $settings['link_rules'] : array();
		$rules = array();

		foreach ( array_values( $rows ) as $index => $row ) {
			$fields  = isset( $row['fields'] ) && is_array( $row['fields'] ) ? $row['fields'] : array();
			$pattern = self::normalize_pattern( (string) ( $fields['pattern'] ?? '' ) );
			$action  = (string) ( $fields['action'] ?? '' );

			if ( '' === $pattern || ! in_array( $action, self::ACTIONS, true ) ) {
				continue;
			}

			if ( '/' === $pattern[0] ) {
				$pattern = $site_host . $pattern;
			}

			$host_only = false === strpos( $pattern, '/' );

			$rules[] = array(
				'id'        => sanitize_key( (string) ( $row['row_id'] ?? 'rule_' . $index ) ),
				'pattern'   => $pattern,
				'action'    => $action,
				'message'   => trim( (string) ( $fields['message'] ?? '' ) ),
				'regex'     => self::compile( $pattern ),
				'host_only' => $host_only,
			);
		}

		/**
		 * Filter the link rules.
		 *
		 * Each rule must keep the regex built by Link_Rules::compile() for its pattern.
		 *
		 * @since 1.7.0
		 *
		 * @param array[] $rules    Rules in order. First match wins.
		 * @param array   $settings Plugin settings.
		 */
		return (array) apply_filters( 'wzlw_link_rules', $rules, $settings );
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
			$target = ! empty( $rule['host_only'] ) ? $subject['host'] : $subject['host'] . $subject['path'];
			if ( preg_match( '/' . $rule['regex'] . '/i', $target ) ) {
				return $rule;
			}
		}

		return null;
	}

	/**
	 * Check whether any rule uses one of the given actions.
	 *
	 * @since 1.7.0
	 * @param string[]     $actions  Actions to look for.
	 * @param array[]|null $rules    Rules from get_rules(). Defaults to the saved rules.
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
	 * Find a rule by its ID.
	 *
	 * @since 1.7.0
	 * @param string       $id    Rule ID.
	 * @param array[]|null $rules Rules from get_rules(). Defaults to the saved rules.
	 * @return array|null The rule, or null.
	 */
	public static function get_rule( $id, $rules = null ) {
		if ( '' === (string) $id ) {
			return null;
		}

		if ( null === $rules ) {
			$rules = self::get_rules();
		}

		foreach ( $rules as $rule ) {
			if ( $rule['id'] === $id ) {
				return $rule;
			}
		}

		return null;
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
						'regex'    => $rule['regex'],
						'hostOnly' => (bool) $rule['host_only'],
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
	 * Strips whitespace and any http(s) scheme, and collapses repeated wildcards.
	 *
	 * @since 1.7.0
	 * @param string $pattern Pattern.
	 * @return string Normalized pattern.
	 */
	public static function normalize_pattern( $pattern ) {
		$pattern = preg_replace( '/\s+/', '', $pattern );
		$pattern = preg_replace( '#^(?:https?:)?//#i', '', (string) $pattern );
		$pattern = preg_replace( '/\*+/', '*', (string) $pattern );

		return (string) $pattern;
	}

	/**
	 * Compile a normalized pattern into a regular expression body.
	 *
	 * ASCII punctuation is escaped with a backslash, which both PCRE and JavaScript
	 * (without the u flag) read as the literal character. `*` becomes `.*`.
	 *
	 * @since 1.7.0
	 * @param string $pattern Normalized pattern.
	 * @return string Regular expression body, anchored at both ends, without delimiters.
	 */
	public static function compile( $pattern ) {
		$regex  = '';
		$length = strlen( $pattern );

		for ( $i = 0; $i < $length; $i++ ) {
			$char = $pattern[ $i ];

			if ( '*' === $char ) {
				$regex .= '.*';
			} elseif ( ord( $char ) < 128 && ! preg_match( '/[A-Za-z0-9]/', $char ) ) {
				$regex .= '\\' . $char;
			} else {
				$regex .= $char;
			}
		}

		return '^' . $regex . '$';
	}

	/**
	 * Build the string a link is matched against.
	 *
	 * Works on the raw href with string operations only, mirrored exactly by
	 * getRuleSubject() in modal.js. Relative links without a leading slash, fragments,
	 * query-only links and non-http schemes are not matched.
	 *
	 * @since 1.7.0
	 * @param string $href      Link href.
	 * @param string $site_host Site hostname.
	 * @return array|null Array with host and path keys, or null when the link cannot be matched.
	 */
	public static function get_subject( $href, $site_host ) {
		$href = trim( $href );

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
		$host = strtolower( rtrim( (string) preg_replace( '/:\d*$/', '', $host ), '.' ) );

		if ( '' === $host ) {
			return null;
		}

		if ( '' === $path || '?' === $path[0] ) {
			$path = '/' . $path;
		}

		return array(
			'host' => $host,
			'path' => $path,
		);
	}
}
