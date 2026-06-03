{{-- resources/views/Temp/Investor/partials/openclaw.blade.php --}}

<style>
    #openclaw-widget {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 1060;
        pointer-events: none;
    }

    #openclaw-chat-btn {
        width: 62px;
        height: 62px;
        border: none;
        border-radius: 999px;
        background: #2563eb;
        color: white;
        font-size: 24px;
        cursor: pointer;
        pointer-events: auto;
        box-shadow: 0 16px 40px rgba(37, 99, 235, .35);
    }

    #openclaw-chat-box {
        position: absolute;
        right: 0;
        bottom: 74px;
        width: 380px;
        max-width: calc(100vw - 24px);
        height: 540px;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, .1);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);

        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(10px);
        transition: .18s ease;
    }

    #openclaw-widget.open #openclaw-chat-box {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: translateY(0);
    }

    .openclaw-header {
        height: 64px;
        background: #020617;
        color: white;
        padding: 0 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .openclaw-header-title {
        font-size: 14px;
        font-weight: 900;
    }

    .openclaw-header-sub {
        font-size: 11px;
        color: #cbd5e1;
        margin-top: 2px;
    }

    .openclaw-close {
        border: none;
        background: none;
        color: white;
        font-size: 22px;
        cursor: pointer;
    }

    .openclaw-body {
        height: 410px;
        overflow-y: auto;
        padding: 14px;
        background: #f8fafc;
    }

    .openclaw-msg {
        margin-bottom: 12px;
        padding: 11px 13px;
        border-radius: 14px;
        font-size: 13px;
        line-height: 1.5;
        word-break: break-word;
    }

    .openclaw-user {
        background: #2563eb;
        color: white;
        margin-left: 40px;
    }

    .openclaw-ai {
        background: white;
        color: #0f172a;
        margin-right: 40px;
        border: 1px solid rgba(15, 23, 42, .08);
    }

    .openclaw-footer {
        height: 66px;
        border-top: 1px solid rgba(15, 23, 42, .08);
        padding: 10px;
        display: flex;
        gap: 8px;
        background: white;
    }

    #openclaw-input {
        flex: 1;
        border: 1px solid rgba(15, 23, 42, .15);
        border-radius: 12px;
        padding: 10px 12px;
        outline: none;
        font-size: 13px;
    }

    #openclaw-send {
        border: none;
        border-radius: 12px;
        background: #2563eb;
        color: white;
        font-weight: 800;
        padding: 0 16px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        #openclaw-widget {
            right: 14px;
            bottom: 14px;
        }

        #openclaw-chat-box {
            position: fixed;
            right: 12px;
            left: 12px;
            bottom: 88px;
            width: auto;
            max-width: none;
            height: 78vh;
        }
    }
</style>

<div id="openclaw-widget">
    <div id="openclaw-chat-box">
        <div class="openclaw-header">
            <div>
                <div class="openclaw-header-title">
                    OpenClaw AI
                </div>

                <div class="openclaw-header-sub">
                    Tanya data sales Geprekin
                </div>
            </div>

            <button type="button" class="openclaw-close" id="openclaw-close">
                &times;
            </button>
        </div>

        <div class="openclaw-body" id="openclaw-messages">
            <div class="openclaw-msg openclaw-ai">
                Halo 👋<br>
                Saya siap bantu cek data sales.
            </div>
        </div>

        <div class="openclaw-footer">
            <input
                type="text"
                id="openclaw-input"
                placeholder="Contoh: sales hari ini">

            <button type="button" id="openclaw-send">
                Kirim
            </button>
        </div>
    </div>

    <button id="openclaw-chat-btn" type="button">
        <i class="bi bi-robot"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('openclaw-widget');
    const btn = document.getElementById('openclaw-chat-btn');
    const box = document.getElementById('openclaw-chat-box');
    const closeBtn = document.getElementById('openclaw-close');
    const input = document.getElementById('openclaw-input');
    const sendBtn = document.getElementById('openclaw-send');
    const messages = document.getElementById('openclaw-messages');

    if (!widget || !btn || !box || !closeBtn || !input || !sendBtn || !messages) {
        console.warn('OpenClaw widget element missing');
        return;
    }

    function openChat() {
        widget.classList.add('open');

        setTimeout(function () {
            input.focus();
        }, 100);
    }

    function closeChat() {
        widget.classList.remove('open');
    }

    function toggleChat(e) {
        e.preventDefault();
        e.stopPropagation();

        if (widget.classList.contains('open')) {
            closeChat();
        } else {
            openChat();
        }
    }

    btn.addEventListener('click', toggleChat);

    closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeChat();
    });

    box.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    async function sendMessage() {
        const text = input.value.trim();

        if (!text) {
            return;
        }

        const userMsg = document.createElement('div');
        userMsg.className = 'openclaw-msg openclaw-user';
        userMsg.textContent = text;
        messages.appendChild(userMsg);

        input.value = '';

        const loading = document.createElement('div');
        loading.className = 'openclaw-msg openclaw-ai';
        loading.textContent = 'Sedang cek data...';
        messages.appendChild(loading);

        messages.scrollTop = messages.scrollHeight;

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            const response = await fetch('/openclaw/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify({
                    message: text
                })
            });

            if (!response.ok) {
                throw new Error('HTTP Error ' + response.status);
            }

            const data = await response.json();

            loading.remove();

            const aiMsg = document.createElement('div');
            aiMsg.className = 'openclaw-msg openclaw-ai';
            aiMsg.innerHTML = data.reply || 'AI tidak memberi jawaban';
            messages.appendChild(aiMsg);

        } catch (err) {
            console.error(err);

            loading.remove();

            const errorMsg = document.createElement('div');
            errorMsg.className = 'openclaw-msg openclaw-ai';
            errorMsg.textContent = 'Server AI gagal dihubungi';
            messages.appendChild(errorMsg);
        }

        messages.scrollTop = messages.scrollHeight;
    }

    sendBtn.addEventListener('click', sendMessage);

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });
});
</script>