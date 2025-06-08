const mix = require('laravel-mix');
const fs = require('fs');

/**
 * Mix JS
 * @type {string}
 */
const srcJS = 'src/js/';
const pluginJS = 'assets/js/app.min.js';

let JsFiles = [];
let files = fs.readdirSync(srcJS);
files.forEach(function (filename) {
	if (!(/(^|\/)\.[^\/.]/g).test(filename)) {
		JsFiles.push(srcJS + '/' + filename);
	}
});

mix.js(JsFiles, pluginJS)
	.sourceMaps();

/**
 * Mix SCSS
 * @type {string}
 */
const srcScss = 'src/scss/app.scss';
const pluginCss = 'assets/css/app.min.css';

mix.sass(srcScss, pluginCss, {
	sassOptions: {
		outputStyle: "compressed",
	}
}).sourceMaps();

/**
 * Browser Sync
 */
if (process.env.NODE_ENV === 'development' && fs.existsSync(`./browser-sync-config.json`)) {

	// Get json from file
	let contents = fs.readFileSync(`./browser-sync-config.json`);

	// Parse json
	let settings = JSON.parse(contents);

	// Init browser sync
	mix.browserSync(settings);
}