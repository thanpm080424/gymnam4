<?php
/**
 * Chatbot Component - Monkey Gym AI
 * Nâng cấp: action routing + quick reply suggestions
 * Fix: Badge position, spam protection, suggestions safety
 */
require_once __DIR__ . '/../../database/config.php';
$chatbotDb = Database::getConnection();

// Lấy 3 mã giảm giá mới nhất
$chatbotPromos = $chatbotDb->query(
    "SELECT * FROM MA_GIAM_GIA WHERE so_luong_con > 0 AND ngay_het_han > NOW() ORDER BY created_at DESC LIMIT 3"
)->fetchAll();

$isUserLoggedIn = !empty($_SESSION['user_id']);
$chatUserName   = $_SESSION['ho_ten'] ?? $_SESSION['ten_dang_nhap'] ?? null;

$greeting = $chatUserName ? "Xin chào **{$chatUserName}**! Mình là Monkey 🐒 — trợ lý của Monkey Gym." : "Chào bạn! Mình là Monkey 🐒 — trợ lý của Monkey Gym.";

if (!empty($chatbotPromos)) {
    $greeting .= "\n\n🎁 **Ưu đãi đang có:**";
    foreach ($chatbotPromos as $p) {
        $discount = $p['phan_tram_giam'] > 0 ? $p['phan_tram_giam'] . '%' : number_format($p['so_tien_giam']) . 'đ';
        $greeting .= "\n• Mã **{$p['code']}**: Giảm {$discount}";
    }
}
$greeting .= "\n\nMình có thể giúp gì cho bạn?";
?>
<style>
.chatbot-btn {
    position: fixed; bottom: 30px; right: 30px;
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    border-radius: 50%; color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; cursor: move;
    box-shadow: 0 4px 20px rgba(59,130,246,0.45);
    z-index: 9999; transition: transform 0.3s;
    user-select: none;
    touch-action: none;
}
.chatbot-btn.floating {
    animation: floatBot 3s ease-in-out infinite;
}
.chatbot-btn:hover { transform: scale(1.1); }
.chatbot-badge {
    position: absolute; top: -4px; right: -4px;
    background: #ef4444; color: white;
    border-radius: 50%; width: 20px; height: 20px;
    font-size: 11px; display: none;
    align-items: center; justify-content: center; font-weight: 600;
    pointer-events: none;
}
@keyframes floatBot {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-8px); }
}
.chatbot-window {
    position: fixed; bottom: 100px; right: 30px;
    width: 360px; height: 520px;
    background: #fff; border-radius: 18px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.18);
    z-index: 9998; display: none; flex-direction: column;
    overflow: hidden; border: 1px solid #e5e7eb;
    font-family: -apple-system, BlinkMacSystemFont, sans-serif;
}
.chatbot-header {
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    color: white; padding: 14px 16px;
    display: flex; justify-content: space-between; align-items: center;
    flex-shrink: 0;
}
.chatbot-header h3 { margin: 0; font-size: 15px; display: flex; align-items: center; gap: 8px; font-weight: 600; }
.chatbot-status { font-size: 11px; opacity: 0.85; display: flex; align-items: center; gap: 4px; }
.chatbot-status::before { content:''; display:inline-block; width:7px; height:7px; background:#4ade80; border-radius:50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.chatbot-close { cursor: pointer; font-size: 20px; line-height: 1; padding: 2px; }
.chatbot-body {
    flex: 1; padding: 14px; overflow-y: auto;
    background: #f9fafb; display: flex; flex-direction: column; gap: 10px;
    scroll-behavior: smooth;
}
.chat-msg {
    max-width: 88%; padding: 10px 14px;
    border-radius: 16px; font-size: 14px; line-height: 1.5;
    white-space: pre-wrap; word-break: break-word;
}
.chat-msg.bot {
    background: white; color: #1f2937; align-self: flex-start;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    border: 1px solid #f0f0f0;
}
.chat-msg.user {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white; align-self: flex-end;
    border-bottom-right-radius: 4px;
}
/* Action button bên trong message */
.chat-action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 10px; padding: 8px 14px;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    color: white; border-radius: 20px; font-size: 13px;
    cursor: pointer; text-decoration: none;
    border: none; font-family: inherit; font-weight: 500;
    transition: opacity 0.2s;
}
.chat-action-btn:hover { opacity: 0.88; color: white; text-decoration: none; }
/* Quick reply suggestions */
.suggestions-row {
    display: flex; flex-wrap: wrap; gap: 6px;
    align-self: flex-start; max-width: 100%;
}
.suggest-btn {
    padding: 6px 12px; border-radius: 16px; font-size: 12px;
    border: 1.5px solid #3b82f6; color: #2563eb;
    background: white; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
    font-family: inherit;
}
.suggest-btn:hover { background: #eff6ff; }
.typing-indicator {
    display: none; padding: 10px 14px;
    background: white; border-radius: 16px; border-bottom-left-radius: 4px;
    align-self: flex-start; box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    color: #9ca3af; font-size: 13px; border: 1px solid #f0f0f0;
}
.typing-dots { display: inline-flex; gap: 3px; align-items: center; }
.typing-dots span {
    width: 6px; height: 6px; background: #9ca3af; border-radius: 50%;
    animation: typingDot 1.2s infinite;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingDot { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-4px)} }
.chatbot-footer {
    padding: 10px 12px; background: white;
    border-top: 1px solid #f0f0f0; display: flex; gap: 8px; flex-shrink: 0;
}
.chatbot-input {
    flex: 1; padding: 10px 14px;
    border: 1.5px solid #e5e7eb; border-radius: 22px;
    outline: none; font-size: 14px; font-family: inherit;
    transition: border-color 0.2s; color: #1f2937;
    background: #f9fafb;
}
.chatbot-input:focus { border-color: #3b82f6; background: white; }
.chatbot-send {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white; border: none; width: 42px; height: 42px;
    border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: opacity 0.2s; flex-shrink: 0;
}
.chatbot-send:hover { opacity: 0.88; }
.chatbot-send:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<div class="chatbot-btn floating" id="chatbotToggleBtn" style="position:fixed">
    🤖
    <div class="chatbot-badge" id="chatbotBadge">1</div>
</div>

<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <div>
            <h3>🤖 Monkey Gym AI</h3>
            <div class="chatbot-status">Đang hoạt động</div>
        </div>
        <span class="chatbot-close" onclick="toggleChatbot()">✕</span>
    </div>
    <div class="chatbot-body" id="chatbotBody">
        <div class="chat-msg bot" id="greetingMsg"></div>
        <div class="suggestions-row" id="defaultSuggestions">
            <button class="suggest-btn" onclick="quickSend(this)">Xem gói tập</button>
            <button class="suggest-btn" onclick="quickSend(this)">Đặt lịch PT</button>
            <button class="suggest-btn" onclick="quickSend(this)">Mã giảm giá</button>
            <?php if ($isUserLoggedIn): ?>
            <button class="suggest-btn" onclick="quickSend(this)">Điểm của tôi</button>
            <button class="suggest-btn" onclick="quickSend(this)">Lịch PT của tôi</button>
            <?php endif; ?>
        </div>
        <div class="typing-indicator" id="typingIndicator">
            <div class="typing-dots"><span></span><span></span><span></span></div>
        </div>
    </div>
    <div class="chatbot-footer">
        <input type="text" id="chatInput" class="chatbot-input"
               placeholder="Nhập câu hỏi..."
               onkeypress="if(event.key==='Enter')sendChatMessage()">
        <button class="chatbot-send" id="chatbotSendBtn" onclick="sendChatMessage()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>
</div>

<script>
// ── Action routing map ──────────────────────────────────────────────────────
const ACTION_MAP = {
    open_store:     '<?= SITE_URL ?>/member/store',
    open_booking:   '<?= SITE_URL ?>/member/booking',
    open_journal:   '<?= SITE_URL ?>/member/journal',
    open_locker:    '<?= SITE_URL ?>/member/locker',
    open_dashboard: '<?= SITE_URL ?>/member/dashboard',
    open_login:     '<?= SITE_URL ?>/login',
};
const ACTION_LABEL = {
    open_store:     '🛒 Đến Cửa Hàng',
    open_booking:   '📅 Đặt Lịch PT ngay',
    open_journal:   '📓 Mở Nhật Ký',
    open_locker:    '🔒 Xem Tủ Đồ',
    open_dashboard: '👤 Xem Dashboard',
    open_login:     '🔑 Đăng Nhập',
};

// ── Greeting message với markdown cơ bản ──────────────────────────────────
(function(){
    const greet = <?= json_encode($greeting) ?>;
    document.getElementById('greetingMsg').innerHTML = renderMarkdown(greet);
    // Badge thông báo
    setTimeout(() => { document.getElementById('chatbotBadge').style.display = 'flex'; }, 3000);
})();

const chatHistory = [];

function toggleChatbot() {
    const win = document.getElementById('chatbotWindow');
    const isOpen = win.style.display === 'flex';
    win.style.display = isOpen ? 'none' : 'flex';
    if (!isOpen) {
        document.getElementById('chatbotBadge').style.display = 'none';
        document.getElementById('chatInput').focus();
        
        // Cập nhật vị trí cửa sổ chat theo nút bot
        const btn = document.getElementById('chatbotToggleBtn');
        const rect = btn.getBoundingClientRect();
        win.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
        win.style.right = (window.innerWidth - rect.right) + 'px';
    }
}

// ── Draggable logic ────────────────────────────────────────────────────────
(function() {
    const btn = document.getElementById('chatbotToggleBtn');
    let isDragging = false;
    let startX, startY, initialX, initialY;
    let hasMoved = false;

    btn.addEventListener('mousedown', startDrag);
    btn.addEventListener('touchstart', startDrag, {passive: false});

    function startDrag(e) {
        isDragging = true;
        btn.classList.remove('floating');
        btn.style.transition = 'none';
        
        const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
        
        startX = clientX;
        startY = clientY;
        
        const rect = btn.getBoundingClientRect();
        initialX = rect.left;
        initialY = rect.top;
        hasMoved = false;

        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag, {passive: false});
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
    }

    function drag(e) {
        if (!isDragging) return;
        if (e.type === 'touchmove') e.preventDefault();

        const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
        const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

        const dx = clientX - startX;
        const dy = clientY - startY;

        if (Math.abs(dx) > 5 || Math.abs(dy) > 5) hasMoved = true;

        let newX = initialX + dx;
        let newY = initialY + dy;

        // Giới hạn trong màn hình
        newX = Math.max(10, Math.min(window.innerWidth - 70, newX));
        newY = Math.max(10, Math.min(window.innerHeight - 70, newY));

        btn.style.left = newX + 'px';
        btn.style.top = newY + 'px';
        btn.style.bottom = 'auto';
        btn.style.right = 'auto';
    }

    function stopDrag() {
        if (!isDragging) return;
        isDragging = false;
        btn.classList.add('floating');
        btn.style.transition = 'transform 0.3s, left 0.2s, top 0.2s';
        
        document.removeEventListener('mousemove', drag);
        document.removeEventListener('touchmove', drag);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchend', stopDrag);

        // Nếu không di chuyển đáng kể thì coi như là click
        if (!hasMoved) {
            toggleChatbot();
        }
    }
})();

function quickSend(btn) {
    document.getElementById('chatInput').value = btn.textContent;
    sendChatMessage();
}

function renderMarkdown(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>')
        .replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" style="color:var(--accent)">$1</a>');
}

function appendMessage(role, html, action, suggestions) {
    const body    = document.getElementById('chatbotBody');
    const typing  = document.getElementById('typingIndicator');

    const div = document.createElement('div');
    div.className = 'chat-msg ' + role;
    div.innerHTML = html;

    // Action button
    if (action && ACTION_MAP[action]) {
        const btn = document.createElement('a');
        btn.className = 'chat-action-btn';
        btn.href = ACTION_MAP[action];
        btn.innerHTML = ACTION_LABEL[action] || 'Xem thêm';
        div.appendChild(document.createElement('br'));
        div.appendChild(btn);
    }

    body.insertBefore(div, typing);

    // Suggestion chips
    if (Array.isArray(suggestions) && suggestions.length > 0) {
        const row = document.createElement('div');
        row.className = 'suggestions-row';
        suggestions.forEach(s => {
            const btn = document.createElement('button');
            btn.className = 'suggest-btn';
            btn.textContent = s;
            btn.onclick = () => quickSend(btn);
            row.appendChild(btn);
        });
        body.insertBefore(row, typing);
    }

    body.scrollTop = body.scrollHeight;
}

async function sendChatMessage() {
    const input   = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatbotSendBtn');
    const text    = input.value.trim();
    if (!text || input.disabled) return;

    const typing = document.getElementById('typingIndicator');

    // Ẩn default suggestions sau lần chat đầu tiên
    const defaultSug = document.getElementById('defaultSuggestions');
    if (defaultSug) defaultSug.style.display = 'none';

    appendMessage('user', renderMarkdown(text), null, null);
    input.value = '';
    
    // Disable input để chống spam
    input.disabled = true;
    sendBtn.disabled = true;

    chatHistory.push({ role: 'user', text });
    typing.style.display = 'block';
    document.getElementById('chatbotBody').scrollTop = 99999;

    try {
        const res = await fetch('<?= SITE_URL ?>/api/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                message: text,
                history: chatHistory.slice(-8),
            }),
        });
        const data = await res.json();
        typing.style.display = 'none';

        const reply       = data.reply       || 'Lỗi phản hồi.';
        const action      = data.action      || null;
        const suggestions = Array.isArray(data.suggestions) ? data.suggestions : [];

        chatHistory.push({ role: 'model', text: reply });
        appendMessage('bot', renderMarkdown(reply), action, suggestions);

    } catch (e) {
        typing.style.display = 'none';
        appendMessage('bot', '<span style="color:#ef4444">Lỗi kết nối. Vui lòng thử lại.</span>', null, null);
    } finally {
        input.disabled = false;
        sendBtn.disabled = false;
        input.focus();
    }
}
</script>