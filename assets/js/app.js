/**
 * Project Showcase & Portfolio Management System
 * Frontend Interactive Controller
 */

(() => {
  'use strict';

  // ========================================================
  // Theme Toggle (Dark / Light)
  // ========================================================
  const themeToggle = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem('theme') || 'dark';

  function applyTheme(theme) {
    if (theme === 'light') {
      document.documentElement.setAttribute('data-theme', 'light');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    localStorage.setItem('theme', theme);

    if (themeToggle) {
      themeToggle.setAttribute('aria-label', `Switch to ${theme === 'light' ? 'dark' : 'light'} theme`);
    }
  }

  applyTheme(savedTheme);

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
      const next = current === 'light' ? 'dark' : 'light';
      applyTheme(next);
    });
  }

  // ========================================================
  // Mobile Navigation Menu Toggle
  // ========================================================
  const mobileToggle = document.getElementById('mobileToggle');
  const navMenu = document.getElementById('navMenu');

  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target)) {
        navMenu.classList.remove('active');
      }
    });
  }

  // ========================================================
  // Projects Search, Filter & Sort Engine
  // ========================================================
  const searchInput = document.getElementById('projectSearch');
  const searchClear = document.getElementById('searchClear');
  const categoryFilter = document.getElementById('categoryFilter');
  const statusFilter = document.getElementById('statusFilter');
  const sortSelect = document.getElementById('sortProjects');
  const resetBtn = document.getElementById('resetFilters');
  const grid = document.getElementById('projectGrid');
  const emptyState = document.getElementById('noResults');
  const showingCount = document.getElementById('showingCount');

  if (grid) {
    const cards = Array.from(grid.querySelectorAll('.project-card'));

    function filterAndSortProjects() {
      const q = (searchInput?.value || '').trim().toLowerCase();
      const cat = (categoryFilter?.value || '').toLowerCase();
      const status = (statusFilter?.value || '').toLowerCase();
      const sortBy = sortSelect?.value || 'updated';

      // Toggle search clear button
      if (searchClear) {
        searchClear.hidden = q.length === 0;
      }

      // Filter visible cards
      const visibleCards = cards.filter(card => {
        const name = (card.dataset.name || '').toLowerCase();
        const desc = (card.dataset.description || '').toLowerCase();
        const tech = (card.dataset.technologies || '').toLowerCase();
        const cardCat = (card.dataset.category || '').toLowerCase();
        const cardStatus = (card.dataset.status || '').toLowerCase();

        const matchesQuery = !q || name.includes(q) || desc.includes(q) || tech.includes(q);
        const matchesCategory = !cat || cardCat === cat;
        const matchesStatus = !status || cardStatus === status;

        return matchesQuery && matchesCategory && matchesStatus;
      });

      // Sort visible cards
      visibleCards.sort((a, b) => {
        if (sortBy === 'name-asc') {
          return (a.dataset.name || '').localeCompare(b.dataset.name || '');
        } else if (sortBy === 'name-desc') {
          return (b.dataset.name || '').localeCompare(a.dataset.name || '');
        } else if (sortBy === 'created') {
          return (b.dataset.created || '').localeCompare(a.dataset.created || '');
        } else {
          // Default: updated
          return (b.dataset.updated || '').localeCompare(a.dataset.updated || '');
        }
      });

      // Re-order and toggle display
      cards.forEach(card => {
        card.style.display = 'none';
      });

      visibleCards.forEach(card => {
        card.style.display = '';
        grid.appendChild(card);
      });

      // Update counter
      if (showingCount) {
        showingCount.textContent = visibleCards.length;
      }

      // Empty state
      if (emptyState) {
        emptyState.hidden = visibleCards.length > 0;
      }
    }

    searchInput?.addEventListener('input', filterAndSortProjects);
    categoryFilter?.addEventListener('change', filterAndSortProjects);
    statusFilter?.addEventListener('change', filterAndSortProjects);
    sortSelect?.addEventListener('change', filterAndSortProjects);

    searchClear?.addEventListener('click', () => {
      if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
        filterAndSortProjects();
      }
    });

    resetBtn?.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      if (categoryFilter) categoryFilter.value = '';
      if (statusFilter) statusFilter.value = '';
      if (sortSelect) sortSelect.value = 'updated';
      filterAndSortProjects();
    });
  }

  // ========================================================
  // Lightbox Modal for Gallery and Cover Images
  // ========================================================
  const lightbox = document.getElementById('lightboxModal');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxClose = document.getElementById('lightboxClose');

  function openLightbox(src, caption = '') {
    if (!lightbox || !lightboxImg) return;
    lightboxImg.src = src;
    if (lightboxCaption) lightboxCaption.textContent = caption;
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (lightbox) {
    document.querySelectorAll('[data-lightbox]').forEach(el => {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        const src = el.getAttribute('href') || el.dataset.lightboxSrc;
        const caption = el.dataset.lightboxCaption || '';
        if (src) openLightbox(src, caption);
      });
    });

    lightboxClose?.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && lightbox.classList.contains('active')) {
        closeLightbox();
      }
    });
  }

  // ========================================================
  // Toast Notification Helper
  // ========================================================
  window.showToast = function(message) {
    let toast = document.getElementById('toastNotice');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'toastNotice';
      toast.className = 'toast-notice';
      document.body.appendChild(toast);
    }
    toast.innerHTML = `<span>✔</span> <span>${message}</span>`;
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
    }, 3200);
  };

  // Share button copy handler
  const shareBtn = document.getElementById('shareProjectBtn');
  if (shareBtn) {
    shareBtn.addEventListener('click', () => {
      navigator.clipboard.writeText(window.location.href).then(() => {
        window.showToast('Project link copied to clipboard!');
      }).catch(() => {
        window.showToast('Copied to clipboard!');
      });
    });
  }

})();
