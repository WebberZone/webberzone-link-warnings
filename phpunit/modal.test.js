const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

function element(attributes = {}, innerMarkup = '') {
	const attrs = { ...attributes };
	const listeners = {};
	const classes = new Set((attrs.class || '').split(/\s+/).filter(Boolean));
	return {
		listeners,
		textContent: '',
		checked: false,
		markup: innerMarkup,
		getAttribute: (name) => name === 'class' ? [...classes].join(' ') : (attrs[name] ?? null),
		hasAttribute: (name) => Object.hasOwn(attrs, name),
		setAttribute: (name, value) => { attrs[name] = String(value); },
		removeAttribute: (name) => { delete attrs[name]; },
		classList: {
			contains: (name) => classes.has(name),
			add: (...names) => names.forEach((name) => classes.add(name)),
			remove: (...names) => names.forEach((name) => classes.delete(name)),
		},
		addEventListener: (name, listener) => { listeners[name] = listener; },
		insertAdjacentHTML(_, html) { this.markup += html; },
		// Mocked links carry visible text unless a test says otherwise.
		cloneNode() {
			return { textContent: attrs['data-test-text'] ?? 'link text', querySelectorAll: () => [] };
		},
		querySelector(selector) {
			const names = selector.split(',').map((name) => name.trim().replace(/^\./, ''));
			return names.some((name) => this.markup.includes(name)) ? element() : null;
		},
		closest(selector) {
			if (selector.startsWith('a[')) {
				return ['data-wzlw-external', 'data-wzlw-blank', 'data-wzlw-download', 'data-wzlw-rule'].some((name) => this.hasAttribute(name)) ? this : null;
			}
			return selector.startsWith('.') && classes.has(selector.slice(1)) ? this : null;
		},
		focus() {},
	};
}

function boot(script, attributes, overrides = {}, innerMarkup = '', windowExtras = {}) {
	const link = element(attributes, innerMarkup);
	const modal = element({ hidden: '' });
	const title = element();
	const message = element();
	const displayUrl = element();
	const proceed = element();
	const cancel = element();
	const dismiss = element();
	const nodes = {
		'#wzlw-modal-title': title,
		'#wzlw-modal-message': message,
		'.wzlw-modal-url-value': displayUrl,
		'[data-wzlw-continue]': proceed,
		'.wzlw-modal-cancel': cancel,
		'[data-wzlw-dismiss]': dismiss,
	};
	modal.querySelector = (selector) => nodes[selector];
	modal.querySelectorAll = (selector) => selector === '[data-wzlw-close]' ? [cancel] : [cancel, proceed];
	const listeners = {};
	const opened = [];
	const location = { href: 'https://site.test/blog/post/' };
	const document = {
		baseURI: location.href,
		readyState: 'complete',
		body: { children: [modal], classList: element().classList },
		getElementById: () => modal,
		querySelectorAll: () => [link],
		addEventListener: (name, callback) => { listeners[name] = callback; },
	};
	const context = {
		document,
		window: { location, open: (...args) => opened.push(args), ...windowExtras },
		wzlwSettings: {
			siteHost: 'site.test',
			warningMethod: 'inline_modal',
			scope: 'external',
			excludedDomains: [],
			forceExternalClass: ['wzlw-force-external'],
			affiliateClass: ['wzlw-affiliate'],
			redirectBaseUrl: 'https://site.test/blog/external-redirect/',
			...overrides,
		},
		URL,
		FormData,
		CSS: { escape: (value) => value },
		fetch: async () => ({ json: async () => ({ success: true, data: {} }) }),
	};
	vm.runInNewContext(script, context);
	return {
		link,
		modal,
		title,
		message,
		displayUrl,
		dismiss,
		location,
		opened,
		click() {
			const event = { target: link, defaultPrevented: false, preventDefault() { this.defaultPrevented = true; } };
			listeners.click(event);
			return event.defaultPrevented;
		},
		continue() { proceed.listeners.click(); },
	};
}

function redirectUrl(destination) {
	const url = new URL('https://site.test/blog/external-redirect/');
	url.searchParams.set('url', destination);
	url.searchParams.set('wzlw_sig', 'a'.repeat(64));
	return url.href;
}

const ruleCases = JSON.parse(readFileSync(join(__dirname, 'fixtures/link-rules.json'), 'utf8'));

for (const filename of ['modal.js', 'modal.min.js']) {
	const script = readFileSync(join(__dirname, '../includes/admin/js', filename), 'utf8');
	for (const method of ['modal', 'inline_modal']) {
		test(`${filename}: ${method} ignores a forged data destination`, () => {
			const page = boot(script, { href: '/', 'data-wzlw-external': 'true', 'data-wzlw-url': 'javascript:void(0)' }, { warningMethod: method });
			page.click();
			assert.equal(page.displayUrl.textContent, 'https://site.test/');
			page.continue();
			assert.equal(page.location.href, 'https://site.test/');
		});
	}
	for (const unsafe of ['javascript:void(0)', 'JaVaScRiPt:void(0)', '\tjava\nscript:void(0)', 'data:text/html,test']) {
		test(`${filename}: unsafe href is never passed to a modal navigation sink: ${JSON.stringify(unsafe)}`, () => {
			const page = boot(script, { href: unsafe, class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-url': unsafe });
			assert.equal(page.click(), false);
			assert.equal(page.modal.hasAttribute('hidden'), true);
			assert.equal(page.location.href, 'https://site.test/blog/post/');
			assert.equal(page.opened.length, 0);
		});
	}
	for (const destination of ['javascript:void(0)', 'https://outside.test/', 'https://site.test/blog/other/', redirectUrl('https://different.test/')]) {
		test(`${filename}: rejects forged redirect attribute: ${destination}`, () => {
			const page = boot(script, { href: '/safe/', class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-redirect-url': destination }, { warningMethod: 'redirect' });
			// Fail closed: the click is stopped and the link is signed again.
			assert.equal(page.click(), true);
			assert.equal(page.location.href, 'https://site.test/blog/post/');
			assert.equal(page.opened.length, 0);
			assert.equal(page.link.getAttribute('data-wzlw-signing'), 'true');
		});
	}
	for (const target of ['', '_blank']) {
		test(`${filename}: valid signed redirect retains navigation behavior for ${target || 'same tab'}`, () => {
			const destination = 'https://outside.test/?a=1&b=2#section';
			const signed = redirectUrl(destination);
			const page = boot(script, { href: destination, target, rel: 'noreferrer', class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-redirect-url': signed }, { warningMethod: 'redirect' });
			assert.equal(page.click(), true);
			if (target) {
				assert.deepEqual(page.opened[0], [signed, '_blank', 'noopener,noreferrer']);
			} else {
				assert.equal(page.location.href, signed);
			}
		});
	}
	test(`${filename}: root-relative affiliate redirects still work`, () => {
		const signed = redirectUrl('/go/product/');
		const page = boot(script, { href: '/go/product/', class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-redirect-url': signed }, { warningMethod: 'redirect' });
		assert.equal(page.click(), true);
		assert.equal(page.location.href, signed);
	});
	test(`${filename}: PHP exclusion marker is respected even with stale warning attributes`, () => {
		const page = boot(script, { href: 'https://outside.test/', 'data-wzlw-excluded': 'true', 'data-wzlw-external': 'true', 'data-wzlw-url': 'https://outside.test/' }, { excludedDomains: ['outside.test'] });
		assert.equal(page.link.classList.contains('wzlw-processed'), false);
		assert.equal(page.link.markup, '');
		assert.equal(page.click(), false);
	});
	test(`${filename}: explicit force class still overrides exclusion`, () => {
		const page = boot(script, { href: 'https://outside.test/', class: 'wzlw-force-external', 'data-wzlw-excluded': 'true' }, { excludedDomains: ['outside.test'] });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
		assert.equal(page.link.hasAttribute('data-wzlw-excluded'), false);
		assert.equal(page.click(), true);
	});
	test(`${filename}: network-path external URL is detected`, () => {
		const page = boot(script, { href: '//outside.test/' });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
	});
	test(`${filename}: internal configured download URL is processed and uses download copy`, () => {
		const page = boot(script, { href: '/files/report.PDF?download=1#top' }, {
			downloadExtensions: ['pdf', 'zip'],
			downloadModalTitle: 'Download this file',
			downloadModalMessage: 'The file will be downloaded. Continue?',
		});
		assert.equal(page.link.getAttribute('data-wzlw-download'), 'true');
		assert.equal(page.link.getAttribute('data-wzlw-external'), null);
		assert.match(page.link.markup, /wzlw-download-icon/);
		assert.equal(page.click(), true);
		assert.equal(page.title.textContent, 'Download this file');
		assert.equal(page.message.textContent, 'The file will be downloaded. Continue?');
	});
	for (const href of ['/wp-content/uploads/a.pdf', 'https://site.test/b.pdf']) {
		test(`${filename}: download scope external skips site file: ${href}`, () => {
			const page = boot(script, { href }, { downloadExtensions: ['pdf'], downloadScope: 'external' });
			assert.equal(page.link.getAttribute('data-wzlw-download'), null);
			assert.equal(page.link.classList.contains('wzlw-processed'), false);
		});
	}
	test(`${filename}: download scope external keeps external and forced files`, () => {
		for (const attributes of [{ href: 'https://outside.test/c.pdf' }, { href: '/e.pdf', class: 'wzlw-force-external' }]) {
			const page = boot(script, attributes, { downloadExtensions: ['pdf'], downloadScope: 'external' });
			assert.equal(page.link.getAttribute('data-wzlw-download'), 'true');
		}
	});
	test(`${filename}: download scope all keeps site files`, () => {
		const page = boot(script, { href: '/wp-content/uploads/a.pdf' }, { downloadExtensions: ['pdf'], downloadScope: 'all' });
		assert.equal(page.link.getAttribute('data-wzlw-download'), 'true');
	});
	test(`${filename}: disabled warnings still apply link attributes`, () => {
		const page = boot(script, { href: 'https://outside.test/off/' }, { warningsDisabled: true, linkAttributesExternal: ['nofollow'] });
		assert.equal(page.link.getAttribute('rel'), 'nofollow');
		assert.equal(page.link.getAttribute('data-wzlw-external'), null);
		assert.equal(page.link.markup, '');
		assert.equal(page.click(), false);
	});
	test(`${filename}: link rules match exactly as the shared fixture expects`, () => {
		for (const ruleCase of ruleCases) {
			const rules = [{ id: 'r1', pattern: ruleCase.normalized, hostOnly: ruleCase.hostOnly, action: 'inline', message: '' }];
			const start = Date.now();
			const page = boot(script, { href: ruleCase.href }, { linkRules: rules, warningMethod: 'none' });
			assert.equal(page.link.getAttribute('data-wzlw-rule') === 'r1', ruleCase.matches, `${ruleCase.pattern} vs ${ruleCase.href.slice(0, 60)}`);
			assert.ok(Date.now() - start < 500, `matching was slow for ${ruleCase.pattern}`);
		}
	});
	test(`${filename}: first matching rule wins and sets the link method`, () => {
		const rules = [
			{ id: 'a', pattern: 'outside.test/first/*', hostOnly: false, action: 'modal', message: '' },
			{ id: 'b', pattern: 'outside.test', hostOnly: true, action: 'redirect', message: '' },
		];
		const page = boot(script, { href: 'https://outside.test/first/x' }, { linkRules: rules, warningMethod: 'inline' });
		assert.equal(page.link.getAttribute('data-wzlw-rule'), 'a');
		assert.equal(page.link.getAttribute('data-wzlw-method'), 'modal');
		assert.equal(page.link.markup, '');
		assert.equal(page.click(), true);
	});
	test(`${filename}: a rule processes an internal link and shows its own message`, () => {
		const rules = [{ id: 'go', pattern: 'site.test/go/*', hostOnly: false, action: 'inline_modal', message: 'Affiliate link ahead' }];
		const page = boot(script, { href: '/go/product/' }, { linkRules: rules, warningMethod: 'none', modalMessage: 'Global message' });
		assert.equal(page.link.getAttribute('data-wzlw-url'), 'https://site.test/go/product/');
		assert.equal(page.link.getAttribute('data-wzlw-external'), null);
		assert.match(page.link.markup, /wzlw-icon/);
		assert.equal(page.click(), true);
		assert.equal(page.message.textContent, 'Affiliate link ahead');
	});
	test(`${filename}: a rule overrides an excluded domain`, () => {
		const rules = [{ id: 't', pattern: 'trusted.test', hostOnly: true, action: 'modal', message: '' }];
		const page = boot(script, { href: 'https://trusted.test/' }, { linkRules: rules, excludedDomains: ['trusted.test'] });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
		assert.equal(page.click(), true);
	});
	test(`${filename}: exclude rule skips the link unless a force class is present`, () => {
		const rules = [{ id: 'x', pattern: 'outside.test', hostOnly: true, action: 'exclude', message: '' }];
		let page = boot(script, { href: 'https://outside.test/' }, { linkRules: rules });
		assert.equal(page.link.classList.contains('wzlw-processed'), false);
		assert.equal(page.click(), false);
		page = boot(script, { href: 'https://outside.test/', class: 'wzlw-force-external' }, { linkRules: rules });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
	});
	test(`${filename}: a PHP-processed rule link is left alone and uses its own method`, () => {
		const rules = [{ id: 'm', pattern: 'outside.test', hostOnly: true, action: 'modal', message: 'Rule message' }];
		const page = boot(script, { href: 'https://outside.test/', class: 'wzlw-processed wzlw-external', 'data-wzlw-rule': 'm', 'data-wzlw-method': 'modal', 'data-wzlw-url': 'https://outside.test/', 'data-wzlw-external': 'true' }, { linkRules: rules, warningMethod: 'inline' });
		assert.equal(page.link.markup, '');
		assert.equal(page.click(), true);
		assert.equal(page.message.textContent, 'Rule message');
	});
	test(`${filename}: a malformed rule is ignored`, () => {
		const page = boot(script, { href: 'https://outside.test/' }, { linkRules: [{ id: 'bad', hostOnly: true, action: 'exclude' }, null] });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
	});
	test(`${filename}: author-written rule attributes cannot change the method`, () => {
		const page = boot(script, { href: 'https://out.test/', 'data-wzlw-method': 'inline', 'data-wzlw-rule': 'x' }, { warningMethod: 'modal' });
		assert.equal(page.link.getAttribute('data-wzlw-method'), null);
		assert.equal(page.link.getAttribute('data-wzlw-rule'), null);
		assert.equal(page.click(), true);
	});
	test(`${filename}: a forged rule ID on a server-processed link is ignored on click`, () => {
		const rules = [{ id: 'soft', pattern: 'other.test', hostOnly: true, action: 'inline', message: '' }];
		const page = boot(script, { href: 'https://out.test/', class: 'wzlw-processed wzlw-external', 'data-wzlw-external': 'true', 'data-wzlw-url': 'https://out.test/', 'data-wzlw-rule': 'soft', 'data-wzlw-method': 'inline' }, { warningMethod: 'modal', linkRules: rules });
		assert.equal(page.click(), true);
	});
	test(`${filename}: backslash URLs are treated as external`, () => {
		for (const href of ['/\\evil.test/x', 'https:\\\\evil.test/y']) {
			const page = boot(script, { href }, { excludedDomains: ['trusted.test'] });
			assert.equal(page.link.getAttribute('data-wzlw-external'), 'true', href);
		}
	});
	test(`${filename}: force class beats an exclude rule for downloads too`, () => {
		const rules = [{ id: 'x', pattern: 'partner.test', hostOnly: true, action: 'exclude', message: '' }];
		const page = boot(script, { href: 'https://partner.test/file.pdf', class: 'wzlw-affiliate' }, { linkRules: rules, downloadExtensions: ['pdf'] });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
		assert.equal(page.link.getAttribute('data-wzlw-download'), 'true');
	});
	test(`${filename}: users with warnings disabled keep new-tab screen reader text`, () => {
		const page = boot(script, { href: 'https://out.test/', target: '_blank' }, { warningsDisabled: true, screenReaderText: 'Opens in a new window', noIconClass: ['wzlw-no-icon'] });
		assert.equal(page.link.markup, '<span class="screen-reader-text">Opens in a new window</span>');
		assert.equal(page.link.getAttribute('data-wzlw-url'), null);
		assert.equal(page.click(), false);
	});
	test(`${filename}: target=_BLANK is matched case-insensitively`, () => {
		const page = boot(script, { href: 'https://out.test/', target: '_BLANK', 'aria-label': 'Docs' }, { screenReaderText: 'Opens in a new window' });
		assert.equal(page.link.getAttribute('aria-label'), 'Docs, Opens in a new window');
		assert.match(page.link.markup, /screen-reader-text/);
	});
	test(`${filename}: backslash links send the normalized URL for signing`, () => {
		const page = boot(script, { href: 'https://example.com\\go\\deal' }, { warningMethod: 'redirect' });
		assert.equal(page.link.getAttribute('data-wzlw-url'), 'https://example.com/go/deal');
	});
	test(`${filename}: a data-wzlw-rule attribute alone does not trigger a warning`, () => {
		const page = boot(script, { href: '/about/' }, { warningMethod: 'modal' });
		page.link.setAttribute('data-wzlw-rule', 'x');
		assert.equal(page.click(), false);
	});
	test(`${filename}: dismissing one modal does not hide a rule's own modal`, () => {
		const store = new Map();
		const sessionStorage = { getItem: (k) => store.get(k) ?? null, setItem: (k, v) => store.set(k, String(v)) };
		const rules = [{ id: 'aff', pattern: 'site.test/go/*', hostOnly: false, action: 'modal', message: 'Disclosure' }];
		const options = { modalFrequency: 'session', linkRules: rules, scope: 'both' };
		let page = boot(script, { href: '/blank/', target: '_blank' }, options, '', { sessionStorage });
		assert.equal(page.click(), true);
		page.dismiss.checked = true;
		page.continue();
		page = boot(script, { href: '/blank/', target: '_blank' }, options, '', { sessionStorage });
		assert.equal(page.click(), false, 'the dismissed modal came back');
		page = boot(script, { href: '/go/deal/' }, options, '', { sessionStorage });
		assert.equal(page.click(), true, 'the rule modal was hidden by an unrelated dismissal');
		assert.equal(page.message.textContent, 'Disclosure');
	});
	test(`${filename}: a redirect link whose signing failed asks before opening`, async () => {
		const asked = [];
		const extras = { confirm: (text) => { asked.push(text); return true; } };
		const page = boot(script, { href: 'https://brace.test/a{b}' }, { warningMethod: 'redirect', modalMessage: 'Leaving?' }, '', extras);
		assert.equal(page.link.getAttribute('data-wzlw-url'), 'https://brace.test/a%7Bb%7D');
		assert.equal(page.click(), true, 'the click went through while signing ran');
		assert.equal(asked.length, 0);
		await new Promise((resolve) => setTimeout(resolve, 0));
		assert.equal(page.link.getAttribute('data-wzlw-signing'), null);
		assert.equal(page.click(), true);
		assert.equal(asked.length, 1);
		assert.match(asked[0], /Leaving\?/);
		assert.equal(page.location.href, 'https://brace.test/a%7Bb%7D');
	});
	test(`${filename}: an excluded marker cannot cancel a rule's warning`, () => {
		const rules = [{ id: 'aff', pattern: 'evil.example/*', hostOnly: false, action: 'modal', message: 'Affiliate disclosure' }];
		const page = boot(script, { href: 'https://evil.example/x', 'data-wzlw-excluded': 'true' }, { linkRules: rules });
		assert.equal(page.click(), true);
		assert.equal(page.message.textContent, 'Affiliate disclosure');
	});
	test(`${filename}: clicks do nothing when warnings are disabled`, () => {
		const page = boot(script, { href: 'https://out.test/', 'data-wzlw-external': 'true', 'data-wzlw-url': 'https://out.test/' }, { warningsDisabled: true });
		assert.equal(page.click(), false);
	});
	test(`${filename}: file extension in a query string is ignored`, () => {
		const page = boot(script, { href: '/download?file=report.pdf' }, { downloadExtensions: ['pdf'] });
		assert.equal(page.link.getAttribute('data-wzlw-download'), null);
		assert.equal(page.link.classList.contains('wzlw-processed'), false);
	});
	test(`${filename}: author-written marker class is not an opt-out`, () => {
		const page = boot(script, { href: 'https://outside.test/marker/', class: 'wzlw-processed' });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
		assert.equal(page.link.getAttribute('data-wzlw-url'), 'https://outside.test/marker/');
		assert.match(page.link.markup, /wzlw-icon/);
		assert.equal(page.click(), true);
	});
	test(`${filename}: a PHP-processed link is left alone`, () => {
		const page = boot(script, { href: 'https://outside.test/done/', class: 'wzlw-processed wzlw-external', 'data-wzlw-external': 'true', 'data-wzlw-url': 'https://outside.test/done/' });
		assert.equal(page.link.markup, '');
	});
	test(`${filename}: a PHP-processed inline-only link is left alone`, () => {
		const page = boot(script, { href: 'https://outside.test/inline/', class: 'wzlw-processed wzlw-external' }, { warningMethod: 'inline' }, '<span class="screen-reader-text">Opens in a new window</span><span class="wzlw-icon" aria-hidden="true"></span>');
		assert.equal(page.link.markup, '<span class="screen-reader-text">Opens in a new window</span><span class="wzlw-icon" aria-hidden="true"></span>');
	});
	test(`${filename}: an existing ARIA label is not appended twice`, () => {
		const page = boot(script, { href: 'https://outside.test/aria/', class: 'wzlw-processed wzlw-external', 'aria-label': 'Docs, Opens in a new window' }, { screenReaderText: 'Opens in a new window', warningMethod: 'none' });
		assert.equal(page.link.getAttribute('aria-label'), 'Docs, Opens in a new window');
	});
	for (const href of ['/path/', '//site.test/path/', '?query=1', '#section']) {
		test(`${filename}: internal URL is not marked external: ${href}`, () => {
			const page = boot(script, { href });
			assert.equal(page.link.getAttribute('data-wzlw-external'), null);
		});
	}
	test(`${filename}: network-path excluded URL receives no warning`, () => {
		const page = boot(script, { href: '//sub.outside.test/', target: '_blank' }, { scope: 'both', excludedDomains: ['*.outside.test'] });
		assert.equal(page.link.getAttribute('data-wzlw-blank'), null);
		assert.equal(page.link.markup, '');
	});
	test(`${filename}: destination is revalidated when Continue is clicked`, () => {
		const page = boot(script, { href: 'https://outside.test/' });
		page.click();
		page.link.setAttribute('href', 'javascript:void(0)');
		page.link.setAttribute('data-wzlw-url', 'javascript:void(0)');
		page.continue();
		assert.equal(page.location.href, 'https://site.test/blog/post/');
		assert.equal(page.opened.length, 0);
	});
}

const redirectScript = readFileSync(join(__dirname, '../includes/assets/js/redirect.js'), 'utf8');
for (const countdown of ['0', 0, '', 'x', -3]) {
	test(`redirect.js: countdown ${JSON.stringify(countdown)} never redirects, even with a countdown element`, () => {
		const location = { href: 'https://site.test/external-redirect/' };
		const timers = [];
		const element = { textContent: '' };
		const context = {
			wzlwRedirect: { destination: 'https://evil.example/', countdown },
			window: { location },
			document: {
				readyState: 'complete',
				querySelector: (selector) => ('.wzlw-countdown-number' === selector ? element : null),
				querySelectorAll: () => [],
				addEventListener: () => {},
			},
			setInterval: (fn) => { timers.push(fn); return timers.length; },
			clearInterval: () => {},
		};
		vm.runInNewContext(redirectScript, context);
		for (let i = 0; i < 10; i++) {
			timers.forEach((fn) => fn());
		}
		assert.equal(location.href, 'https://site.test/external-redirect/');
	});
}

