document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    window.lucide.createIcons();
  }

  const root = document.querySelector('[data-variants]');
  if (!root) {
    return;
  }

  const variants = JSON.parse(root.dataset.variants || '[]');
  const hidden = root.querySelector('input[name="variant_id"]');
  const price = document.querySelector('[data-live-price]');
  const stock = document.querySelector('[data-live-stock]');
  const selected = {};

  const render = () => {
    const match = variants.find((variant) => {
      return Object.entries(selected).every(([name, value]) => variant.attributes[name] === value);
    }) || variants[0];

    if (!match || !hidden) {
      return;
    }

    hidden.value = match.id;
    if (price) {
      price.textContent = match.formatted_price;
    }
    if (stock) {
      stock.textContent = match.stock > 0 ? match.stock + ' in demonstration stock' : 'Out of demonstration stock';
    }

    const button = root.querySelector('[data-add]');
    if (button) {
      button.disabled = match.stock < 1;
    }
  };

  root.querySelectorAll('.variant-option').forEach((button) => {
    button.addEventListener('click', () => {
      const name = button.dataset.attribute;
      selected[name] = button.dataset.value;
      root.querySelectorAll(`[data-attribute="${name}"]`).forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      render();
    });
  });

  root.querySelectorAll('.variant-group').forEach((group) => {
    const active = group.querySelector('.variant-option');
    if (active) {
      selected[active.dataset.attribute] = active.dataset.value;
      active.classList.add('is-active');
    }
  });

  render();

  document.querySelectorAll('[data-qty]').forEach((wrap) => {
    const input = wrap.querySelector('input');
    wrap.querySelectorAll('[data-step]').forEach((button) => {
      button.addEventListener('click', () => {
        const next = Math.min(20, Math.max(1, Number(input.value || 1) + Number(button.dataset.step)));
        input.value = next;
      });
    });
  });
});
