/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';
import './components/navbar.js';
import './components/delete_image.js';
import './components/slider.js';
import '../js/admin.js';
import 'bootstrap';
import '@fortawesome/fontawesome-free';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

console.log('Hello my friends');