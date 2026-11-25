/* B&B Projektbau – Verhalten */
(function () {
	'use strict';

	/* Hell/Dunkel */
	document.querySelectorAll('[data-bb-theme-toggle]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
			document.documentElement.setAttribute('data-theme', next);
			try { localStorage.setItem('bb-theme', next); } catch (e) {}
		});
	});

	/* Formular in Schritten */
	document.querySelectorAll('form[data-bb-steps]').forEach(function (form) {
		var steps = Array.prototype.slice.call(form.querySelectorAll('.bb-step'));
		var prev = form.querySelector('[data-bb-prev]');
		var next = form.querySelector('[data-bb-next]');
		var submit = form.querySelector('[data-bb-submit]');
		var bar = form.querySelector('.bb-progress span');
		var current = 0;

		function validate(step) {
			var ok = true;
			step.querySelectorAll('input[required], textarea[required], select[required]').forEach(function (el) {
				var valid = el.type === 'checkbox' ? el.checked : el.checkValidity();
				el.classList.toggle('is-invalid', !valid);
				if (!valid && ok) { el.focus(); ok = false; }
			});
			if (step.dataset.step === '1' && !step.querySelector('input[name="gewerke[]"]:checked') && !step.querySelector('textarea').value.trim()) {
				step.querySelector('.bb-options').classList.add('is-invalid');
				ok = false;
			}
			return ok;
		}

		if (steps.length > 1) {
			form.classList.add('is-stepped');
			var show = function (i) {
				current = i;
				steps.forEach(function (s, n) { s.classList.toggle('is-active', n === i); });
				prev.hidden = i === 0;
				next.hidden = i === steps.length - 1;
				submit.hidden = i !== steps.length - 1;
				if (bar) { bar.style.width = ((i + 1) / steps.length * 100) + '%'; }
			};
			next.addEventListener('click', function () {
				if (validate(steps[current])) {
					show(current + 1);
					form.scrollIntoView({ block: 'start', behavior: 'smooth' });
				}
			});
			prev.addEventListener('click', function () { show(current - 1); });
			form.addEventListener('change', function (e) {
				if (e.target.name === 'gewerke[]') {
					var o = form.querySelector('.bb-options.is-invalid');
					if (o) { o.classList.remove('is-invalid'); }
				}
			});
			show(0);
		}

		form.addEventListener('submit', function (e) {
			var all = steps.length ? steps : [form];
			for (var i = 0; i < all.length; i++) {
				if (!validate(all[i])) {
					e.preventDefault();
					if (steps.length > 1) {
						steps.forEach(function (s, n) { s.classList.toggle('is-active', n === i); });
					}
					return;
				}
			}
			submit.disabled = true;
			submit.textContent = 'Wird gesendet …';
		});

		/* Dateiliste */
		var file = form.querySelector('input[type="file"]');
		var list = form.querySelector('[data-bb-files]');
		if (file && list) {
			file.addEventListener('change', function () {
				list.innerHTML = '';
				Array.prototype.slice.call(file.files, 0, 5).forEach(function (f) {
					var li = document.createElement('li');
					li.textContent = '✓ ' + f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
					list.appendChild(li);
				});
				if (file.files.length > 5) {
					var more = document.createElement('li');
					more.textContent = 'Es werden nur die ersten 5 Fotos übertragen.';
					list.appendChild(more);
				}
			});
		}
	});

	/* Mobile Leiste nach dem ersten Bildschirm */
	var bar = document.querySelector('[data-bb-mobilebar]');
	if (bar) {
		var onScroll = function () {
			var formVisible = false;
			var f = document.getElementById('anfrage');
			if (f) { var r = f.getBoundingClientRect(); formVisible = r.top < window.innerHeight && r.bottom > 0; }
			bar.classList.toggle('is-visible', window.scrollY > window.innerHeight * 0.6 && !formVisible);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* Dezentes Einblenden */
	if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		var targets = document.querySelectorAll('.bb-card, .bb-steps li, .bb-features li, .bb-stat, .bb-section h2');
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
			});
		}, { rootMargin: '0px 0px -8% 0px' });
		targets.forEach(function (t) {
			if (t.getBoundingClientRect().top > window.innerHeight) {
				t.classList.add('bb-reveal');
				io.observe(t);
			}
		});
	}
})();
