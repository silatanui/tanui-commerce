document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    window.lucide.createIcons();
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
    });
  });
});
