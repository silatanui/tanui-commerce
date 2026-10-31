(() => {
  const endpoint = document.body?.dataset?.aiSuggestUrl;
  if (!endpoint) return;

  const csrf =
    document.querySelector('meta[name="csrf-token"]')?.content ||
    document.querySelector('input[name="_token"]')?.value ||
    '';

  function contextFromForm(form) {
    const context = {};
    form.querySelectorAll('[name]').forEach((el) => {
      const name = el.getAttribute('name');
      if (!name || name === '_token' || name === '_method') return;
      if (el.type === 'file') return;
      if (el.type === 'checkbox') {
        context[name] = el.checked ? el.value || '1' : '';
        return;
      }
      context[name] = el.value;
    });
    return context;
  }

  function applyFields(form, fields) {
    Object.entries(fields || {}).forEach(([key, value]) => {
      const el =
        form.querySelector(`[name="${CSS.escape(key)}"]`) ||
        form.querySelector(`[data-ai-field="${CSS.escape(key)}"]`);
      if (!el || el.type === 'file') return;
      if (el.tagName === 'SELECT') {
        const option = Array.from(el.options).find(
          (opt) => opt.value === String(value) || opt.textContent.trim().toLowerCase() === String(value).toLowerCase()
        );
        if (option) el.value = option.value;
        else el.value = String(value);
      } else if (el.type === 'checkbox') {
        el.checked = ['1', 'true', 'yes', 'on'].includes(String(value).toLowerCase());
      } else {
        el.value = String(value);
      }
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
      el.classList.add('af-ai-touched');
      setTimeout(() => el.classList.remove('af-ai-touched'), 1200);
    });
  }

  async function requestSuggestion(resource, context, button) {
    const original = button.innerHTML;
    button.disabled = true;
    button.classList.add('is-loading');
    button.innerHTML = 'Thinking…';
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ resource, context }),
      });
      const data = await response.json();
      if (!response.ok || !data.ok) {
        throw new Error(data.message || 'AI suggestion failed');
      }
      return data.suggestion;
    } finally {
      button.disabled = false;
      button.classList.remove('is-loading');
      button.innerHTML = original;
      if (window.lucide) window.lucide.createIcons();
    }
  }

  document.addEventListener('click', async (event) => {
    const fillBtn = event.target.closest('[data-ai-fill]');
    const fieldBtn = event.target.closest('[data-ai-field-btn]');
    if (!fillBtn && !fieldBtn) return;

    const button = fillBtn || fieldBtn;
    const form = button.closest('form') || document.querySelector('.af-form');
    if (!form) return;

    const resource = button.getAttribute('data-ai-fill') || button.getAttribute('data-ai-resource');
    if (!resource) return;

    try {
      const suggestion = await requestSuggestion(resource, contextFromForm(form), button);
      applyFields(form, suggestion.fields || {});

      let tipHost = form.querySelector('.af-ai-tips');
      if (!tipHost) {
        tipHost = document.createElement('div');
        tipHost.className = 'af-ai-tips';
        form.prepend(tipHost);
      }
      const tips = suggestion.tips || [];
      tipHost.hidden = tips.length === 0;
      tipHost.innerHTML = tips.length
        ? `<strong>AI tips</strong><ul>${tips.map((t) => `<li>${String(t).replace(/</g, '&lt;')}</li>`).join('')}</ul>`
        : '';
    } catch (error) {
      alert(error.message || 'Could not load AI suggestions.');
    }
  });
})();
