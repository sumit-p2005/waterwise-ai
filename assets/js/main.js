const $ = (s, c = document) => c.querySelector(s);
const $$ = (s, c = document) => [...c.querySelectorAll(s)];

const toastContainer = $('#toastContainer');
function showToast(message, type = 'info') {
  if (!toastContainer) return;
  const div = document.createElement('div');
  div.className = 'toast';
  div.textContent = message;
  div.style.background = type === 'error' ? 'rgba(225,29,72,.93)' : (type === 'success' ? 'rgba(5,150,105,.93)' : 'rgba(15,23,42,.9)');
  toastContainer.appendChild(div);
  setTimeout(() => div.remove(), 3200);
}
window.showToast = showToast;

function initTheme() {
  const toggle = $('#themeToggle');
  if (!toggle) return;
  toggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('dark');
    const isDark = document.documentElement.classList.contains('dark');
    localStorage.setItem('ww-theme', isDark ? 'dark' : 'light');
  });
}

function initSidebar() {
  const sidebar = $('#sidebar');
  const desktopToggle = $('#sidebarToggle');
  const mobileToggle = $('#mobileSidebarToggle');
  if (!sidebar) return;

  desktopToggle?.addEventListener('click', () => {
    document.documentElement.classList.toggle('sidebar-collapsed');
    localStorage.setItem('ww-sidebar', document.documentElement.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded');
  });

  mobileToggle?.addEventListener('click', () => sidebar.classList.toggle('open'));
  $$('.nav-item').forEach(item => item.addEventListener('click', () => sidebar.classList.remove('open')));
}

function initRipple() {
  document.addEventListener('click', (e) => {
    const target = e.target.closest('.ripple');
    if (!target) return;
    const dot = document.createElement('span');
    dot.className = 'ripple-dot';
    const rect = target.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    dot.style.width = `${size}px`;
    dot.style.height = `${size}px`;
    dot.style.left = `${e.clientX - rect.left - size / 2}px`;
    dot.style.top = `${e.clientY - rect.top - size / 2}px`;
    target.appendChild(dot);
    setTimeout(() => dot.remove(), 600);
  });
}

function initTilt() {
  $$('.tilt-card').forEach((card) => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const ry = ((x / rect.width) - .5) * 14;
      const rx = ((y / rect.height) - .5) * -14;
      card.style.transform = `perspective(920px) rotateX(${rx}deg) rotateY(${ry}deg)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = 'perspective(920px) rotateX(0deg) rotateY(0deg)'; });
  });
}

function initParallax() {
  const blobs = $$('.gradient-blob');
  if (!blobs.length) return;
  window.addEventListener('scroll', () => {
    const y = window.scrollY;
    blobs.forEach((b, i) => {
      const speed = (i + 1) * 0.05;
      b.style.transform = `translate3d(0, ${y * speed}px, 0)`;
    });
  }, { passive: true });
}

function initReveal() {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) entry.target.classList.add('in');
    });
  }, { threshold: 0.12 });
  $$('.reveal').forEach(el => obs.observe(el));
}

function debounce(fn, delay = 250) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
}
window.debounce = debounce;

function lazyInit(selector, callback) {
  const target = $(selector);
  if (!target) return;
  const io = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      callback(target);
      io.disconnect();
    }
  }, { threshold: 0.05 });
  io.observe(target);
}
window.lazyInit = lazyInit;

initTheme();
initSidebar();
initRipple();
initTilt();
initParallax();
initReveal();
if (window.lucide) window.lucide.createIcons();
