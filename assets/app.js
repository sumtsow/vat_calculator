/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

import './styles/global.scss';

// start the Stimulus application
import './bootstrap';

import 'datatables.net';
import 'datatables.net-bs5'

// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;

var dt = require('datatables.net');

require('bootstrap');

$(document).ready(function() {
	$('[data-toggle="popover"]').popover();
});