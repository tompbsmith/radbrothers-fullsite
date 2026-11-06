/* Rad Brothers — front-end behaviour (no dependencies) */
(function () {
	'use strict';

	var doc = document;
	var body = doc.body;
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	/* ---------- Header: scrolled state + hide on scroll down ---------- */
	var lastY = window.scrollY;
	var ticking = false;
	function onScroll() {
		var y = window.scrollY;
		body.classList.toggle('is-scrolled', y > 40);
		if (!body.classList.contains('nav-open')) {
			body.classList.toggle('header-hidden', y > 400 && y > lastY + 4);
			if (y < lastY - 4) body.classList.remove('header-hidden');
		}
		lastY = y;
		parallax(y);
		processProgress();
		ticking = false;
	}
	window.addEventListener('scroll', function () {
		if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
	}, { passive: true });

	/* ---------- Mobile navigation ---------- */
	var toggle = doc.querySelector('[data-nav-toggle]');
	var nav = doc.getElementById('site-nav');
	// While the menu is open, everything outside the header is inert so focus stays in the menu.
	var outside = [doc.getElementById('main'), doc.querySelector('.site-footer'), doc.querySelector('.site-header__brand')].filter(Boolean);
	function setNav(open, returnFocus) {
		body.classList.toggle('nav-open', open);
		toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		outside.forEach(function (el) { el.inert = open; });
		if (open) {
			body.classList.remove('header-hidden');
			var first = nav && nav.querySelector('a');
			if (first) setTimeout(function () { first.focus(); }, 50);
		} else if (returnFocus) {
			toggle.focus();
		}
	}
	if (toggle) {
		toggle.addEventListener('click', function () { setNav(!body.classList.contains('nav-open'), false); });
		doc.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && body.classList.contains('nav-open')) setNav(false, true);
		});
		window.addEventListener('resize', function () {
			if (window.innerWidth > 1023 && body.classList.contains('nav-open')) setNav(false, false);
		});
		// Close the menu when an in-page link is followed.
		doc.querySelectorAll('.site-nav a[href*="#"]').forEach(function (a) {
			a.addEventListener('click', function () { if (body.classList.contains('nav-open')) setNav(false, false); });
		});
	}
	// Sub-menus: a separate button opens them in the mobile menu; the parent link still goes to its page.
	doc.querySelectorAll('.site-nav .menu-item-has-children').forEach(function (li, n) {
		var link = li.querySelector(':scope > a');
		var sub = li.querySelector(':scope > .sub-menu');
		if (!link || !sub) return;
		if (!sub.id) sub.id = 'sub-menu-' + n;
		var btn = doc.createElement('button');
		btn.type = 'button';
		btn.className = 'sub-toggle';
		btn.setAttribute('aria-expanded', 'false');
		btn.setAttribute('aria-controls', sub.id);
		btn.setAttribute('aria-label', 'Show ' + link.textContent.trim() + ' pages');
		btn.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>';
		link.after(btn);
		btn.addEventListener('click', function () {
			var open = sub.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			btn.setAttribute('aria-label', (open ? 'Hide ' : 'Show ') + link.textContent.trim() + ' pages');
		});
	});

	/* ---------- Scroll reveal ---------- */
	var animated = doc.querySelectorAll('[data-animate]');
	if ('IntersectionObserver' in window && !reduceMotion) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('in-view');
					io.unobserve(entry.target);
				}
			});
		}, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
		animated.forEach(function (el) { io.observe(el); });
	} else {
		animated.forEach(function (el) { el.classList.add('in-view'); });
	}

	/* ---------- Hero parallax ---------- */
	var heroMedia = doc.querySelector('[data-parallax]');
	function parallax(y) {
		if (!heroMedia || reduceMotion || window.innerWidth < 768) return;
		if (y > window.innerHeight * 1.2) return;
		heroMedia.style.transform = 'translate3d(0,' + (y * 0.18).toFixed(1) + 'px,0)';
	}

	/* ---------- Detailed process: line fills as you scroll ---------- */
	var detailed = doc.querySelectorAll('.steps--detailed');
	function processProgress() {
		detailed.forEach(function (list) {
			var r = list.getBoundingClientRect();
			var vh = window.innerHeight;
			var p = Math.min(1, Math.max(0, (vh * 0.65 - r.top) / r.height));
			list.style.setProperty('--progress', (p * 100).toFixed(1) + '%');
		});
	}

	/* ---------- Service-level filter on the detailed process ---------- */
	doc.querySelectorAll('.tier-filter').forEach(function (filter) {
		var steps = filter.closest('section').querySelectorAll('[data-steps] .step');
		filter.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-tier]');
			if (!btn) return;
			filter.querySelectorAll('[data-tier]').forEach(function (b) {
				b.classList.toggle('is-active', b === btn);
				b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
			});
			var tier = btn.getAttribute('data-tier');
			var name = btn.textContent.trim();
			steps.forEach(function (s) {
				var tiers = (s.getAttribute('data-tiers') || '').split(' ');
				var dim = tier !== 'all' && tiers.indexOf(tier) === -1;
				s.classList.toggle('is-dimmed', dim);
				// Greyed-out steps are only visual, so say it for screen readers too.
				var note = s.querySelector('.step__note');
				if (dim) {
					if (!note) {
						note = doc.createElement('span');
						note.className = 'step__note screen-reader-text';
						var title = s.querySelector('.step__title');
						(title || s).appendChild(note);
					}
					note.textContent = ' (not included in ' + name + ')';
				} else if (note) {
					note.remove();
				}
			});
		});
	});

	/* ---------- Testimonial slider ---------- */
	doc.querySelectorAll('[data-slider]').forEach(function (slider) {
		var slides = slider.querySelectorAll('[data-slide]');
		var dots = slider.querySelectorAll('[data-dot]');
		var track = slider.querySelector('[data-track]');
		var pauseBtn = slider.querySelector('[data-pause]');
		if (slides.length < 2) return;
		var i = 0;
		var timer;
		var userPaused = false;
		var focusPaused = false;
		var hoverPaused = false;
		function go(n, byUser) {
			i = (n + slides.length) % slides.length;
			// Only announce changes the visitor asked for, never the automatic ones.
			if (track) track.setAttribute('aria-live', byUser ? 'polite' : 'off');
			slides.forEach(function (s, k) {
				s.classList.toggle('is-active', k === i);
				if (k === i) s.removeAttribute('hidden'); else s.setAttribute('hidden', '');
			});
			dots.forEach(function (d, k) {
				d.classList.toggle('is-active', k === i);
				if (k === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
			});
		}
		function auto() {
			clearInterval(timer);
			if (reduceMotion || userPaused || focusPaused || hoverPaused) return;
			timer = setInterval(function () { go(i + 1, false); }, 7000);
		}
		function setPaused(p) {
			userPaused = p;
			slider.classList.toggle('is-paused', p);
			if (pauseBtn) pauseBtn.setAttribute('aria-label', p ? 'Play testimonials' : 'Pause testimonials');
			auto();
		}
		slider.querySelector('[data-next]').addEventListener('click', function () { go(i + 1, true); auto(); });
		slider.querySelector('[data-prev]').addEventListener('click', function () { go(i - 1, true); auto(); });
		dots.forEach(function (d) { d.addEventListener('click', function () { go(+d.getAttribute('data-dot'), true); auto(); }); });
		if (pauseBtn) {
			if (reduceMotion) pauseBtn.hidden = true;
			pauseBtn.addEventListener('click', function () { setPaused(!userPaused); });
		}
		slider.addEventListener('mouseenter', function () { hoverPaused = true; auto(); });
		slider.addEventListener('mouseleave', function () { hoverPaused = false; auto(); });
		slider.addEventListener('focusin', function () { focusPaused = true; auto(); });
		slider.addEventListener('focusout', function (e) {
			if (!slider.contains(e.relatedTarget)) { focusPaused = false; auto(); }
		});
		auto();
	});

	/* ---------- Logo strip: pause control ---------- */
	doc.querySelectorAll('[data-marquee-toggle]').forEach(function (btn) {
		var marquee = btn.closest('section').querySelector('.marquee');
		if (!marquee) return;
		if (reduceMotion) { btn.hidden = true; return; }
		btn.addEventListener('click', function () {
			var paused = marquee.classList.toggle('is-paused');
			btn.classList.toggle('is-paused', paused);
			btn.setAttribute('aria-label', paused ? 'Play the scrolling logos' : 'Pause the scrolling logos');
		});
	});

	/* ---------- Meet the team: hotspots on the group photo ---------- */
	doc.querySelectorAll('[data-team]').forEach(function (team) {
		var stage = team.querySelector('[data-team-stage]');
		var spots = team.querySelectorAll('[data-spot]');
		var cards = team.querySelectorAll('[data-card]');
		var chips = team.querySelectorAll('[data-chip]');
		var chipList = team.querySelector('[data-team-chips]');
		var panel = team.querySelector('[data-team-panel]');
		var spotlight = team.querySelector('.team__spotlight');
		var active = -1;
		var pinned = false;
		var leaveTimer;
		var canHover = window.matchMedia('(hover: hover)').matches;

		// Spot size in px drives the spotlight radius and card offset.
		function measure() {
			if (!spots.length) return;
			var w = spots[0].getBoundingClientRect().width;
			team.style.setProperty('--spot-px', w + 'px');
		}
		measure();
		window.addEventListener('resize', measure);

		// Decide where each card sits so it stays inside the photo.
		cards.forEach(function (card) {
			var x = parseFloat(card.getAttribute('data-x'));
			var y = parseFloat(card.getAttribute('data-y'));
			if (x < 24) card.classList.add('is-left');
			if (x > 76) card.classList.add('is-right');
			if (y > 58) card.classList.add('is-above');
		});

		function setActive(n, opts) {
			opts = opts || {};
			clearTimeout(leaveTimer);
			active = n;
			team.classList.toggle('has-active', n > -1);
			if (n > -1) team.classList.add('was-used');
			spots.forEach(function (s, k) {
				s.classList.toggle('is-active', k === n);
				s.setAttribute('aria-expanded', k === n ? 'true' : 'false');
			});
			cards.forEach(function (c, k) { c.classList.toggle('is-active', k === n); });
			chips.forEach(function (c, k) { c.classList.toggle('is-active', k === n); });
			if (n > -1) {
				var sp = spots[n];
				spotlight.style.setProperty('--sx', sp.style.getPropertyValue('--x'));
				spotlight.style.setProperty('--sy', sp.style.getPropertyValue('--y'));
				if (opts.scrollChip && chips[n] && chipList) {
					var c = chips[n].parentElement;
					chipList.scrollTo({ left: c.offsetLeft - chipList.offsetLeft - 8, behavior: reduceMotion ? 'auto' : 'smooth' });
				}
				if (panel) {
					var clone = cards[n].cloneNode(true);
					clone.removeAttribute('id');
					clone.classList.add('is-active');
					clone.classList.remove('is-left', 'is-right', 'is-above');
					panel.innerHTML = '';
					panel.appendChild(clone);
				}
			} else if (panel) {
				panel.innerHTML = '';
			}
		}

		spots.forEach(function (spot, k) {
			if (canHover) {
				spot.addEventListener('mouseenter', function () { if (!pinned) setActive(k); });
			}
			spot.addEventListener('focus', function () { setActive(k, { scrollChip: true }); });
			spot.addEventListener('click', function () {
				if (active === k && pinned) { pinned = false; setActive(-1); return; }
				pinned = true;
				setActive(k, { scrollChip: true });
			});
		});

		if (canHover) {
			stage.addEventListener('mouseleave', function () {
				if (pinned) return;
				leaveTimer = setTimeout(function () { setActive(-1); }, 250);
			});
			// Keep the card open while the pointer moves onto it.
			cards.forEach(function (card) {
				card.addEventListener('mouseenter', function () { clearTimeout(leaveTimer); });
			});
		}

		chips.forEach(function (chip, k) {
			chip.addEventListener('click', function () {
				pinned = true;
				setActive(k, { scrollChip: true });
			});
		});

		function step(dir) {
			var n = active < 0 ? (dir > 0 ? 0 : spots.length - 1) : (active + dir + spots.length) % spots.length;
			pinned = true;
			setActive(n, { scrollChip: true });
		}
		var next = team.querySelector('[data-team-next]');
		var prev = team.querySelector('[data-team-prev]');
		if (next) next.addEventListener('click', function () { step(1); });
		if (prev) prev.addEventListener('click', function () { step(-1); });

		team.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { step(1); if (spots[active]) spots[active].focus(); }
			if (e.key === 'ArrowLeft') { step(-1); if (spots[active]) spots[active].focus(); }
			if (e.key === 'Escape') { pinned = false; setActive(-1); }
		});

		// Click outside closes a pinned card.
		doc.addEventListener('click', function (e) {
			if (pinned && !team.contains(e.target)) { pinned = false; setActive(-1); }
		});

		// On touch screens, show the first person once the section is in view so it's obvious how it works.
		if (!canHover && 'IntersectionObserver' in window) {
			var shown = false;
			var tio = new IntersectionObserver(function (entries) {
				if (entries[0].isIntersecting && !shown && active < 0) {
					shown = true;
					setTimeout(function () { if (active < 0) setActive(0); }, 900);
					tio.disconnect();
				}
			}, { threshold: 0.5 });
			tio.observe(stage);
		}
	});

	/* ---------- Quote form: hide event fields for services with no event ---------- */
	Array.prototype.forEach.call(doc.querySelectorAll('select[data-no-event]'), function (sel) {
		var form = sel.form;
		var noEvent = [];
		try { noEvent = JSON.parse(sel.getAttribute('data-no-event')) || []; } catch (e) {}
		var fields = form ? form.querySelectorAll('[data-event-field]') : [];
		function sync() {
			var hide = noEvent.indexOf(sel.value) !== -1;
			Array.prototype.forEach.call(fields, function (f) {
				f.hidden = hide;
				Array.prototype.forEach.call(f.querySelectorAll('input, select, textarea'), function (i) {
					i.disabled = hide; // disabled fields are never required or submitted
				});
			});
		}
		sel.addEventListener('change', sync);
		sync();
	});

	onScroll();
})();
