/* ============================================
   SCASH — Scam Shield | Interactive Scripts
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {

  /* ------------------------------------------
     1. Navbar scroll effect
     ------------------------------------------ */
  const navbar = document.querySelector('.navbar-scash');
  const onScroll = () => {
    navbar.classList.toggle('scrolled', window.scrollY > 40);
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ------------------------------------------
     2. Scroll-reveal animations
     ------------------------------------------ */
  const reveals = document.querySelectorAll('.reveal');
  const revealObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          revealObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
  );
  reveals.forEach((el) => revealObserver.observe(el));

  /* ------------------------------------------
     3. Interactive card hover (tilt + glow)
     ------------------------------------------ */
  const interactiveCards = document.querySelectorAll('.alert-card, .blog-card');

  interactiveCards.forEach((card) => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      const rotateX = ((y - centerY) / centerY) * -4;
      const rotateY = ((x - centerX) / centerX) * 4;

      card.style.transform = `perspective(800px) translateY(-6px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;

      // Glow follow cursor
      card.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(0,200,150,.04), transparent 60%), #fff`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
      card.style.background = '';
    });
  });

  /* ------------------------------------------
     4. Hero search bar functionality (mock)
     ------------------------------------------ */
  const verifyBtn = document.getElementById('btn-verify');
  const searchInput = document.getElementById('search-input');
  const searchSelect = document.getElementById('search-type');

  if (verifyBtn) {
    verifyBtn.addEventListener('click', () => {
      const query = searchInput.value.trim();
      const type = searchSelect.value;

      if (!query) {
        searchInput.focus();
        searchInput.style.outline = '2px solid var(--coral)';
        setTimeout(() => { searchInput.style.outline = ''; }, 1200);
        return;
      }

      // Mock search animation
      const originalText = verifyBtn.innerHTML;
      verifyBtn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Searching…';
      verifyBtn.disabled = true;

      setTimeout(() => {
        verifyBtn.innerHTML = '<i class="bi bi-check-circle"></i> No threats found';
        verifyBtn.style.background = 'linear-gradient(135deg, var(--emerald), var(--emerald-dark))';

        setTimeout(() => {
          verifyBtn.innerHTML = originalText;
          verifyBtn.disabled = false;
          verifyBtn.style.background = '';
        }, 2200);
      }, 1800);
    });

    // Enter key support
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') verifyBtn.click();
    });
  }

  /* ------------------------------------------
     5. Trust score bar animations
     ------------------------------------------ */
  const scoreBars = document.querySelectorAll('.trust-score-bar .fill');
  const barObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const target = entry.target.dataset.width;
          entry.target.style.width = target;
          barObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.5 }
  );
  scoreBars.forEach((bar) => {
    bar.style.width = '0%';
    barObserver.observe(bar);
  });

  /* ------------------------------------------
     6. Smooth scroll for nav links
     ------------------------------------------ */
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = navbar.offsetHeight + 12;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });

        // Close mobile nav if open
        const navCollapse = document.querySelector('.navbar-collapse');
        if (navCollapse && navCollapse.classList.contains('show')) {
          const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
          if (bsCollapse) bsCollapse.hide();
        }
      }
    });
  });

  /* ------------------------------------------
     7. Spin animation helper
     ------------------------------------------ */
  const styleTag = document.createElement('style');
  styleTag.textContent = `
    @keyframes spinAnim { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
    .spin { display:inline-block; animation: spinAnim .8s linear infinite; }
  `;
  document.head.appendChild(styleTag);

});
