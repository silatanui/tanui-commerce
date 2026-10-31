document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    window.lucide.createIcons();
  }

  const applyTheme = window.__tanuiApplyAdminTheme || ((theme) => {
    const next = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    document.documentElement.classList.toggle('admin-theme-dark', next === 'dark');
    document.body.setAttribute('data-theme', next);
    document.body.classList.toggle('admin-theme-dark', next === 'dark');
    try {
      localStorage.setItem('tanui-admin-theme', next);
    } catch (e) {}
  });

  const saved = document.documentElement.getAttribute('data-theme') || 'light';
  applyTheme(saved);

  if (window.jQuery && window.jQuery.fn.DataTable) {
    window.jQuery('table.admin-datatable').each(function initAdminDataTable() {
      const $table = window.jQuery(this);
      if (window.jQuery.fn.DataTable.isDataTable($table)) {
        return;
      }

      $table.DataTable({
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [],
        autoWidth: false,
        language: {
          search: 'Filter',
          lengthMenu: 'Show _MENU_ items',
          info: 'Showing _START_ to _END_ of _TOTAL_',
          infoEmpty: 'No records',
          zeroRecords: 'No matching records',
        },
      });
    });
  }

  document.querySelectorAll('[data-add-row]').forEach((button) => {
    button.addEventListener('click', () => {
      const target = document.querySelector(button.dataset.addRow);
      const template = document.querySelector(button.dataset.template);
      if (!target || !template) {
        return;
      }
      const index = target.children.length;
      const html = template.innerHTML.replaceAll('__INDEX__', index);
      target.insertAdjacentHTML('beforeend', html);
      if (window.lucide) {
        window.lucide.createIcons();
      }
    });
  });
});
