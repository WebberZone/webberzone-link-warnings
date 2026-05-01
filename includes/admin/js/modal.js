/**
 * Modal functionality for External Link Accessibility plugin.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.0.0
 */

(function () {
	'use strict';

	const settings = typeof wzlwSettings !== 'undefined' ? wzlwSettings : {};
	const method = settings.warningMethod || 'inline';

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
		scanNonPostLinks();

		document.addEventListener('click', handleLinkClick);

		modal = document.getElementById('wzlw-modal');
		if (!modal) {
			return;
		}

		modalTitle = modal.querySelector('#wzlw-modal-title');
		modalMessage = modal.querySelector('#wzlw-modal-message');
		modalUrl = modal.querySelector('.wzlw-modal-url-value');
		modalContinue = modal.querySelector('[data-wzlw-continue]');
		modalCancel = modal.querySelector('.wzlw-modal-cancel');

		if (settings.modalTitle) modalTitle.textContent = settings.modalTitle;
		if (settings.modalMessage) modalMessage.textContent = settings.modalMessage;
		if (settings.continueText) modalContinue.textContent = settings.continueText;
		if (settings.cancelText) modalCancel.textContent = settings.cancelText;

		modal.querySelectorAll('[data-wzlw-close]').forEach(function (element) {
			element.addEventListener('click', closeModal);
		});
		modalContinue.addEventListener('click', handleContinue);
		modal.addEventListener('keydown', handleKeydown);
	}

	// ─── DOM scan ─────────────────────────────────────────────────────────────

	/**
	 * Scan all links not already processed by PHP and apply the same rules.
	 */
	function scanNonPostLinks() {
		if (!settings.siteHost) {
			return;
		}

		const noIconWrapperClass        = settings.noIconWrapperClass || '';
		const forceExternalWrapperClass = settings.forceExternalWrapperClass || '';
		const forceExternalClass        = settings.forceExternalClass || '';
		const noIconClass               = settings.noIconClass || '';
		const isInlineMethod            = ['inline', 'inline_modal', 'inline_redirect'].includes(method);
		const needsDataAttrs            = ['modal', 'inline_modal', 'redirect', 'inline_redirect'].includes(method);
		const needsRedirectUrl          = ['redirect', 'inline_redirect'].includes(method);

		const linksNeedingRedirectUrl = [];

		document.querySelectorAll('a:not(.wzlw-processed)').forEach(function (link) {
			const href = link.getAttribute('href');
			if (!href) {
				return;
			}

			const inNoIconWrapper   = noIconWrapperClass && link.closest('.' + CSS.escape(noIconWrapperClass));
			const inForceExtWrapper = forceExternalWrapperClass && link.closest('.' + CSS.escape(forceExternalWrapperClass));
			const hasForceExtClass  = forceExternalClass && link.classList.contains(forceExternalClass);
			const hasNoIconClass    = noIconClass && link.classList.contains(noIconClass);

			const isExternal = !!(inForceExtWrapper || hasForceExtClass || isExternalHref(href));
			const hasTarget  = '_blank' === link.getAttribute('target');

			if (!shouldProcess(isExternal, hasTarget)) {
				if (inNoIconWrapper && hasTarget) {
					appendAriaLabel(link);
				}
				return;
			}

			link.classList.add('wzlw-processed');
			if (isExternal) {
				link.classList.add('wzlw-external');
			}
			if (inNoIconWrapper && noIconClass) {
				link.classList.add(noIconClass);
			}

			appendAriaLabel(link);

			if (needsDataAttrs) {
				link.setAttribute('data-wzlw-external', 'true');
				link.setAttribute('data-wzlw-url', href);
				if (needsRedirectUrl) {
					linksNeedingRedirectUrl.push(link);
				}
			}

			if (isInlineMethod) {
				const indicator = buildIndicatorHtml(!!(inNoIconWrapper || hasNoIconClass), hasTarget);
				if (indicator) {
					link.insertAdjacentHTML('beforeend', indicator);
				}
			}
		});

		if (linksNeedingRedirectUrl.length) {
			fetchRedirectUrls(linksNeedingRedirectUrl);
		}
	}

	/**
	 * Determine if a URL points to an external host.
	 *
	 * @param {string} href
	 * @return {boolean}
	 */
	function isExternalHref(href) {
		if (href.startsWith('/') || href.startsWith('#') || href.startsWith('?')) {
			return false;
		}
		try {
			const host = new URL(href, window.location.href).hostname;
			if (!host) {
				return false;
			}
			return host.toLowerCase().replace(/\.$/, '') !== settings.siteHost;
		} catch (e) {
			return false;
		}
	}

	/**
	 * Mirror PHP's should_process_link() logic.
	 *
	 * @param {boolean} isExternal
	 * @param {boolean} hasTarget
	 * @return {boolean}
	 */
	function shouldProcess(isExternal, hasTarget) {
		return 'both' === (settings.scope || 'external')
			? (isExternal || hasTarget)
			: isExternal;
	}

	/**
	 * Append screen reader text to aria-label if one already exists.
	 *
	 * @param {HTMLElement} link
	 */
	function appendAriaLabel(link) {
		const srText = settings.screenReaderText || '';
		if (!srText) {
			return;
		}
		const existing = link.getAttribute('aria-label');
		if (existing) {
			link.setAttribute('aria-label', existing + ', ' + srText);
		}
	}

	/**
	 * Build indicator HTML, mirroring PHP's get_visual_indicator() /
	 * add_indicator_to_link() logic.
	 *
	 * @param {boolean} suppress  True when the link has the no-icon class or is in a no-icon wrapper.
	 * @param {boolean} hasTarget True when the link has target="_blank".
	 * @return {string}
	 */
	function buildIndicatorHtml(suppress, hasTarget) {
		const srText = settings.screenReaderText || '';
		const srSpan = srText ? '<span class="screen-reader-text">' + escHtml(srText) + '</span>' : '';

		if (suppress) {
			return hasTarget ? srSpan : '';
		}

		const visual = settings.visualIndicator || 'icon';
		let html = srSpan;

		if ('icon' === visual || 'both' === visual) {
			html += '<span class="wzlw-icon" aria-hidden="true"></span>';
		}
		if ('text' === visual || 'both' === visual) {
			html += '<span class="wzlw-text" aria-hidden="true">' + escHtml(settings.indicatorText || '') + '</span>';
		}

		return html;
	}

	/**
	 * Minimal HTML escaping for text injected via insertAdjacentHTML.
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function escHtml(str) {
		return str
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	/**
	 * Fetch HMAC-signed redirect URLs for a batch of links via AJAX.
	 * Only used for redirect/inline_redirect methods.
	 *
	 * @param {HTMLElement[]} links
	 */
	function fetchRedirectUrls(links) {
		const formData = new FormData();
		formData.append('action', 'wzlw_sign_urls');
		formData.append('nonce', settings.nonce || '');
		links.forEach(function (link) {
			formData.append('urls[]', link.getAttribute('data-wzlw-url'));
		});

		fetch(settings.ajaxUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (!data.success) {
					return;
				}
				links.forEach(function (link) {
					const signed = data.data[link.getAttribute('data-wzlw-url')];
					if (signed) {
						link.setAttribute('data-wzlw-redirect-url', signed);
					}
				});
			})
			.catch(function () {});
	}

	// ─── Click handling ───────────────────────────────────────────────────────

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
			e.preventDefault();
			currentLink = link;
			showModal(link);
		}
	}

	// ─── Modal ────────────────────────────────────────────────────────────────

	/**
	 * Show modal.
	 *
	 * @param {HTMLElement} link Link element.
	 */
	function showModal(link) {
		const url = link.getAttribute('data-wzlw-url');
		modalUrl.textContent = url;
		if ('_blank' === link.getAttribute('target') && settings.screenReaderText) {
			modalContinue.setAttribute('aria-label', settings.continueText + ', ' + settings.screenReaderText);
		} else {
			modalContinue.removeAttribute('aria-label');
		}
		modal.removeAttribute('hidden');
		document.body.classList.add('wzlw-modal-active');
		hiddenElements = [];
		Array.from(document.body.children).forEach(function (el) {
			if (el !== modal && !el.hasAttribute('aria-hidden')) {
				el.setAttribute('aria-hidden', 'true');
				hiddenElements.push(el);
			}
		});
		setupFocusTrap();
		if (firstFocusable) {
			firstFocusable.focus();
		}
	}

	/**
	 * Close modal.
	 */
	function closeModal() {
		modal.setAttribute('hidden', '');
		document.body.classList.remove('wzlw-modal-active');
		hiddenElements.forEach(function (el) {
			el.removeAttribute('aria-hidden');
		});
		hiddenElements = [];
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
		if ('_blank' === currentLink.getAttribute('target')) {
			window.open(url, '_blank', 'noopener,noreferrer');
		} else {
			window.location.href = url;
		}
		closeModal();
	}

	/**
	 * Set up focus trap.
	 */
	function setupFocusTrap() {
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
		if ('Escape' === e.key) {
			closeModal();
			return;
		}
		if ('Tab' === e.key) {
			if (e.shiftKey) {
				if (document.activeElement === firstFocusable) {
					e.preventDefault();
					lastFocusable.focus();
				}
			} else {
				if (document.activeElement === lastFocusable) {
					e.preventDefault();
					firstFocusable.focus();
				}
			}
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
