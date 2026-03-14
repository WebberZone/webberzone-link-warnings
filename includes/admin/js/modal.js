/**
 * Modal functionality for External Link Accessibility plugin.
 *
 * @package WebberZone\Link_Warnings
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
	let hiddenElements = [];

	/**
	 * Initialize modal functionality.
	 */
	function init() {
		// Always add click delegation for redirect and modal methods.
		document.addEventListener('click', handleLinkClick);

		// Get modal elements.
		modal = document.getElementById('wzlw-modal');
		if (!modal) {
			return;
		}

		modalTitle = modal.querySelector('#wzlw-modal-title');
		modalMessage = modal.querySelector('#wzlw-modal-message');
		modalUrl = modal.querySelector('.wzlw-modal-url-value');
		modalContinue = modal.querySelector('[data-wzlw-continue]');
		modalCancel = modal.querySelector('.wzlw-modal-cancel');
		// Set button text from settings.
		if (typeof wzlwSettings !== 'undefined') {
			modalTitle.textContent = wzlwSettings.modalTitle;
			modalMessage.textContent = wzlwSettings.modalMessage;
			modalContinue.textContent = wzlwSettings.continueText;
			modalCancel.textContent = wzlwSettings.cancelText;
		}

		// Modal close handlers.
		modal.querySelectorAll('[data-wzlw-close]').forEach(function (element) {
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
		const link = e.target.closest('a[data-wzlw-external]');
		if (!link) {
			return;
		}

		const method = typeof wzlwSettings !== 'undefined' ? wzlwSettings.warningMethod : 'inline';
		if ('redirect' === method || 'inline_redirect' === method) {
			const redirectUrl = link.getAttribute('data-wzlw-redirect-url');
			if (redirectUrl) {
				e.preventDefault();
				if ('_blank' === link.getAttribute('target')) {
					window.open(redirectUrl, '_blank', 'noopener,noreferrer');
				} else {
					window.location.href = redirectUrl;
				}
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
		const url = link.getAttribute('data-wzlw-url');
		// Update modal content.
		modalUrl.textContent = url;
		// Update continue button aria-label for new window links.
		if ('_blank' === link.getAttribute('target') && typeof wzlwSettings !== 'undefined' && wzlwSettings.screenReaderText) {
			modalContinue.setAttribute('aria-label', wzlwSettings.continueText + ', ' + wzlwSettings.screenReaderText);
		} else {
			modalContinue.removeAttribute('aria-label');
		}
		// Show modal.
		modal.removeAttribute('hidden');
		// Lock body scroll.
		document.body.classList.add('wzlw-modal-active');
		// Hide background content from screen readers.
		hiddenElements = [];
		Array.from(document.body.children).forEach(function (el) {
			if (el !== modal && !el.hasAttribute('aria-hidden')) {
				el.setAttribute('aria-hidden', 'true');
				hiddenElements.push(el);
			}
		});
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
		document.body.classList.remove('wzlw-modal-active');
		// Restore background content for screen readers.
		hiddenElements.forEach(function (el) {
			el.removeAttribute('aria-hidden');
		});
		hiddenElements = [];
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

		const url = currentLink.getAttribute('data-wzlw-url');
		// Navigate to external URL, respecting the original link's target.
		if ('_blank' === currentLink.getAttribute('target')) {
			window.open(url, '_blank', 'noopener,noreferrer');
		} else {
			window.location.href = url;
		}
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
