(function () {
  if (window !== window.top) return;

  function formatEventTime(date) {
    const pad = (n) => String(n).padStart(2, '0');
    const ms = String(date.getUTCMilliseconds()).padStart(3, '0');
    return (
      date.getUTCFullYear() +
      '-' + pad(date.getUTCMonth() + 1) +
      '-' + pad(date.getUTCDate()) +
      ' ' + pad(date.getUTCHours()) +
      ':' + pad(date.getUTCMinutes()) +
      ':' + pad(date.getUTCSeconds()) +
      '.' + ms
    );
  }

  function sendActivity(eventType, extra) {
    chrome.runtime.sendMessage(
      {
        type: 'LOG_ACTIVITY',
        url: window.location.href,
        pageTitle: document.title,
        referrer: document.referrer || '',
        userAgent: navigator.userAgent,
        language: navigator.language || '',
        eventTime: formatEventTime(new Date()),
        eventType,
        ...extra,
      },
      (response) => {
        if (chrome.runtime.lastError) return;
        if (response && !response.ok && response.error) {
          console.warn('[Wolf SIEM]', response.error);
        }
      }
    );
  }

  function describeTarget(el) {
    if (!el || !el.tagName) return { tag: '', id: '', name: '', href: '' };
    const tag = (el.tagName || '').toLowerCase();
    return {
      tag,
      id: el.id || '',
      name: el.name || '',
      href: el.href || '',
      text: (el.textContent || '').slice(0, 200).trim(),
    };
  }

  // Page view on load
  sendActivity('page_view');

  // Clicks on links, buttons, inputs
  document.addEventListener(
    'click',
    (e) => {
      const t = e.target;
      if (!t || t === document.body) return;
      const d = describeTarget(t);
      const eventType =
        d.tag === 'a' ? 'link_click' : t.matches ? (t.matches('button, [role="button"], input[type="submit"], input[type="button"]') ? 'button_click' : 'element_click') : 'element_click';
      sendActivity(eventType, {
        targetTag: d.tag,
        targetId: d.id,
        targetName: d.name,
        targetHref: d.href,
        targetText: d.text,
      });
    },
    true
  );

  // Field edits (blur = value committed)
  const fieldValues = new WeakMap();
  document.addEventListener(
    'focusin',
    (e) => {
      const el = e.target;
      if (!el || !el.matches) return;
      if (el.matches('input:not([type="submit"]):not([type="button"]):not([type="hidden"]), textarea, select')) {
        fieldValues.set(el, el.value);
      }
    },
    true
  );
  document.addEventListener(
    'focusout',
    (e) => {
      const el = e.target;
      if (!el || !el.matches) return;
      if (el.matches('input:not([type="submit"]):not([type="button"]):not([type="hidden"]), textarea, select')) {
        const oldVal = fieldValues.get(el);
        const newVal = el.value;
        if (oldVal !== newVal) {
          const fieldId = el.id || el.name || el.placeholder || 'field';
          const parent = el.closest('form, [role="form"]');
          sendActivity('field_edit', {
            fieldHistory: [
              {
                field_name: el.name || el.id || el.placeholder || 'field',
                field_id: fieldId,
                new_value: String(newVal).slice(0, 500),
                old_value: oldVal != null ? String(oldVal).slice(0, 500) : '',
                parent_id: parent ? (parent.id || parent.name || '') : '',
              },
            ],
          });
        }
        fieldValues.delete(el);
      }
    },
    true
  );

  // Form submit
  document.addEventListener(
    'submit',
    (e) => {
      const form = e.target;
      if (!form || form.tagName !== 'FORM') return;
      const action = form.action || '';
      const method = (form.method || 'get').toUpperCase();
      const isSearch =
        action.toLowerCase().includes('search') ||
        form.getAttribute('role') === 'search' ||
        (form.querySelector && form.querySelector('input[type="search"], input[name*="search"], input[name*="q"]'));
      sendActivity(isSearch ? 'page_search' : 'form_submit', {
        formAction: action,
        httpMethod: method,
      });
    },
    true
  );

  // History API (SPA navigation)
  const origPush = history.pushState;
  const origReplace = history.replaceState;
  if (origPush) {
    history.pushState = function (...args) {
      origPush.apply(this, args);
      sendActivity('page_view', { spaUrl: args[2] || location.href });
    };
  }
  if (origReplace) {
    history.replaceState = function (...args) {
      origReplace.apply(this, args);
      sendActivity('page_view', { spaUrl: args[2] || location.href });
    };
  }
  window.addEventListener('popstate', () => {
    sendActivity('page_view', { spaUrl: location.href });
  });
})();
