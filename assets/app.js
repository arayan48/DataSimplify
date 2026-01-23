import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// Global loader functions
window.showLoader = function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.style.display = 'flex';
    }
};

window.hideLoader = function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.style.display = 'none';
    }
};

// Add Turbo event listeners for page navigation
document.addEventListener('turbo:visit', function() {
    window.showLoader();
});

document.addEventListener('turbo:load', function() {
    window.hideLoader();
});

document.addEventListener('turbo:render', function() {
    window.hideLoader();
});
