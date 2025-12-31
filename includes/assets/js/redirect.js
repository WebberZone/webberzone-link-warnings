/**
 * Redirect page functionality for External Link Accessibility plugin.
 *
 * @package WebberZone\Better_External_Links
 * @since 1.0.0
 */

(function () {
	'use strict';
	let countdown = 5;
	let countdownElement = null;
	let countdownInterval = null;
	/**
     * Initialize redirect functionality.
     */
    function init() {
	// Check if we have redirect data.
        if (typeof wzElaRedirect === 'undefined' || !wzElaRedirect.destination) {
	return;
}

// Get countdown element.
        countdownElement = document.querySelector('.wz-ela-countdown-number');
	if (!countdownElement) {
	return;
}

// Start countdown.
        countdown = wzElaRedirect.countdown || 5;
	startCountdown();
}

/**
     * Start countdown timer.
     */
    function startCountdown() {
	// Update initial countdown display.
        updateCountdownDisplay();
	// Set interval for countdown.
        countdownInterval = setInterval(function () {
	countdown--;
	if (countdown <= 0) {
	// Stop countdown and redirect.
                clearInterval(countdownInterval);
	redirect();
}

else {
	// Update display.
                updateCountdownDisplay();
}


}

, 1000);
}

/**
     * Update countdown display.
     */
    function updateCountdownDisplay() {
	if (countdownElement) {
	countdownElement.textContent = countdown;
}


}

/**
     * Redirect to destination.
     */
    function redirect() {
	if (wzElaRedirect.destination) {
	window.location.href = wzElaRedirect.destination;
}


}

/**
     * Cancel automatic redirect.
     */
    function cancelRedirect() {
	if (countdownInterval) {
	clearInterval(countdownInterval);
}


}

// Cancel redirect if user interacts with page.
    document.addEventListener('click', function (e) {
	// Don't cancel if clicking the continue button.
        if (e.target.closest('.wz-ela-redirect-continue')) {
	return;
}

cancelRedirect();
}

);
	document.addEventListener('keydown', function (e) {
	// Cancel on any key press except Tab.
        if ('Tab' !== e.key) {
	cancelRedirect();
}


}

);
	// Initialize when DOM is ready.
    if ('loading' === document.readyState) {
	document.addEventListener('DOMContentLoaded', init);
}

else {
	init();
}


}

)();
