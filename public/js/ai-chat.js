document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('ai-chat');
  if (!root) return;

  root.hidden = false;

  const toggle = document.getElementById('ai-chat-toggle');
  const shell = document.getElementById('tc-shell');
  const backdrop = document.getElementById('tc-backdrop');
  const closeBtn = document.getElementById('ai-chat-close');
  const form = document.getElementById('ai-chat-form');
  const input = document.getElementById('ai-chat-input');
  const messages = document.getElementById('ai-chat-messages');
  const sendBtn = document.getElementById('ai-chat-send');
  const convList = document.getElementById('tc-conv-list');
  const details = document.getElementById('tc-details');
  const detailsBody = document.getElementById('tc-details-body');
  const sidebar = document.getElementById('tc-sidebar');

  const endpoint = root.dataset.endpoint;
  const conversationsUrl = root.dataset.conversations;
  const messagesBase = root.dataset.messagesBase;
  const cartEndpoint = root.dataset.cartEndpoint;
  const storeKey = root.dataset.store || 'store';
  const tokenKey = `tanui-concierge-token:${storeKey}`;
  const tokensKey = `tanui-concierge-tokens:${storeKey}`;

  const csrf = form.querySelector('input[name="_token"]')?.value
    || document.querySelector('meta[name="csrf-token"]')?.content;

  let activeProducts = {};

  const icons = () => { if (window.lucide) window.lucide.createIcons(); };

  const getTokens = () => {
    try { return JSON.parse(localStorage.getItem(tokensKey) || '[]'); }
    catch { return []; }
  };

  const saveToken = (token) => {
    if (!token) return;
    localStorage.setItem(tokenKey, token);
    const list = getTokens().filter((t) => t !== token);
    list.unshift(token);
    localStorage.setItem(tokensKey, JSON.stringify(list.slice(0, 20)));
  };

  const open = () => {
    shell.hidden = false;
    backdrop.hidden = false;
    document.body.classList.add('tc-open');
    toggle.setAttribute('aria-expanded', 'true');
    refreshConversations();
    icons();
    input.focus();
  };

  const close = () => {
    shell.hidden = true;
    backdrop.hidden = true;
    document.body.classList.remove('tc-open');
    toggle.setAttribute('aria-expanded', 'false');
    details.hidden = true;
  };

  toggle.addEventListener('click', () => (shell.hidden ? open() : close()));
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);
  document.getElementById('tc-details-close')?.addEventListener('click', () => { details.hidden = true; });
  document.getElementById('tc-history-toggle')?.addEventListener('click', () => {
    sidebar.classList.toggle('is-open');
  });

  document.getElementById('tc-new')?.addEventListener('click', () => {
    localStorage.removeItem(tokenKey);
    messages.innerHTML = `
      <div class="tc__intro">
        <strong>New conversation</strong>
        <p>Ask about products, specs, or recommendations for ${root.dataset.storeName || 'this store'}.</p>
      </div>`;
    details.hidden = true;
    activeProducts = {};
    refreshConversations();
    input.focus();
  });

  const appendBubble = (role, text) => {
    const bubble = document.createElement('div');
    bubble.className = `tc__bubble tc__bubble--${role}`;
    bubble.textContent = text;
    messages.appendChild(bubble);
    messages.scrollTop = messages.scrollHeight;
    return bubble;
  };

  const appendMeta = (text) => {
    const meta = document.createElement('div');
    meta.className = 'tc__meta';
    meta.textContent = text;
    messages.appendChild(meta);
  };

  const renderProductCards = (products) => {
    if (!Array.isArray(products) || !products.length) return;

    const wrap = document.createElement('div');
    wrap.className = 'tc__cards';

    products.forEach((product) => {
      activeProducts[product.id] = product;
      const card = document.createElement('article');
      card.className = 'tc__card';
      card.innerHTML = `
        <img src="${product.image}" alt="" loading="lazy">
        <div class="tc__card-body">
          <div class="tc__card-top">
            <strong>${escapeHtml(product.name)}</strong>
            <span class="${product.in_stock ? 'is-stock' : 'is-out'}">${product.in_stock ? 'In stock' : 'Out of stock'}</span>
          </div>
          <div class="tc__card-price">${escapeHtml(product.price_label)}${product.compare_at ? ` <s>${escapeHtml(product.compare_at)}</s>` : ''}</div>
          <p>${escapeHtml(product.short || '')}</p>
          <div class="tc__card-actions">
            <button type="button" class="tc__btn tc__btn--ghost" data-detail="${product.id}">View details</button>
            <button type="button" class="tc__btn tc__btn--solid" data-cart="${product.id}" ${product.variant_id && product.in_stock ? '' : 'disabled'}>Add to cart</button>
          </div>
        </div>`;
      wrap.appendChild(card);
    });

    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
    icons();
  };

  const showDetails = (product) => {
    details.hidden = false;
    const specs = Object.entries(product.specs || {}).slice(0, 6)
      .map(([k, v]) => `<li><span>${escapeHtml(k)}</span><strong>${escapeHtml(String(v))}</strong></li>`)
      .join('');

    detailsBody.innerHTML = `
      <img class="tc__details-img" src="${product.image}" alt="">
      <div class="tc__details-price">${escapeHtml(product.price_label)}</div>
      <h3>${escapeHtml(product.name)}</h3>
      <p class="tc__details-status ${product.in_stock ? 'is-stock' : 'is-out'}">${product.in_stock ? 'In stock' : 'Out of stock'}${product.rating ? ` · ★ ${product.rating}` : ''}</p>
      <ul class="tc__specs">
        ${product.brand ? `<li><span>Brand</span><strong>${escapeHtml(product.brand)}</strong></li>` : ''}
        ${product.category ? `<li><span>Category</span><strong>${escapeHtml(product.category)}</strong></li>` : ''}
        ${specs}
      </ul>
      <button type="button" class="tc__btn tc__btn--solid tc__btn--block" data-cart="${product.id}" ${product.variant_id && product.in_stock ? '' : 'disabled'}>Add to cart</button>
      <a class="tc__details-link" href="${product.url}">View full product page</a>`;
    icons();
  };

  messages.addEventListener('click', async (event) => {
    const detailBtn = event.target.closest('[data-detail]');
    const cartBtn = event.target.closest('[data-cart]');

    if (detailBtn) {
      const product = activeProducts[detailBtn.dataset.detail];
      if (product) showDetails(product);
      return;
    }

    if (cartBtn) {
      const product = activeProducts[cartBtn.dataset.cart];
      if (!product?.variant_id) return;
      cartBtn.disabled = true;
      try {
        const res = await fetch(cartEndpoint, {
          method: 'POST',
          headers: jsonHeaders(),
          body: JSON.stringify({
            product_id: product.id,
            variant_id: product.variant_id,
            quantity: 1,
          }),
        });
        const data = await res.json();
        appendMeta(data.message || 'Added to demonstration cart.');
      } catch {
        appendMeta('Could not add to cart.');
      } finally {
        cartBtn.disabled = false;
      }
    }
  });

  detailsBody.addEventListener('click', async (event) => {
    const cartBtn = event.target.closest('[data-cart]');
    if (!cartBtn) return;
    const product = activeProducts[cartBtn.dataset.cart];
    if (!product?.variant_id) return;
    cartBtn.disabled = true;
    try {
      const res = await fetch(cartEndpoint, {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({ product_id: product.id, variant_id: product.variant_id, quantity: 1 }),
      });
      const data = await res.json();
      appendMeta(data.message || 'Added to demonstration cart.');
    } catch {
      appendMeta('Could not add to cart.');
    } finally {
      cartBtn.disabled = false;
    }
  });

  async function refreshConversations() {
    if (!conversationsUrl) return;
    try {
      const res = await fetch(conversationsUrl, {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({ tokens: getTokens() }),
      });
      const data = await res.json();
      const items = data.conversations || [];
      const active = localStorage.getItem(tokenKey);

      if (!items.length) {
        convList.innerHTML = '<p class="tc__empty">No saved conversations yet.</p>';
        return;
      }

      convList.innerHTML = items.map((item) => `
        <button type="button" class="tc__conv ${item.session_token === active ? 'is-active' : ''}" data-token="${item.session_token}">
          <strong>${escapeHtml(item.title)}</strong>
          <span>${escapeHtml(item.snippet || '')}</span>
          <em>${escapeHtml(item.updated_label || '')}</em>
        </button>`).join('');
    } catch {
      convList.innerHTML = '<p class="tc__empty">Unable to load conversations.</p>';
    }
  }

  convList.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-token]');
    if (!btn) return;
    const token = btn.dataset.token;
    saveToken(token);
    await loadConversation(token);
    refreshConversations();
  });

  async function loadConversation(token) {
    messages.innerHTML = '';
    details.hidden = true;
    try {
      const res = await fetch(`${messagesBase}/${encodeURIComponent(token)}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();
      (data.messages || []).forEach((msg) => {
        if (msg.role === 'user' || msg.role === 'assistant') {
          appendBubble(msg.role, msg.content);
        }
      });
    } catch {
      appendBubble('assistant', 'Could not load that conversation.');
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const message = (input.value || '').trim();
    if (!message) return;

    // clear intro on first send
    messages.querySelector('.tc__intro')?.remove();

    appendBubble('user', message);
    input.value = '';
    sendBtn.disabled = true;

    const pending = appendBubble('assistant', 'Searching catalogue…');
    pending.classList.add('is-pending');

    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({
          message,
          session_token: localStorage.getItem(tokenKey) || null,
        }),
      });
      const data = await res.json();
      pending.remove();

      if (!res.ok) {
        appendBubble('assistant', data.message || 'Request failed.');
        return;
      }

      if (data.session_token) saveToken(data.session_token);

      appendBubble('assistant', data.reply || 'No reply.');
      renderProductCards(data.products || []);

      const sourceLine = Array.isArray(data.sources) && data.sources.length
        ? `Sources: ${data.sources.map((s) => s.title).slice(0, 3).join(' · ')} · ${data.latency_ms || 0} ms`
        : `${data.latency_ms || 0} ms · ${data.model || ''}`.trim();
      appendMeta(sourceLine);
      refreshConversations();
    } catch {
      pending.remove();
      appendBubble('assistant', 'Network error while contacting the assistant.');
    } finally {
      sendBtn.disabled = false;
      input.focus();
      icons();
    }
  });

  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });

  function jsonHeaders() {
    return {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrf || '',
      'X-Requested-With': 'XMLHttpRequest',
    };
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;');
  }
});
