/**
 * Project Showcase & Portfolio Management System
 * Admin Dashboard & CRUD Script
 */

(() => {
  'use strict';

  // ========================================================
  // Auto-Slug Generator
  // ========================================================
  const nameInput = document.getElementById('projectName');
  const slugInput = document.getElementById('projectSlug');

  function slugify(text) {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')           // Replace spaces with -
      .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
      .replace(/\-\-+/g, '-')         // Replace multiple - with single -
      .replace(/^-+/, '')             // Trim - from start of text
      .replace(/-+$/, '');            // Trim - from end of text
  }

  if (nameInput && slugInput) {
    let manualEdit = slugInput.dataset.manualEdit === 'true';

    slugInput.addEventListener('input', () => {
      manualEdit = true;
      slugInput.dataset.manualEdit = 'true';
    });

    nameInput.addEventListener('input', () => {
      if (!manualEdit || slugInput.value.trim() === '') {
        slugInput.value = slugify(nameInput.value);
      }
    });
  }

  // ========================================================
  // Thumbnail File Upload Preview
  // ========================================================
  const thumbInput = document.getElementById('thumbnailInput');
  const thumbPreview = document.getElementById('thumbnailPreview');
  const thumbDropZone = document.getElementById('thumbnailDropZone');

  if (thumbInput && thumbPreview) {
    function handleFile(file) {
      if (!file || !file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        thumbPreview.src = e.target.result;
        thumbPreview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }

    thumbInput.addEventListener('change', () => {
      if (thumbInput.files && thumbInput.files[0]) {
        handleFile(thumbInput.files[0]);
      }
    });

    if (thumbDropZone) {
      thumbDropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        thumbDropZone.classList.add('dragover');
      });
      thumbDropZone.addEventListener('dragleave', () => {
        thumbDropZone.classList.remove('dragover');
      });
      thumbDropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        thumbDropZone.classList.remove('dragover');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
          thumbInput.files = e.dataTransfer.files;
          handleFile(e.dataTransfer.files[0]);
        }
      });
    }
  }

  // ========================================================
  // Gallery Multi-Image Upload Preview
  // ========================================================
  const galleryInput = document.getElementById('galleryInput');
  const galleryPreviewContainer = document.getElementById('galleryPreviewContainer');

  if (galleryInput && galleryPreviewContainer) {
    galleryInput.addEventListener('change', () => {
      galleryPreviewContainer.innerHTML = '';
      if (!galleryInput.files) return;

      Array.from(galleryInput.files).forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = (e) => {
          const item = document.createElement('div');
          item.className = 'gallery-thumb-item';
          item.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
          galleryPreviewContainer.appendChild(item);
        };
        reader.readAsDataURL(file);
      });
    });
  }

  // ========================================================
  // Quick "Set to Today" Button for Dates
  // ========================================================
  const setTodayBtn = document.getElementById('setTodayBtn');
  const lastUpdatedInput = document.getElementById('lastUpdatedInput');

  if (setTodayBtn && lastUpdatedInput) {
    setTodayBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const today = new Date().toISOString().split('T')[0];
      lastUpdatedInput.value = today;
    });
  }

  // ========================================================
  // Delete Confirmation Modal
  // ========================================================
  const deleteModal = document.getElementById('deleteModal');
  const deleteModalForm = document.getElementById('deleteModalForm');
  const deleteModalTitle = document.getElementById('deleteModalItemTitle');
  const deleteModalCancel = document.getElementById('deleteModalCancel');

  if (deleteModal) {
    document.querySelectorAll('[data-delete-btn]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const actionUrl = btn.dataset.deleteAction;
        const itemTitle = btn.dataset.deleteTitle || 'this item';
        if (deleteModalForm && actionUrl) {
          deleteModalForm.action = actionUrl;
        }
        if (deleteModalTitle) {
          deleteModalTitle.textContent = itemTitle;
        }
        deleteModal.classList.add('active');
      });
    });

    deleteModalCancel?.addEventListener('click', () => {
      deleteModal.classList.remove('active');
    });

    deleteModal.addEventListener('click', (e) => {
      if (e.target === deleteModal) {
        deleteModal.classList.remove('active');
      }
    });
  }

  // ========================================================
  // Chart.js Admin Analytics Initialization
  // ========================================================
  window.initAdminCharts = function(categoryLabels, categoryCounts, statusLabels, statusCounts) {
    if (typeof Chart === 'undefined') return;

    // 1. Category Distribution Doughnut Chart
    const catCanvas = document.getElementById('categoryChart');
    if (catCanvas) {
      new Chart(catCanvas, {
        type: 'doughnut',
        data: {
          labels: categoryLabels,
          datasets: [{
            data: categoryCounts,
            backgroundColor: [
              '#6366f1', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#64748b'
            ],
            borderColor: '#1e293b',
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right',
              labels: {
                color: '#94a3b8',
                boxWidth: 12,
                font: { size: 11, family: "'Plus Jakarta Sans', sans-serif" }
              }
            }
          }
        }
      });
    }

    // 2. Project Status Bar Chart
    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
      new Chart(statusCanvas, {
        type: 'bar',
        data: {
          labels: statusLabels,
          datasets: [{
            label: 'Projects',
            data: statusCounts,
            backgroundColor: ['#10b981', '#f59e0b', '#64748b'],
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1, color: '#94a3b8' },
              grid: { color: 'rgba(255, 255, 255, 0.05)' }
            },
            x: {
              ticks: { color: '#94a3b8' },
              grid: { display: false }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    }
  };

})();
