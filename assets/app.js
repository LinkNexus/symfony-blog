import './bootstrap.js';
import { Alert } from 'bootstrap';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import './styles/external.css';
import './styles/responsive.css';
import './styles/main.css';

document.addEventListener('DOMContentLoaded', function () {

    // Change the welcome text on the login page depending on the screen size
    const loginDescription = document.querySelector('.login-text-container p'),
        initialLoginText = 'Nexus is a technology platform for beginners, researchers, etc.\n' +
        'The goal here is to help each other by sharing our knowledge\n' +
        'on the various technology fields. Nexus is here to bring us closer!',
        newLoginText = 'Connect with other Technology Fans around the World!',
        breakpoint = 925;

    function updateLoginText() {
        if (null !== loginDescription) {
            if (window.innerWidth <= breakpoint) {
                loginDescription.textContent = newLoginText;
            } else {
                loginDescription.textContent = initialLoginText;
            }
        }
    }

    // Initial Check
    updateLoginText();

    // Making sure the login text changes on window resize
    window.addEventListener('resize', updateLoginText);
})

