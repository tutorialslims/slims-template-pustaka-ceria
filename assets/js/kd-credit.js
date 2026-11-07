/* Tutorial SLiMS - Pustaka Ceria. Keeps the support/credit block in place. */
(function () {
  function html() {
    var d = document.createElement('div');
    d.className = 'kd-container kd-contact';
    d.setAttribute('data-kd-credit', '1');
    d.innerHTML = '<p>Whatever your SLiMS error problem is, we are ready to help, please DM us on Instagram.</p>' +
      '<p><a href="mailto:tutorialslims@gmail.com"><i class="fas fa-envelope"></i> tutorialslims@gmail.com</a>' +
      '<a target="_blank" rel="noopener" href="https://instagram.com/tutorial_slims"><i class="fab fa-instagram"></i> @tutorial_slims</a></p>';
    return d;
  }
  function visible(el) {
    var s = getComputedStyle(el);
    return s.display !== 'none' && s.visibility !== 'hidden' && parseFloat(s.opacity) > 0.5 && el.offsetHeight > 5;
  }
  function check() {
    var el = document.querySelector('[data-kd-credit]');
    if (el && el.textContent.indexOf('tutorialslims@gmail.com') > -1 && el.textContent.indexOf('@tutorial_slims') > -1 && visible(el)) return;
    if (el && el.parentNode) el.parentNode.removeChild(el);
    var f = document.querySelector('footer') || document.body;
    f.appendChild(html());
  }
  function start() {
    check();
    if (window.MutationObserver) {
      new MutationObserver(function () { check(); }).observe(document.documentElement, { childList: true, subtree: true, attributes: true, characterData: true });
    }
    setInterval(check, 3000);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
