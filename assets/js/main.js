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
	if (toggle) {
		toggle.addEventListener('click', function () {
			var open = body.classList.toggle('nav-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			body.classList.remove('header-hidden');
		});
		doc.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && body.classList.contains('nav-open')) {
				body.classList.remove('nav-open');
				toggle.setAttribute('aria-expanded', 'false');
				toggle.focus();
			}
		});
	}
	// Sub-menus open on tap in the mobile menu.
	doc.querySelectorAll('.site-nav .menu-item-has-children > a').forEach(function (a) {
		a.addEventListener('click', function (e) {
			if (window.innerWidth > 1023) return;
			var sub = a.parentElement.querySelector('.sub-menu');
			if (sub && !sub.classList.contains('is-open')) {
				e.preventDefault();
				sub.classList.add('is-open');
			}
		});
	});
	// Close the menu when an in-page link is followed.
	doc.querySelectorAll('.site-nav a[href*="#"]').forEach(function (a) {
		a.addEventListener('click', function () { body.classList.remove('nav-open'); });
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
			filter.querySelectorAll('[data-tier]').forEach(function (b) { b.classList.toggle('is-active', b === btn); });
			var tier = btn.getAttribute('data-tier');
			steps.forEach(function (s) {
				var tiers = (s.getAttribute('data-tiers') || '').split(' ');
				s.classList.toggle('is-dimmed', tier !== 'all' && tiers.indexOf(tier) === -1);
			});
		});
	});

	/* ---------- Testimonial slider ---------- */
	doc.querySelectorAll('[data-slider]').forEach(function (slider) {
		var slides = slider.querySelectorAll('[data-slide]');
		var dots = slider.querySelectorAll('[data-dot]');
		if (slides.length < 2) return;
		var i = 0;
		var timer;
		function go(n) {
			i = (n + slides.length) % slides.length;
			slides.forEach(function (s, k) {
				s.classList.toggle('is-active', k === i);
				if (k === i) s.removeAttribute('hidden'); else s.setAttribute('hidden', '');
			});
			dots.forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
		}
		function auto() {
			clearInterval(timer);
			if (!reduceMotion) timer = setInterval(function () { go(i + 1); }, 7000);
		}
		slider.querySelector('[data-next]').addEventListener('click', function () { go(i + 1); auto(); });
		slider.querySelector('[data-prev]').addEventListener('click', function () { go(i - 1); auto(); });
		dots.forEach(function (d) { d.addEventListener('click', function () { go(+d.getAttribute('data-dot')); auto(); }); });
		slider.addEventListener('mouseenter', function () { clearInterval(timer); });
		slider.addEventListener('mouseleave', auto);
		auto();
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

	onScroll();
})();
