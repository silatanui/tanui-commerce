document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    window.lucide.createIcons();
  }

  const ux = document.getElementById('store-ux');
  const csrf = ux?.dataset.csrf
    || document.querySelector('meta[name="csrf-token"]')?.content
    || '';
  const cartUrl = ux?.dataset.cartUrl || '';
  const suggestUrl = ux?.dataset.suggestUrl || '';

  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');

  const jsonHeaders = (method = 'GET') => {
    const headers = {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrf,
    };
    if (method !== 'GET') {
      headers['Content-Type'] = 'application/json';
    }
    return headers;
  };

  /* ——— Toast ——— */
  const toastEl = document.getElementById('ux-toast');
  let toastTimer = null;
  const toast = (message) => {
    if (!toastEl || !message) return;
    toastEl.textContent = message;
    toastEl.hidden = false;
    toastEl.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toastEl.classList.remove('is-visible');
      toastEl.hidden = true;
    }, 2800);
  };

  /* ——— Cart drawer ——— */
  const drawer = document.getElementById('cart-drawer');
  const drawerBackdrop = document.getElementById('cart-drawer-backdrop');
  const drawerBody = document.getElementById('cart-drawer-body');
  const drawerFoot = document.getElementById('cart-drawer-foot');
  const drawerSubtotal = document.getElementById('cart-drawer-subtotal');
  const cartToggle = document.getElementById('cart-drawer-toggle');
  const cartCountEls = () => document.querySelectorAll('.cart-count');

  const setCartCount = (count) => {
    cartCountEls().forEach((el) => {
      el.textContent = String(count ?? 0);
    });
  };

  const renderDrawer = (payload) => {
    if (!drawerBody) return;
    const lines = payload.lines || [];
    setCartCount(payload.count ?? 0);

    if (!lines.length) {
      drawerBody.innerHTML = '<p class="cart-drawer__empty">Your demonstration cart is empty.</p>';
      if (drawerFoot) drawerFoot.hidden = true;
      return;
    }

    drawerBody.innerHTML = lines.map((line) => `
      <article class="cart-drawer__line" data-item-id="${line.item_id}">
        <img src="${escapeHtml(line.image || '')}" alt="" loading="lazy">
        <div class="cart-drawer__line-body">
          <a href="${escapeHtml(line.url || '#')}">${escapeHtml(line.name)}</a>
          <div class="cart-drawer__line-meta">
            <span>${escapeHtml(line.unit_label)}</span>
            <strong>${escapeHtml(line.line_label)}</strong>
          </div>
          <div class="cart-drawer__line-actions">
            <label class="visually-hidden" for="qty-${line.item_id}">Quantity</label>
            <input id="qty-${line.item_id}" type="number" min="1" max="${line.max || 20}" value="${line.quantity}" data-cart-qty="${line.item_id}">
            <button type="button" class="cart-drawer__remove" data-cart-remove="${line.item_id}">Remove</button>
          </div>
        </div>
      </article>
    `).join('');

    if (drawerSubtotal) drawerSubtotal.textContent = payload.subtotal_label || '—';
    if (drawerFoot) drawerFoot.hidden = false;
    if (window.lucide) window.lucide.createIcons();
  };

  const fetchCart = async () => {
    if (!cartUrl) return null;
    const res = await fetch(`${cartUrl}?drawer=1`, {
      headers: jsonHeaders(),
      credentials: 'same-origin',
    });
    if (!res.ok) throw new Error('Cart fetch failed');
    return res.json();
  };

  const openDrawer = async () => {
    if (!drawer) return;
    if (window.TanuiAssistant?.isOpen?.()) {
      window.TanuiAssistant.close();
    }
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    if (drawerBackdrop) drawerBackdrop.hidden = false;
    document.body.classList.add('cart-drawer-open');
    cartToggle?.setAttribute('aria-expanded', 'true');
    drawerBody.innerHTML = '<p class="cart-drawer__empty">Loading bag…</p>';
    try {
      const data = await fetchCart();
      renderDrawer(data);
    } catch {
      drawerBody.innerHTML = '<p class="cart-drawer__empty">Could not load the cart.</p>';
    }
  };

  const closeDrawer = () => {
    drawer?.classList.remove('is-open');
    drawer?.setAttribute('aria-hidden', 'true');
    if (drawerBackdrop) drawerBackdrop.hidden = true;
    document.body.classList.remove('cart-drawer-open');
    cartToggle?.setAttribute('aria-expanded', 'false');
  };

  cartToggle?.addEventListener('click', () => {
    if (drawer?.classList.contains('is-open')) closeDrawer();
    else openDrawer();
  });
  document.getElementById('cart-drawer-close')?.addEventListener('click', closeDrawer);
  drawerBackdrop?.addEventListener('click', closeDrawer);

  drawerBody?.addEventListener('change', async (event) => {
    const input = event.target.closest('[data-cart-qty]');
    if (!input || !cartUrl) return;
    const quantity = Number(input.value || 1);
    try {
      const res = await fetch(cartUrl, {
        method: 'PATCH',
        headers: jsonHeaders('PATCH'),
        credentials: 'same-origin',
        body: JSON.stringify({ item_id: Number(input.dataset.cartQty), quantity }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Update failed');
      renderDrawer(data);
      toast(data.message || 'Cart updated.');
    } catch {
      toast('Could not update quantity.');
      openDrawer();
    }
  });

  drawerBody?.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-cart-remove]');
    if (!btn || !cartUrl) return;
    try {
      const res = await fetch(cartUrl, {
        method: 'DELETE',
        headers: jsonHeaders('DELETE'),
        credentials: 'same-origin',
        body: JSON.stringify({ item_id: Number(btn.dataset.cartRemove) }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Remove failed');
      renderDrawer(data);
      toast(data.message || 'Item removed.');
    } catch {
      toast('Could not remove item.');
    }
  });

  /* ——— AJAX add to cart ——— */
  document.querySelectorAll('form.js-add-to-cart').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      if (!cartUrl) return;
      event.preventDefault();
      const fd = new FormData(form);
      const payload = {
        product_id: Number(fd.get('product_id')),
        variant_id: Number(fd.get('variant_id')),
        quantity: Number(fd.get('quantity') || 1),
      };
      const button = form.querySelector('[data-add], button[type="submit"]');
      if (button) button.disabled = true;
      try {
        const res = await fetch(cartUrl, {
          method: 'POST',
          headers: jsonHeaders('POST'),
          credentials: 'same-origin',
          body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!res.ok) {
          const msg = data.message || Object.values(data.errors || {})[0]?.[0] || 'Could not add to cart.';
          toast(msg);
          return;
        }
        renderDrawer(data);
        toast(data.message || 'Added to cart.');
        openDrawer();
      } catch {
        toast('Network error while adding to cart.');
      } finally {
        if (button) button.disabled = false;
      }
    });
  });

  /* ——— Live search ——— */
  const searchForm = document.querySelector('[data-live-search]');
  const searchInput = document.getElementById('store-search');
  const suggestBox = document.getElementById('search-suggest');
  let suggestTimer = null;

  const hideSuggest = () => {
    if (!suggestBox || !searchInput) return;
    suggestBox.hidden = true;
    suggestBox.innerHTML = '';
    searchInput.setAttribute('aria-expanded', 'false');
  };

  const showSuggest = (html) => {
    if (!suggestBox || !searchInput) return;
    suggestBox.innerHTML = html;
    suggestBox.hidden = !html;
    searchInput.setAttribute('aria-expanded', html ? 'true' : 'false');
    if (window.lucide) window.lucide.createIcons();
  };

  const runSuggest = async (q) => {
    if (!suggestUrl || q.trim().length < 2) {
      hideSuggest();
      return;
    }
    try {
      const res = await fetch(`${suggestUrl}?q=${encodeURIComponent(q.trim())}`, {
        headers: jsonHeaders(),
        credentials: 'same-origin',
      });
      const data = await res.json();
      const results = data.results || [];
      if (!results.length) {
        showSuggest(`
          <div class="search-suggest__empty">No matches for “${escapeHtml(q.trim())}”.</div>
          <a class="search-suggest__all" href="${escapeHtml(data.search_url || '#')}">Search the full catalogue</a>
        `);
        return;
      }
      showSuggest(`
        ${results.map((item) => `
          <a class="search-suggest__item" role="option" href="${escapeHtml(item.url)}">
            <img src="${escapeHtml(item.image || '')}" alt="" loading="lazy">
            <span>
              <strong>${escapeHtml(item.name)}</strong>
              <em>${escapeHtml(item.brand || '')}</em>
            </span>
            <b>${escapeHtml(item.price_label)}</b>
          </a>
        `).join('')}
        <a class="search-suggest__all" href="${escapeHtml(data.search_url || '#')}">View all results</a>
      `);
    } catch {
      hideSuggest();
    }
  };

  searchInput?.addEventListener('input', () => {
    clearTimeout(suggestTimer);
    suggestTimer = setTimeout(() => runSuggest(searchInput.value), 220);
  });
  searchInput?.addEventListener('focus', () => {
    if (searchInput.value.trim().length >= 2) runSuggest(searchInput.value);
  });
  document.addEventListener('click', (event) => {
    if (!searchForm?.contains(event.target)) hideSuggest();
  });
  searchInput?.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') hideSuggest();
  });

  /* ——— Open assistant from hero / deep link ——— */
  const openAssistant = () => {
    closeDrawer();
    if (window.TanuiAssistant?.open) {
      window.TanuiAssistant.open();
      return;
    }
    document.getElementById('ai-chat-toggle')?.click();
  };

  document.querySelectorAll('[data-open-assistant]').forEach((btn) => {
    btn.addEventListener('click', (event) => {
      event.preventDefault();
      openAssistant();
    });
  });

  const params = new URLSearchParams(window.location.search);
  if (params.get('open_assistant') === '1') {
    setTimeout(openAssistant, 350);
    params.delete('open_assistant');
    const next = `${window.location.pathname}${params.toString() ? `?${params}` : ''}${window.location.hash}`;
    window.history.replaceState({}, '', next);
  }

  /* ——— Product variants ——— */
  const root = document.querySelector('[data-variants]');
  if (!root) return;

  const variants = JSON.parse(root.dataset.variants || '[]');
  const hidden = root.querySelector('input[name="variant_id"]');
  const price = document.querySelector('[data-live-price]');
  const stock = document.querySelector('[data-live-stock]');
  const selected = {};

  const render = () => {
    const match = variants.find((variant) => {
      return Object.entries(selected).every(([name, value]) => variant.attributes[name] === value);
    }) || variants[0];

    if (!match || !hidden) return;

    hidden.value = match.id;
    if (price) price.textContent = match.formatted_price;
    if (stock) {
      stock.textContent = match.stock > 0
        ? `${match.stock} in demonstration stock`
        : 'Out of demonstration stock';
    }

    const button = root.querySelector('[data-add]');
    if (button) button.disabled = match.stock < 1;
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
