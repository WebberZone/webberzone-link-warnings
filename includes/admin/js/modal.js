/**
 * Modal functionality for External Link Accessibility plugin.
 *
 * @package WebberZone\Better_External_Links
 * @since 1.0.0
 */

(function () {
	'use strict';
	// Modal elements.
	let modal = null;
	let modalTitle = null;
	let modalMessage = null;
	let modalUrl = null;
	let modalContinue = null;
	let modalCancel = null;
	let currentLink = null;
	let focusableElements = [];
	let firstFocusable = null;
	let lastFocusable = null;

	/**
	 * Initialize modal functionality.
	 */
	function init() {
		// Always add click delegation for redirect and modal methods.
		document.addEventListener('click', handleLinkClick);

		// Get modal elements.
		modal = document.getElementById('wz-bel-modal');
		if (!modal) {
			return;
		}

		modalTitle = modal.querySelector('#wz-bel-modal-title');
		modalMessage = modal.querySelector('#wz-bel-modal-message');
		modalUrl = modal.querySelector('.wz-bel-modal-url');
		modalContinue = modal.querySelector('[data-wz-bel-continue]');
		modalCancel = modal.querySelector('.wz-bel-modal-cancel');
		// Set button text from settings.
		if (typeof wzBelSettings !== 'undefined') {
			modalTitle.textContent = wzBelSettings.modalTitle;
			modalMessage.textContent = wzBelSettings.modalMessage;
			modalContinue.textContent = wzBelSettings.continueText;
			modalCancel.textContent = wzBelSettings.cancelText;
		}

		// Modal close handlers.
		modal.querySelectorAll('[data-wz-bel-close]').forEach(function (element) {
			element.addEventListener('click', closeModal);
		});
		// Continue button handler.
		modalContinue.addEventListener('click', handleContinue);
		// Keyboard handlers.
		modal.addEventListener('keydown', handleKeydown);
	}

	/**
	 * Handle link clicks.
	 *
	 * @param {Event} e Click event.
	 */
	function handleLinkClick(e) {
		const link = e.target.closest('a[data-wz-bel-external]');
		if (!link) {
			return;
		}

		const method = typeof wzBelSettings !== 'undefined' ? wzBelSettings.warningMethod : 'inline';
		if ('redirect' === method || 'inline_redirect' === method) {
			const redirectUrl = link.getAttribute('data-wz-bel-redirect-url');
			if (redirectUrl) {
				e.preventDefault();
				window.location.href = redirectUrl;
				return;
			}
		}

		if ('modal' === method || 'inline_modal' === method) {
			// Prevent default navigation.
			e.preventDefault();
			// Store link reference.
			currentLink = link;
			// Show modal with link information.
			showModal(link);
		}
	}

	/**
	 * Show modal.
	 *
	 * @param {HTMLElement} link Link element.
	 */
	function showModal(link) {
		const url = link.getAttribute('data-wz-bel-url');
		// Update modal content.
		modalUrl.textContent = url;
		// Show modal.
		modal.removeAttribute('hidden');
		// Lock body scroll.
		document.body.classList.add('wz-bel-modal-active');
		// Set up focus trap.
		setupFocusTrap();
		// Focus first element.
		if (firstFocusable) {
			firstFocusable.focus();
		}
	}

	/**
	 * Close modal.
	 */
	function closeModal() {
		modal.setAttribute('hidden', '');
		// Unlock body scroll.
		document.body.classList.remove('wz-bel-modal-active');
		// Return focus to link.
		if (currentLink) {
			currentLink.focus();
		}
		currentLink = null;
	}

	/**
	 * Handle continue button click.
	 */
	function handleContinue() {
		if (!currentLink) {
			return;
		}

		const url = currentLink.getAttribute('data-wz-bel-url');
		// Navigate to external URL.
		window.open(url, '_blank', 'noopener,noreferrer');
		// Close modal.
		closeModal();
	}

	/**
	 * Set up focus trap.
	 */
	function setupFocusTrap() {
		// Get all focusable elements.
		focusableElements = modal.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);
		firstFocusable = focusableElements[0];
		lastFocusable = focusableElements[focusableElements.length - 1];
	}

	/**
	 * Handle keyboard events.
	 *
	 * @param {KeyboardEvent} e Keyboard event.
	 */
	function handleKeydown(e) {
		// Close on Escape.
		if ('Escape' === e.key) {
			closeModal();
			return;
		}

		// Focus trap with Tab.
		if ('Tab' === e.key) {
			if (e.shiftKey) {
				// Shift + Tab.
				if (document.activeElement === firstFocusable) {
					e.preventDefault();
					lastFocusable.focus();
				}
			} else {
				// Tab.
				if (document.activeElement === lastFocusable) {
					e.preventDefault();
					firstFocusable.focus();
				}
			}
		}
	}

	// Initialize when DOM is ready.
	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
