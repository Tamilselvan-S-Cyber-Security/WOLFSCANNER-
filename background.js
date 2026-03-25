const SENSOR_URL = 'http://localhost/cyberwolf/sensor/';

function formatEventTime(date) {
  const pad = (n) => String(n).padStart(2, '0');
  const ms = String(date.getUTCMilliseconds()).padStart(3, '0');
  return `${date.getUTCFullYear()}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())} ${pad(date.getUTCHours())}:${pad(date.getUTCMinutes())}:${pad(date.getUTCSeconds())}.${ms}`;
}

function buildFormData(payload) {
  const params = new URLSearchParams();
  const optional = (key, val) => {
    if (val != null && val !== '') params.append(key, String(val));
  };
  params.append('userName', payload.userName || '');
  params.append('ipAddress', payload.ipAddress || '');
  params.append('url', payload.url || '');
  params.append('userAgent', payload.userAgent || '');
  params.append('eventTime', payload.eventTime || formatEventTime(new Date()));
  optional('emailAddress', payload.emailAddress);
  optional('firstName', payload.firstName);
  optional('lastName', payload.lastName);
  optional('fullName', payload.fullName);
  optional('pageTitle', payload.pageTitle);
  optional('phoneNumber', payload.phoneNumber);
  optional('httpReferer', payload.httpReferer);
  optional('httpCode', payload.httpCode);
  optional('browserLanguage', payload.browserLanguage);
  optional('eventType', payload.eventType || 'page_view');
  optional('httpMethod', payload.httpMethod);
  optional('userCreated', payload.userCreated);
  if (payload.payload && payload.payload.length) {
    params.append('payload', JSON.stringify(payload.payload));
  }
  if (payload.fieldHistory && payload.fieldHistory.length) {
    params.append('fieldHistory', JSON.stringify(payload.fieldHistory));
  }
  return params.toString();
}

chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
  if (message.type !== 'LOG_ACTIVITY') {
    sendResponse({ ok: false, error: 'Unknown message type' });
    return true;
  }

  chrome.storage.sync.get(['apiKey', 'userName'], (stored) => {
    const apiKey = stored.apiKey || '';
    if (!apiKey) {
      sendResponse({ ok: false, error: 'API key not set. Open the extension popup to set it.' });
      return;
    }

    const data = {
      userName: stored.userName || 'anonymous',
      ipAddress: '',
      url: message.url || '',
      userAgent: message.userAgent || '',
      eventTime: message.eventTime || formatEventTime(new Date()),
      pageTitle: message.pageTitle || '',
      httpReferer: message.referrer || '',
      browserLanguage: message.language || '',
      eventType: message.eventType || 'page_view',
      fieldHistory: message.eventType === 'field_edit' ? message.fieldHistory : undefined,
      payload: message.eventType === 'page_search' ? [{ formAction: message.formAction, httpMethod: message.httpMethod }] : undefined,
    };

    const body = buildFormData(data);
    const headers = {
      'Content-Type': 'application/x-www-form-urlencoded',
      'Api-Key': apiKey,
    };

    fetch(SENSOR_URL, {
      method: 'POST',
      headers,
      body,
    })
      .then((res) => {
        if (!res.ok) {
          return res.text().then((t) => {
            throw new Error(`HTTP ${res.status}: ${t}`);
          });
        }
        sendResponse({ ok: true });
      })
      .catch((err) => {
        sendResponse({ ok: false, error: err.message });
      });
  });

  return true;
});
