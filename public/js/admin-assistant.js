(() => {
  const root = document.getElementById('admin-assistant');
  if (!root) return;

  const endpoint = root.dataset.endpoint;
  const thread = document.getElementById('ai-thread');
  const form = document.getElementById('ai-form');
  const input = document.getElementById('ai-input');
  const sendBtn = document.getElementById('ai-send');
  const statusEl = document.getElementById('ai-status');
  const actionsWrap = document.getElementById('ai-latest-actions');
  const actionsList = document.getElementById('ai-actions-list');
  const csrf = form.querySelector('input[name="_token"]')?.value || '';

  const history = [];
  const charts = [];

  const isDark = () =>
    document.documentElement.classList.contains('admin-theme-dark') ||
    document.documentElement.getAttribute('data-theme') === 'dark';

  const palette = () =>
    isDark()
      ? {
          ink: '#f2f2f2',
          muted: '#b8b8b8',
          grid: '#3a3a3a',
          bars: ['#ffffff', '#d0d0d0', '#a8a8a8', '#808080', '#585858'],
          fill: 'rgba(255,255,255,0.14)',
        }
      : {
          ink: '#111111',
          muted: '#555555',
          grid: '#e0e0e0',
          bars: ['#111111', '#3a3a3a', '#666666', '#909090', '#b8b8b8'],
          fill: 'rgba(17,17,17,0.08)',
        };

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;');
  }

  function showEmpty() {
    thread.innerHTML = `
      <div class="ai-empty">
        <strong>Ask about performance</strong>
        Start with a suggested prompt, or type your own question about sales, conversion, stock risk, or Kenya traffic.
      </div>`;
  }

  showEmpty();

  function appendMessage(role, payload) {
    if (thread.querySelector('.ai-empty')) {
      thread.innerHTML = '';
    }

    const el = document.createElement('article');
    el.className = `ai-msg ai-msg--${role}`;

    const meta = document.createElement('div');
    meta.className = 'ai-msg__meta';
    meta.textContent = role === 'user' ? 'You' : 'Assistant';
    el.appendChild(meta);

    if (typeof payload === 'string') {
      const text = document.createElement('div');
      text.className = 'ai-msg__text';
      text.textContent = payload;
      el.appendChild(text);
    } else {
      const text = document.createElement('div');
      text.className = 'ai-msg__text';
      text.textContent = payload.answer || '';
      el.appendChild(text);

      if (Array.isArray(payload.actions) && payload.actions.length) {
        const list = document.createElement('ol');
        list.className = 'ai-actions';
        payload.actions.forEach((action) => {
          const li = document.createElement('li');
          li.textContent = action;
          list.appendChild(li);
        });
        el.appendChild(list);
        renderSideActions(payload.actions);
      }

      (payload.tables || []).forEach((table) => el.appendChild(renderTable(table)));
      (payload.charts || []).forEach((chart) => el.appendChild(renderChart(chart)));
    }

    thread.appendChild(el);
    thread.scrollTop = thread.scrollHeight;
    return el;
  }

  function renderSideActions(actions) {
    actionsList.innerHTML = '';
    actions.forEach((action) => {
      const li = document.createElement('li');
      li.textContent = action;
      actionsList.appendChild(li);
    });
    actionsWrap.hidden = false;
  }

  function renderTable(table) {
    const wrap = document.createElement('div');
    wrap.className = 'ai-table-wrap';
    const title = document.createElement('h3');
    title.style.padding = '.65rem .65rem 0';
    title.textContent = table.title || 'Table';
    wrap.appendChild(title);

    const html = [`<table class="ai-table"><thead><tr>`];
    (table.headers || []).forEach((header) => {
      html.push(`<th>${escapeHtml(header)}</th>`);
    });
    html.push('</tr></thead><tbody>');
    (table.rows || []).forEach((row) => {
      html.push('<tr>');
      (row || []).forEach((cell) => html.push(`<td>${escapeHtml(cell)}</td>`));
      html.push('</tr>');
    });
    html.push('</tbody></table>');
    wrap.insertAdjacentHTML('beforeend', html.join(''));
    return wrap;
  }

  function renderChart(chart) {
    const block = document.createElement('div');
    block.className = 'ai-chart-block';
    const title = document.createElement('h3');
    title.textContent = chart.title || 'Chart';
    block.appendChild(title);

    const frame = document.createElement('div');
    frame.className = 'ai-chart-frame';
    const canvas = document.createElement('canvas');
    frame.appendChild(canvas);
    block.appendChild(frame);

    const colors = palette();
    const type = chart.type || 'bar';
    const datasets = (chart.datasets || []).map((dataset, index) => {
      const color = colors.bars[index % colors.bars.length];
      if (type === 'line') {
        return {
          label: dataset.label || 'Series',
          data: dataset.data || [],
          borderColor: color,
          backgroundColor: colors.fill,
          fill: index === 0,
          tension: 0.3,
          pointRadius: 2,
          borderWidth: 2.25,
        };
      }
      if (type === 'doughnut') {
        return {
          label: dataset.label || 'Series',
          data: dataset.data || [],
          backgroundColor: colors.bars,
          borderWidth: 0,
        };
      }
      return {
        label: dataset.label || 'Series',
        data: dataset.data || [],
        backgroundColor: color,
        borderRadius: 0,
      };
    });

    const instance = new Chart(canvas, {
      type,
      data: {
        labels: chart.labels || [],
        datasets,
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: type === 'doughnut' || datasets.length > 1,
            labels: { color: colors.muted, boxWidth: 10, font: { family: 'Outfit' } },
          },
        },
        scales:
          type === 'doughnut'
            ? {}
            : {
                x: {
                  grid: { display: type === 'line' ? false : undefined, color: colors.grid },
                  ticks: { color: colors.muted, maxTicksLimit: 8 },
                },
                y: {
                  beginAtZero: true,
                  grid: { color: colors.grid },
                  ticks: { color: colors.muted },
                },
              },
        cutout: type === 'doughnut' ? '62%' : undefined,
      },
    });

    charts.push(instance);
    return block;
  }

  async function ask(message) {
    const text = String(message || '').trim();
    if (!text) return;

    appendMessage('user', text);
    history.push({ role: 'user', content: text });
    input.value = '';
    sendBtn.disabled = true;
    statusEl.textContent = 'Thinking…';

    const pending = appendMessage('assistant', '…');
    pending.querySelector('.ai-msg__text').classList.add('ai-typing');

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          message: text,
          from: document.getElementById('ai-from').value,
          to: document.getElementById('ai-to').value,
          history: history.slice(0, -1),
        }),
      });

      const data = await response.json();
      if (!response.ok || !data.ok) {
        throw new Error(data.message || 'Assistant request failed');
      }

      pending.remove();
      appendMessage('assistant', data.reply);
      history.push({ role: 'assistant', content: data.reply.answer || '' });
      statusEl.textContent = data.reply.configured
        ? data.reply.source === 'openai'
          ? 'Model ready'
          : 'Fallback summary'
        : 'Local mode';
    } catch (error) {
      pending.remove();
      appendMessage('assistant', error.message || 'Something went wrong while contacting the assistant.');
      statusEl.textContent = 'Error';
    } finally {
      sendBtn.disabled = false;
      input.focus();
    }
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    ask(input.value);
  });

  document.getElementById('ai-suggestions')?.addEventListener('click', (event) => {
    const button = event.target.closest('[data-prompt]');
    if (!button) return;
    ask(button.dataset.prompt);
  });

  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      form.requestSubmit();
    }
  });
})();
