<?php
/**
 * Build assets script.
 *
 * Processes CSS and JS files: minification and RTL generation.
 * Supports passing specific files or directories as arguments.
 *
 * @package WebberZone\Better_External_Links
 */

// Basic setup.
if ( ! defined( 'WZ_BEL_BUILD_DIR' ) ) {
	define( 'WZ_BEL_BUILD_DIR', dirname( __DIR__ ) );
}
require_once WZ_BEL_BUILD_DIR . '/vendor/autoload.php';

use MatthiasMullie\Minify;
use Irmmr\RTLCss\Parser;
use Sabberworm\CSS\Parser as CSSParser;

/**
 * Configuration for asset processing.
 */
$config = array(
	// Directories to exclude from processing.
	'excludeDirs'                 => array(
		'node_modules',
		'vendor',
		'freemius',
		'build',
		'.git',
		'includes/blocks',
		'includes/frontend/blocks',
		'includes/pro/blocks',
		'dev-helpers',
	),
	// File patterns to exclude from discovery.
	'excludePatterns'             => array(
		'/-rtl\.css$/',
		'/^build-assets\.php$/',
		'/^build-assets\.js$/',
	),
	// If true, minify source files before combining (results in smaller bundles).
	'minifyBeforeCombine'         => false,
	// If true, keep individual .min versions of combined output files.
	'createMinifiedCombinedFiles' => true,
	// CSS files to combine - order matters!
	// Example: 'path/to/output.css' => array( 'path/to/source1.css', 'path/to/source2.css' ).
	'combineCss'                  => array(),
	// JS files to combine - order matters!
	// Example: 'path/to/output.js' => array( 'path/to/source1.js', 'path/to/source2.js' ).
	'combineJs'                   => array(),
);

/**
 * Track errors for final exit code.
 *
 * @var int
 */
$error_count = 0;

/**
 * Normalise file paths.
 *
 * @param string $path File path to normalise.
 * @return string Normalised relative path.
 */
function normalize_path( $path ) {
	return str_replace( array( '\\', WZ_BEL_BUILD_DIR . '/' ), array( '/', '' ), $path );
}

/**
 * Check if a path should be excluded.
 *
 * @param string $path File path to check.
 * @return bool True if should be excluded.
 */
function should_exclude( $path ) {
	global $config;
	$relative_path = normalize_path( $path );

	foreach ( $config['excludeDirs'] as $exclude_dir ) {
		if ( 0 === strpos( $relative_path . '/', $exclude_dir . '/' ) ) {
			return true;
		}
	}

	foreach ( $config['excludePatterns'] as $pattern ) {
		if ( preg_match( $pattern, $relative_path ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Recursively find files.
 *
 * @param string $dir       Directory to search.
 * @param string $extension Extension to match.
 * @return array List of files.
 */
function find_files( $dir, $extension ) {
	$file_list = array();

	if ( ! is_dir( $dir ) ) {
		return $file_list;
	}

	$files = scandir( $dir );
	if ( false === $files ) {
		return $file_list;
	}

	foreach ( $files as $file ) {
		if ( '.' === $file || '..' === $file ) {
			continue;
		}

		$path = $dir . '/' . $file;

		if ( should_exclude( $path ) ) {
			continue;
		}

		if ( is_dir( $path ) ) {
			$file_list = array_merge( $file_list, find_files( $path, $extension ) );
		} elseif ( substr( $path, -strlen( $extension ) ) === $extension ) {
			$file_list[] = $path;
		}
	}

	return $file_list;
}

/**
 * Minify a CSS file.
 *
 * @param string $input_file  Input file path.
 * @param string $output_file Output file path.
 * @return void
 */
function minify_css( $input_file, $output_file ) {
	global $error_count;
	try {
		$minifier = new Minify\CSS( $input_file );
		/**
		 * Status of minification.
		 *
		 * @var string|bool $status
		 */
		$status = $minifier->minify( $output_file );
		if ( false === $status ) {
			echo '  ✗ Error writing ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			++$error_count;
		} else {
			echo '  ✓ Minified CSS: ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	} catch ( Exception $e ) {
		echo '  ✗ Error minifying ' . normalize_path( $input_file ) . ': ' . $e->getMessage() . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		++$error_count;
	}
}

/**
 * Minify a JS file.
 *
 * Minifies a JS file using MatthiasMullie\Minify.
 *
 * @param string $input_file  Input file path.
 * @param string $output_file Output file path.
 * @return void
 */
function minify_js( $input_file, $output_file ) {
	global $error_count;
	try {
		$minifier = new Minify\JS( $input_file );
		/**
		 * Status of minification.
		 *
		 * @var string|bool $status
		 */
		$status = $minifier->minify( $output_file );
		if ( false === $status ) {
			echo '  ✗ Error writing ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			++$error_count;
		} else {
			echo '  ✓ Minified JS: ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	} catch ( Exception $e ) {
		echo '  ✗ Error minifying ' . normalize_path( $input_file ) . ': ' . $e->getMessage() . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		++$error_count;
	}
}

/**
 * Generate RTL CSS.
 *
 * @param string $input_file  Input file path.
 * @param string $output_file Output file path.
 */
function generate_rtl( $input_file, $output_file ) {
	global $error_count;
	$content = file_get_contents( $input_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $content ) {
		echo '  ✗ Error reading ' . normalize_path( $input_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		++$error_count;
		return;
	}

	try {
		$css_parser  = new CSSParser( $content );
		$tree        = $css_parser->parse();
		$rtl_parser  = new Parser( $tree );
		$rtl_content = $rtl_parser->flip()->render();

		if ( false === file_put_contents( $output_file, $rtl_content ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			echo '  ✗ Error writing ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			++$error_count;
		} else {
			echo '  ✓ Generated RTL: ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	} catch ( Exception $e ) {
		echo '  ✗ Error generating RTL for ' . normalize_path( $input_file ) . ': ' . $e->getMessage() . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		++$error_count;
	}
}

/**
 * Combine multiple files into one.
 *
 * @param string $output_file Output file path.
 * @param array  $input_files Array of input file paths.
 * @param bool   $is_js       Whether files are JavaScript.
 */
function combine_files( $output_file, $input_files, $is_js = false ) {
	global $config, $error_count;

	echo "\nCombining files into: " . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	$combined_content = '';

	foreach ( $input_files as $file ) {
		$full_path = (string) WZ_BEL_BUILD_DIR . '/' . $file;
		if ( ! file_exists( $full_path ) ) {
			echo '  ✗ File not found: ' . $file . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			++$error_count;
			continue;
		}

		try {
			$content = file_get_contents( $full_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( false === $content ) {
				echo '  ✗ Error reading ' . $file . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				++$error_count;
				continue;
			}

			if ( $config['minifyBeforeCombine'] ) {
				if ( $is_js ) {
					$minifier = new Minify\JS( $content );
				} else {
					$minifier = new Minify\CSS( $content );
				}
				$content = $minifier->minify();
			}

			$combined_content .= "\n/* Source: " . $file . " */\n";
			$combined_content .= $content;
			$combined_content .= "\n";
			echo '  ✓ Added: ' . $file . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} catch ( Exception $e ) {
			echo '  ✗ Error processing ' . $file . ': ' . $e->getMessage() . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			++$error_count;
		}
	}

	if ( false === file_put_contents( $output_file, $combined_content ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		echo '  ✗ Error writing ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		++$error_count;
	} else {
		echo '  ✓ Created: ' . normalize_path( $output_file ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

// Parse arguments.
$args           = array_slice( $argv, 1 );
$specific_paths = array_filter(
	$args,
	function ( $arg ) {
		return 0 !== strpos( $arg, '--' );
	}
);

$all_css_files = array();
$all_js_files  = array();

if ( ! empty( $specific_paths ) ) {
	echo "=== Processing Specific Paths ===\n";
	foreach ( $specific_paths as $arg_path ) {
		$full_path = realpath( $arg_path );
		if ( ! $full_path ) {
			echo '  ✗ Path not found: ' . $arg_path . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			continue;
		}

		if ( should_exclude( $full_path ) ) {
			echo '  ✗ Skipped (excluded): ' . $arg_path . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			continue;
		}

		if ( is_dir( $full_path ) ) {
			$all_css_files = array_merge( $all_css_files, find_files( $full_path, '.css' ) );
			$all_js_files  = array_merge( $all_js_files, find_files( $full_path, '.js' ) );
		} elseif ( substr( $full_path, -4 ) === '.css' ) {
			$all_css_files[] = $full_path;
		} elseif ( substr( $full_path, -3 ) === '.js' ) {
			$all_js_files[] = $full_path;
		}
	}
} else {
	echo "=== Processing All Assets ===\n";
	$all_css_files = find_files( (string) WZ_BEL_BUILD_DIR, '.css' );
	$all_js_files  = find_files( (string) WZ_BEL_BUILD_DIR, '.js' );
}

$all_css_files = array_unique( $all_css_files );
$all_js_files  = array_unique( $all_js_files );

// Step 1: Combine files.
if ( ! empty( $config['combineCss'] ) ) {
	echo "\n=== Combining CSS Files ===\n";
	foreach ( $config['combineCss'] as $output => $inputs ) {
		combine_files( WZ_BEL_BUILD_DIR . '/' . $output, $inputs, false );
	}
}

if ( ! empty( $config['combineJs'] ) ) {
	echo "\n=== Combining JS Files ===\n";
	foreach ( $config['combineJs'] as $output => $inputs ) {
		combine_files( WZ_BEL_BUILD_DIR . '/' . $output, $inputs, true );
	}
}

// Build exclusion lists for discovery.
$combined_css_sources = empty( $config['combineCss'] )
	? array()
	: array_merge( ...array_values( $config['combineCss'] ) );

$combined_js_sources = empty( $config['combineJs'] )
	? array()
	: array_merge( ...array_values( $config['combineJs'] ) );

$combined_source_files = array_merge( $combined_css_sources, $combined_js_sources );

$combined_output_files = array_merge(
	array_keys( $config['combineCss'] ),
	array_keys( $config['combineJs'] )
);

// Filter discovery results.
$all_css_files = array_filter(
	$all_css_files,
	function ( $file ) use ( $combined_source_files, $combined_output_files, $config ) {
		$rel = normalize_path( $file );
		if ( ! empty( $combined_source_files ) && in_array( $rel, $combined_source_files, true ) ) {
			return false;
		}
		if ( ! $config['createMinifiedCombinedFiles'] && ! empty( $combined_output_files ) && in_array( $rel, $combined_output_files, true ) ) {
			return false;
		}
		return true;
	}
);

$all_js_files = array_filter(
	$all_js_files,
	function ( $file ) use ( $combined_source_files, $combined_output_files, $config ) {
		$rel = normalize_path( $file );
		if ( ! empty( $combined_source_files ) && in_array( $rel, $combined_source_files, true ) ) {
			return false;
		}
		if ( ! $config['createMinifiedCombinedFiles'] && ! empty( $combined_output_files ) && in_array( $rel, $combined_output_files, true ) ) {
			return false;
		}
		return true;
	}
);

// Processing...
if ( ! empty( $all_css_files ) ) {
	echo "\nProcessing CSS...\n";
	foreach ( $all_css_files as $css_file ) {
		if ( false !== strpos( $css_file, '.min.css' ) ) {
			continue;
		}

		$css_output = str_replace( '.css', '.min.css', $css_file );
		minify_css( $css_file, $css_output );

		// RTL.
		$rtl_output = str_replace( '.css', '-rtl.css', $css_file );
		generate_rtl( $css_file, $rtl_output );

		$rtl_min_output = str_replace( '.css', '-rtl.min.css', $css_file );
		// Generate RTL from source, then minify it separately.
		generate_rtl( $css_file, $rtl_min_output );
		minify_css( $rtl_min_output, $rtl_min_output );
	}
}

if ( ! empty( $all_js_files ) ) {
	echo "\nProcessing JS...\n";
	foreach ( $all_js_files as $js_file ) {
		if ( false !== strpos( $js_file, '.min.js' ) ) {
			continue;
		}

		$js_output = str_replace( '.js', '.min.js', $js_file );
		minify_js( $js_file, $js_output );
	}
}

echo "\n==================================\n";
if ( $error_count > 0 ) {
	echo 'Completed with ' . $error_count . " error(s).\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
} else {
	echo "Success!\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 0 );
}
