/**
 * Build Assets Script
 *
 * This script builds the CSS and JS assets for the plugin.
 * It minifies CSS files, creates RTL versions, and minifies JS files.
 *
 * @package WebberZone\Link_Warnings
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

// Configuration
const config = {
	css: {
		inputDir: './includes/css',
		outputDir: './includes/css',
		files: [
			'admin.css',
			'frontend.css',
		],
	},
	js: {
		inputDir: './includes/js',
		outputDir: './includes/js',
		files: [
			'admin.js',
			'frontend.js',
		],
	},
};

// Ensure directories exist
function ensureDirExists(dirPath) {
	if (!fs.existsSync(dirPath)) {
		fs.mkdirSync(dirPath, { recursive: true });
	}
}

// Minify CSS file
function minifyCss(inputPath, outputPath) {
	try {
		execSync(`cleancss -o ${outputPath} ${inputPath}`, { stdio: 'inherit' });
		console.log(`✓ Minified CSS: ${path.basename(outputPath)}`);
	} catch (error) {
		console.error(`✗ Failed to minify CSS: ${error.message}`);
	}
}

// Create RTL version of CSS
function createRtlCss(inputPath, outputPath) {
	try {
		execSync(`rtlcss ${inputPath} ${outputPath}`, { stdio: 'inherit' });
		console.log(`✓ Created RTL CSS: ${path.basename(outputPath)}`);
	} catch (error) {
		console.error(`✗ Failed to create RTL CSS: ${error.message}`);
	}
}

// Minify JS file
function minifyJs(inputPath, outputPath) {
	try {
		execSync(`terser ${inputPath} -o ${outputPath} -c -m`, { stdio: 'inherit' });
		console.log(`✓ Minified JS: ${path.basename(outputPath)}`);
	} catch (error) {
		console.error(`✗ Failed to minify JS: ${error.message}`);
	}
}

// Build CSS assets
function buildCssAssets() {
	console.log('\nBuilding CSS assets...');

	config.css.files.forEach(file => {
		const baseName = path.basename(file, '.css');
		const inputPath = path.join(config.css.inputDir, file);
		const minPath = path.join(config.css.outputDir, `${baseName}.min.css`);
		const rtlPath = path.join(config.css.outputDir, `${baseName}-rtl.css`);
		const rtlMinPath = path.join(config.css.outputDir, `${baseName}-rtl.min.css`);

		// Minify original file
		if (fs.existsSync(inputPath)) {
			minifyCss(inputPath, minPath);
		}

		// Create RTL version
		if (fs.existsSync(inputPath)) {
			createRtlCss(inputPath, rtlPath);
		}

		// Minify RTL version
		if (fs.existsSync(rtlPath)) {
			minifyCss(rtlPath, rtlMinPath);
		}
	});
}

// Build JS assets
function buildJsAssets() {
	console.log('\nBuilding JS assets...');

	config.js.files.forEach(file => {
		const baseName = path.basename(file, '.js');
		const inputPath = path.join(config.js.inputDir, file);
		const minPath = path.join(config.js.outputDir, `${baseName}.min.js`);

		// Minify file
		if (fs.existsSync(inputPath)) {
			minifyJs(inputPath, minPath);
		}
	});
}

// Main build process
function build() {
	console.log('🚀 Building WebberZone Link Warnings assets...');

	// Ensure output directories exist
	ensureDirExists(config.css.outputDir);
	ensureDirExists(config.js.outputDir);

	// Build assets
	buildCssAssets();
	buildJsAssets();

	console.log('\n✅ Build completed!');
}

// Run the build
build();
