/* Branded launch screen: shown once per app launch, only when installed (standalone). */
(function () {
  try {
    var standalone = (window.matchMedia && matchMedia('(display-mode: standalone)').matches) || navigator.standalone === true;
    if (!standalone) return;
    var seen = false;
    try { seen = sessionStorage.getItem('tilawa-launch') === '1'; sessionStorage.setItem('tilawa-launch', '1'); } catch (e) {}
    if (seen) return;

    var el = document.createElement('div');
    el.id = 'tilawa-launch';
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML = '<div class="tl-glow"></div><img src="/pwa/icon-v3-512.png" alt=""><div class="tl-ar">تلاوة</div><div class="tl-name">TILAWA</div><div class="tl-bar"><i></i></div>';
    document.documentElement.appendChild(el);

    var started = Date.now(), done = false;
    function hide() {
      if (done) return; done = true;
      var wait = Math.max(0, 1300 - (Date.now() - started));
      setTimeout(function () {
        el.classList.add('is-leaving');
        setTimeout(function () { el.remove(); }, 500);
      }, wait);
    }
    if (document.readyState === 'complete') hide(); else window.addEventListener('load', hide);
    setTimeout(hide, 4000);
  } catch (e) {}
})();
