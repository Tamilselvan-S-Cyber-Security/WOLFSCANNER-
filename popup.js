const apiKeyInput = document.getElementById('apiKey');
const userNameInput = document.getElementById('userName');
const saveBtn = document.getElementById('save');
const statusEl = document.getElementById('status');

function showStatus(message, isError) {
  statusEl.textContent = message;
  statusEl.className = 'status ' + (isError ? 'error' : 'success');
}

chrome.storage.sync.get(['apiKey', 'userName'], (stored) => {
  if (stored.apiKey) apiKeyInput.value = stored.apiKey;
  if (stored.userName) userNameInput.value = stored.userName;
});

saveBtn.addEventListener('click', () => {
  const apiKey = apiKeyInput.value.trim();
  const userName = userNameInput.value.trim();
  if (!apiKey) {
    showStatus('Please enter an API key.', true);
    return;
  }
  chrome.storage.sync.set(
    { apiKey, userName: userName || 'anonymous' },
    () => {
      showStatus('Settings saved.');
    }
  );
});
