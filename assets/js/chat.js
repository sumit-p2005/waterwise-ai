const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');

function addBubble(content, type = 'bot', isHtml = false) {
  if (!chatMessages) return;
  const div = document.createElement('div');
  div.className = `msg ${type}`;
  if (isHtml) div.innerHTML = content;
  else div.textContent = content;
  chatMessages.appendChild(div);
  chatMessages.scrollTop = chatMessages.scrollHeight;
  return div;
}

function typingBubble() {
  return addBubble('<span class="typing"><span></span><span></span><span></span></span>', 'bot', true);
}

function structuredResponse(payload) {
  const wqi = payload.predicted_wqi != null ? `<div><b>Predicted WQI:</b> ${payload.predicted_wqi}</div>` : '';
  const cat = payload.category ? `<div><b>Category:</b> ${payload.category}</div>` : '';
  const exp = payload.explanation ? `<div class="mt-1">${payload.explanation}</div>` : '';
  const tips = (payload.tips || []).length ? `<ul class="mt-2">${payload.tips.slice(0, 3).map(t => `<li>- ${t}</li>`).join('')}</ul>` : '';
  return `${wqi}${cat}${exp}${tips}` || payload.reply || 'No response available.';
}

async function sendMessage(message) {
  addBubble(message, 'user');
  const typing = typingBubble();
  try {
    const res = await fetch('api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message })
    });
    const out = await res.json();
    typing?.remove();
    addBubble(structuredResponse(out), 'bot', true);
  } catch (err) {
    typing?.remove();
    addBubble('Assistant is temporarily unavailable.', 'bot');
    showToast('Chat request failed', 'error');
  }
}

if (chatForm) {
  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const message = (chatInput?.value || '').trim();
    if (!message) return;
    chatInput.value = '';
    sendMessage(message);
  });
}

document.querySelectorAll('[data-chat-chip]').forEach((chip) => {
  chip.addEventListener('click', () => {
    const text = chip.getAttribute('data-chat-chip') || '';
    if (!text) return;
    sendMessage(text);
  });
});

if (chatMessages) {
  addBubble('Hi, I am WaterWise Copilot. Share 9 values in order: Temperature, DO, pH, BOD, FS, Nitrate, FC, TC, Conductivity.', 'bot');
}
