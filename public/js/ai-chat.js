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
  const commerceEndpoint = root.dataset.commerceEndpoint;
  const storeKey = root.dataset.store || 'store';
  const tokenKey = `tanui-store-assistant-token:${storeKey}`;
  const tokensKey = `tanui-store-assistant-tokens:${storeKey}`;
  const legacyTokenKey = `tanui-concierge-token:${storeKey}`;
  const legacyTokensKey = `tanui-concierge-tokens:${storeKey}`;

  // one-time migrate old Concierge keys
  if (!localStorage.getItem(tokenKey) && localStorage.getItem(legacyTokenKey)) {
    localStorage.setItem(tokenKey, localStorage.getItem(legacyTokenKey));
  }
  if (!localStorage.getItem(tokensKey) && localStorage.getItem(legacyTokensKey)) {
    localStorage.setItem(tokensKey, localStorage.getItem(legacyTokensKey));
  }

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

  let historyLoadedFor = null;

  const open = async () => {
    document.body.classList.remove('cart-drawer-open');
    document.getElementById('cart-drawer')?.classList.remove('is-open');
    document.getElementById('cart-drawer')?.setAttribute('aria-hidden', 'true');
    const cartBackdrop = document.getElementById('cart-drawer-backdrop');
    if (cartBackdrop) cartBackdrop.hidden = true;
    document.getElementById('cart-drawer-toggle')?.setAttribute('aria-expanded', 'false');

    shell.hidden = false;
    backdrop.hidden = false;
    document.body.classList.add('tc-open');
    toggle.setAttribute('aria-expanded', 'true');
    await restoreActiveConversation();
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

  async function restoreActiveConversation({ force = false } = {}) {
    const token = localStorage.getItem(tokenKey);
    if (!token) return false;

    const hasThread = !!messages.querySelector('.tc__bubble');
    if (!force && hasThread && historyLoadedFor === token) {
      return true;
    }

    await loadConversation(token);
    historyLoadedFor = token;
    return true;
  }

  toggle.addEventListener('click', () => (shell.hidden ? open() : close()));
  closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', close);

  window.TanuiAssistant = {
    open,
    close,
    isOpen: () => !shell.hidden,
  };
  document.getElementById('tc-details-close')?.addEventListener('click', () => { details.hidden = true; });
  document.getElementById('tc-history-toggle')?.addEventListener('click', () => {
    sidebar.classList.toggle('is-open');
  });

  const introHtml = () => `
    <div class="tc__intro">
      <p class="tc__eyebrow">${escapeHtml(root.dataset.storeName || 'Store')}</p>
      <strong>Shop from chat — product to delivery</strong>
      <p>Find items, add to cart, check out, confirm the demo payment, then track delivery. Answers stay grounded in this demonstration catalogue. No real payments.</p>
      <div class="tc__chips">
        <button type="button" class="tc__chip" data-prompt="Find a product for me">Find a product</button>
        <button type="button" class="tc__chip" data-action="cart">View cart</button>
        <button type="button" class="tc__chip" data-action="checkout">Checkout</button>
        <button type="button" class="tc__chip" data-prompt="Track my order">Track an order</button>
      </div>
    </div>`;

  document.getElementById('tc-new')?.addEventListener('click', () => {
    localStorage.removeItem(tokenKey);
    historyLoadedFor = null;
    messages.innerHTML = introHtml();
    details.hidden = true;
    activeProducts = {};
    refreshConversations();
    icons();
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

  const appendWidget = (html) => {
    const wrap = document.createElement('div');
    wrap.className = 'tc__widget';
    wrap.innerHTML = html;
    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
    icons();
    return wrap;
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
            <button type="button" class="tc__btn tc__btn--ghost" data-cart="${product.id}" ${product.variant_id && product.in_stock ? '' : 'disabled'}>Add to cart</button>
            <button type="button" class="tc__btn tc__btn--solid" data-buy="${product.id}" ${product.variant_id && product.in_stock ? '' : 'disabled'}>Buy now</button>
          </div>
        </div>`;
      wrap.appendChild(card);
    });

    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
    icons();
  };

  const renderCart = (cart) => {
    if (!cart) return;
    if (!cart.count) {
      appendWidget(`
        <div class="tc__panel">
          <h4>Cart</h4>
          <p class="tc__panel-note">Your demonstration cart is empty.</p>
          <button type="button" class="tc__btn tc__btn--solid" data-prompt="Find a product for me">Find a product</button>
        </div>`);
      return;
    }

    const lines = (cart.lines || []).map((line) => `
      <div class="tc__line" data-item="${line.item_id}">
        <img src="${line.image || ''}" alt="">
        <div>
          <strong>${escapeHtml(line.name)}</strong>
          <span>${escapeHtml(line.unit_label)} each</span>
          <div class="tc__qty">
            <button type="button" data-qty="${line.item_id}" data-delta="-1" aria-label="Decrease">−</button>
            <em>${line.quantity}</em>
            <button type="button" data-qty="${line.item_id}" data-delta="1" data-max="${line.max}" aria-label="Increase">+</button>
            <button type="button" class="tc__linkish" data-remove="${line.item_id}">Remove</button>
          </div>
        </div>
        <b>${escapeHtml(line.line_label)}</b>
      </div>`).join('');

    appendWidget(`
      <div class="tc__panel" data-widget="cart">
        <h4>Your cart · ${cart.count} item${cart.count === 1 ? '' : 's'}</h4>
        <div class="tc__lines">${lines}</div>
        <div class="tc__totals">
          <div><span>Subtotal</span><strong>${escapeHtml(cart.subtotal_label)}</strong></div>
          ${cart.discount > 0 ? `<div><span>Discount</span><strong>−${escapeHtml(cart.discount_label)}</strong></div>` : ''}
          <div><span>Est. delivery*</span><strong>${escapeHtml(cart.shipping_hint)}</strong></div>
          <div class="tc__totals-grand"><span>Estimate</span><strong>${escapeHtml(cart.total_estimate_label)}</strong></div>
        </div>
        <p class="tc__panel-note">*Final delivery cost is chosen at checkout. Sample prices · no real payments.</p>
        <button type="button" class="tc__btn tc__btn--solid tc__btn--block" data-action="checkout">Continue to checkout</button>
      </div>`);
  };

  const renderCheckout = (ui) => {
    const methods = (ui.shipping_methods || []).map((m, i) => `
      <label class="tc__ship">
        <input type="radio" name="tc_shipping" value="${escapeHtml(m.key)}" ${i === 0 ? 'checked' : ''}>
        <span>
          <strong>${escapeHtml(m.label)}</strong>
          <em>${escapeHtml(m.detail || '')}</em>
        </span>
        <b>${escapeHtml(m.cost_label)}</b>
      </label>`).join('');

    appendWidget(`
      <form class="tc__panel tc__checkout" data-widget="checkout">
        <h4>Checkout details</h4>
        <p class="tc__panel-note">Demonstration order only. Card fields are not accepted.</p>
        <div class="tc__fields">
          <label>Full name<input name="full_name" required maxlength="120" autocomplete="name"></label>
          <label>Email<input type="email" name="email" required maxlength="160" autocomplete="email"></label>
          <label>Phone<input name="phone" required maxlength="40" autocomplete="tel"></label>
          <label class="tc__span2">Address<input name="address_line" required maxlength="180" autocomplete="street-address"></label>
          <label>City<input name="city" required maxlength="80" autocomplete="address-level2"></label>
          <label>Postal code<input name="postal_code" maxlength="20" autocomplete="postal-code"></label>
          <label class="tc__span2">Country<input name="country" required maxlength="80" value="Kenya" autocomplete="country-name"></label>
        </div>
        <div class="tc__ships">${methods}</div>
        <button type="submit" class="tc__btn tc__btn--solid tc__btn--block">Continue to payment</button>
      </form>`);
  };

  const renderPayment = (ui) => {
    const cart = ui.cart || {};
    const draft = ui.draft || {};
    const method = (ui.shipping_methods || []).find((m) => m.key === draft.shipping_method);
    appendWidget(`
      <div class="tc__panel" data-widget="payment">
        <h4>Demonstration payment</h4>
        <p class="tc__panel-note">No card number, CVV, or payment token is collected. Confirming places a sample order with payment status <strong>not processed</strong>.</p>
        <ul class="tc__summary">
          <li><span>Ship to</span><strong>${escapeHtml(draft.full_name || '')}, ${escapeHtml(draft.city || '')}</strong></li>
          <li><span>Email</span><strong>${escapeHtml(draft.email || '')}</strong></li>
          <li><span>Delivery</span><strong>${escapeHtml(method?.label || draft.shipping_method || '')}</strong></li>
          <li><span>Cart</span><strong>${escapeHtml(cart.subtotal_label || '')}</strong></li>
        </ul>
        <button type="button" class="tc__btn tc__btn--solid tc__btn--block" data-action="confirm-payment">Place demo order</button>
        <button type="button" class="tc__btn tc__btn--ghost tc__btn--block" data-action="checkout">Edit details</button>
      </div>`);
  };

  const renderOrder = (order) => {
    if (!order) return;
    const items = (order.items || []).map((i) => `<li><span>${escapeHtml(i.name)} × ${i.quantity}</span><strong>${escapeHtml(i.line_label)}</strong></li>`).join('');
    appendWidget(`
      <div class="tc__panel" data-widget="order">
        <h4>Order confirmed</h4>
        <p class="tc__order-num">${escapeHtml(order.order_number)}</p>
        <p class="tc__panel-note">Demo order · payment ${escapeHtml(order.payment_status)} · ${escapeHtml(order.status_label)}</p>
        <ul class="tc__summary">${items}
          <li><span>Shipping</span><strong>${escapeHtml(order.shipping_label)}</strong></li>
          <li class="tc__totals-grand"><span>Total</span><strong>${escapeHtml(order.total_label)}</strong></li>
        </ul>
        <button type="button" class="tc__btn tc__btn--solid tc__btn--block" data-track="${escapeHtml(order.order_number)}">Track delivery</button>
        ${order.url ? `<a class="tc__details-link" href="${escapeHtml(order.url)}">Open order page</a>` : ''}
      </div>`);
  };

  const renderDelivery = (order) => {
    if (!order) return;
    const steps = (order.timeline || []).map((s) => `
      <li class="tc__step is-${escapeHtml(s.state)}">
        <span></span>
        <div>
          <strong>${escapeHtml(s.label)}</strong>
        </div>
      </li>`).join('');

    appendWidget(`
      <div class="tc__panel" data-widget="delivery">
        <h4>Delivery · ${escapeHtml(order.order_number)}</h4>
        <p class="tc__panel-note">${escapeHtml(order.shipment?.method || '')}${order.shipment?.tracking_reference ? ` · ${escapeHtml(order.shipment.tracking_reference)}` : ''}</p>
        <ol class="tc__timeline">${steps}</ol>
        <p class="tc__panel-note">Status updates follow the store’s demonstration fulfilment. Sample ETAs only.</p>
      </div>`);
  };

  const renderTrackPrompt = () => {
    appendWidget(`
      <form class="tc__panel" data-widget="track">
        <h4>Track an order</h4>
        <p class="tc__panel-note">Enter your demonstration order number. Email is required if this browser did not place the order.</p>
        <div class="tc__fields">
          <label class="tc__span2">Order number<input name="order_number" required placeholder="TC-…" maxlength="40"></label>
          <label class="tc__span2">Email<input type="email" name="email" maxlength="160" placeholder="Used at checkout"></label>
        </div>
        <button type="submit" class="tc__btn tc__btn--solid tc__btn--block">Track delivery</button>
      </form>`);
  };

  const renderUi = (ui, products) => {
    if (!ui) {
      renderProductCards(products || []);
      return;
    }
    switch (ui.step) {
      case 'product':
      case 'browse':
        renderProductCards(ui.products || products || []);
        break;
      case 'cart':
        renderCart(ui.cart);
        break;
      case 'checkout':
        renderCheckout(ui);
        break;
      case 'payment':
        renderPayment(ui);
        break;
      case 'order':
        renderOrder(ui.order);
        break;
      case 'delivery':
        renderDelivery(ui.order);
        break;
      case 'track_prompt':
        renderTrackPrompt();
        break;
      default:
        renderProductCards(products || ui.products || []);
    }
  };

  async function commerce(action, payload = {}) {
    const res = await fetch(commerceEndpoint, {
      method: 'POST',
      headers: jsonHeaders(),
      body: JSON.stringify({
        action,
        session_token: localStorage.getItem(tokenKey) || null,
        ...payload,
      }),
    });
    const data = await res.json();
    if (data.session_token) saveToken(data.session_token);
    if (!res.ok || data.ok === false) {
      throw new Error(data.message || 'Commerce request failed.');
    }
    return data;
  }

  async function runCommerce(action, payload = {}, silent = false) {
    messages.querySelector('.tc__intro')?.remove();
    if (!silent) {
      const pending = appendBubble('assistant', 'Working…');
      pending.classList.add('is-pending');
      try {
        const data = await commerce(action, payload);
        pending.remove();
        if (data.reply) appendBubble('assistant', data.reply);
        renderUi(data.ui, data.products);
        appendMeta('Demo commerce · no real payments');
      } catch (err) {
        pending.remove();
        appendBubble('assistant', err.message || 'Could not complete that step.');
      } finally {
        icons();
      }
      return;
    }

    const data = await commerce(action, payload);
    if (data.reply) appendBubble('assistant', data.reply);
    renderUi(data.ui, data.products);
    return data;
  }

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
      <button type="button" class="tc__btn tc__btn--ghost tc__btn--block" data-cart="${product.id}" ${product.variant_id && product.in_stock ? '' : 'disabled'}>Add to cart</button>
      <button type="button" class="tc__btn tc__btn--solid tc__btn--block" data-buy="${product.id}" ${product.variant_id && product.in_stock ? '' : 'disabled'}>Buy now</button>
      <a class="tc__details-link" href="${product.url}">View full product page</a>`;
    icons();
  };

  async function addToCart(product) {
    if (!product?.variant_id) return;
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
  }

  // Global action / chip handlers
  document.getElementById('tc-quick')?.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.dataset.action;
    if (action === 'cart') runCommerce('cart.summary');
    if (action === 'checkout') runCommerce('checkout.start');
    if (action === 'track') {
      messages.querySelector('.tc__intro')?.remove();
      appendBubble('assistant', 'Share your demonstration order number to track delivery.');
      renderTrackPrompt();
    }
  });

  messages.addEventListener('click', async (event) => {
    const chip = event.target.closest('[data-prompt]');
    if (chip && !chip.closest('form')) {
      input.value = chip.dataset.prompt || '';
      form.requestSubmit();
      return;
    }

    const actionBtn = event.target.closest('[data-action]');
    if (actionBtn) {
      const action = actionBtn.dataset.action;
      if (action === 'cart') return runCommerce('cart.summary');
      if (action === 'checkout') return runCommerce('checkout.start');
      if (action === 'confirm-payment') return runCommerce('checkout.confirm');
      if (action === 'track') {
        appendBubble('assistant', 'Enter your order number to track delivery.');
        return renderTrackPrompt();
      }
    }

    const detailBtn = event.target.closest('[data-detail]');
    if (detailBtn) {
      const product = activeProducts[detailBtn.dataset.detail];
      if (product) showDetails(product);
      return;
    }

    const cartBtn = event.target.closest('[data-cart]');
    if (cartBtn) {
      const product = activeProducts[cartBtn.dataset.cart];
      cartBtn.disabled = true;
      try { await addToCart(product); }
      catch { appendMeta('Could not add to cart.'); }
      finally { cartBtn.disabled = false; }
      return;
    }

    const buyBtn = event.target.closest('[data-buy]');
    if (buyBtn) {
      const product = activeProducts[buyBtn.dataset.buy];
      if (!product?.variant_id) return;
      buyBtn.disabled = true;
      try {
        await runCommerce('buy_now', {
          product_id: product.id,
          variant_id: product.variant_id,
          quantity: 1,
        });
      } finally {
        buyBtn.disabled = false;
      }
      return;
    }

    const trackBtn = event.target.closest('[data-track]');
    if (trackBtn) {
      return runCommerce('order.track', { order_number: trackBtn.dataset.track });
    }

    const qtyBtn = event.target.closest('[data-qty]');
    if (qtyBtn) {
      const line = qtyBtn.closest('.tc__line');
      const current = Number(line?.querySelector('em')?.textContent || 1);
      const delta = Number(qtyBtn.dataset.delta);
      const max = Number(qtyBtn.dataset.max || 20);
      const next = Math.max(0, Math.min(max, current + delta));
      return runCommerce('cart.update', { item_id: Number(qtyBtn.dataset.qty), quantity: next });
    }

    const removeBtn = event.target.closest('[data-remove]');
    if (removeBtn) {
      return runCommerce('cart.remove', { item_id: Number(removeBtn.dataset.remove) });
    }
  });

  messages.addEventListener('submit', async (event) => {
    const checkoutForm = event.target.closest('form[data-widget="checkout"]');
    const trackForm = event.target.closest('form[data-widget="track"]');
    if (!checkoutForm && !trackForm) return;
    event.preventDefault();

    if (checkoutForm) {
      const fd = new FormData(checkoutForm);
      const draft = Object.fromEntries(fd.entries());
      draft.shipping_method = checkoutForm.querySelector('input[name="tc_shipping"]:checked')?.value
        || draft.tc_shipping
        || 'standard';
      delete draft.tc_shipping;
      const btn = checkoutForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        await runCommerce('checkout.save', { draft });
      } catch (err) {
        appendBubble('assistant', err.message || 'Could not save checkout details.');
      } finally {
        if (btn) btn.disabled = false;
      }
      return;
    }

    if (trackForm) {
      const fd = new FormData(trackForm);
      const btn = trackForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        await runCommerce('order.track', {
          order_number: fd.get('order_number'),
          email: fd.get('email') || null,
        });
      } catch (err) {
        appendBubble('assistant', err.message || 'Could not track that order.');
      } finally {
        if (btn) btn.disabled = false;
      }
    }
  });

  detailsBody.addEventListener('click', async (event) => {
    const cartBtn = event.target.closest('[data-cart]');
    const buyBtn = event.target.closest('[data-buy]');
    if (cartBtn) {
      const product = activeProducts[cartBtn.dataset.cart];
      cartBtn.disabled = true;
      try { await addToCart(product); } finally { cartBtn.disabled = false; }
    }
    if (buyBtn) {
      const product = activeProducts[buyBtn.dataset.buy];
      if (!product?.variant_id) return;
      buyBtn.disabled = true;
      try {
        await runCommerce('buy_now', {
          product_id: product.id,
          variant_id: product.variant_id,
          quantity: 1,
        });
      } finally {
        buyBtn.disabled = false;
      }
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
    historyLoadedFor = token;
    refreshConversations();
  });

  // Keep the last conversation ready even after leaving a storefront page.
  if (localStorage.getItem(tokenKey)) {
    restoreActiveConversation({ force: true }).catch(() => {});
  }

  async function loadConversation(token) {
    messages.innerHTML = '';
    details.hidden = true;
    activeProducts = {};
    try {
      const res = await fetch(`${messagesBase}/${encodeURIComponent(token)}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();
      if (data.session_token) saveToken(data.session_token);

      const rows = data.messages || [];
      if (!rows.length) {
        messages.innerHTML = introHtml();
        icons();
        return;
      }

      rows.forEach((msg) => {
        if (msg.role === 'user' || msg.role === 'assistant') {
          appendBubble(msg.role, msg.content);
        }
      });

      // Restore last commerce widget (cart / checkout / order / delivery) when available
      if (data.ui) {
        renderUi(data.ui, data.ui.products || []);
      }

      historyLoadedFor = data.session_token || token;
      messages.scrollTop = messages.scrollHeight;
      icons();
    } catch {
      appendBubble('assistant', 'Could not load that conversation.');
    }
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const message = (input.value || '').trim();
    if (!message) return;

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

      if (!res.ok || data.ok === false) {
        appendBubble('assistant', data.message || 'Request failed.');
        return;
      }

      if (data.session_token) saveToken(data.session_token);

      appendBubble('assistant', data.reply || 'No reply.');
      renderUi(data.ui, data.products || []);

      if (data.ui?.step === 'browse' || data.ui?.step === 'product' || (!data.ui && data.products?.length)) {
        const sourceLine = Array.isArray(data.sources) && data.sources.length
          ? `Sources: ${data.sources.map((s) => s.title).slice(0, 3).join(' · ')} · ${data.latency_ms || 0} ms`
          : `${data.latency_ms || 0} ms · ${data.model || ''}`.trim();
        if (sourceLine) appendMeta(sourceLine);
      } else {
        appendMeta('Demo commerce · no real payments');
      }
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
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;');
  }
});
