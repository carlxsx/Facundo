<!-- Chat Widget -->
<div class="chat-widget" id="chatWidget">
    <!-- Chat Bubble Button -->
    <div class="chat-bubble" onclick="toggleChat()">
        <i class="fas fa-comment-dots"></i>
    </div>
    
    <!-- Chat Box -->
    <div class="chat-box" id="chatBox">
        <!-- Chat Header -->
        <div class="chat-header">
            <div>
                <h6 class="mb-0">Chat with Admin</h6>
                <small>We're here to help!</small>
            </div>
            <button onclick="toggleChat()" class="btn btn-sm text-white border-0">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <?php if (isLoggedIn()): ?>
                <div class="chat-message admin">
                    <div class="message-bubble">
                        Hi <?php echo $_SESSION['full_name']; ?>! How can we help you today?
                    </div>
                    <div class="message-time">Just now</div>
                </div>
            <?php else: ?>
                <div class="chat-message admin">
                    <div class="message-bubble">
                        Welcome to <?php echo SITE_NAME; ?>! Please login to start chatting.
                    </div>
                    <div class="message-time">Just now</div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input">
            <?php if (isLoggedIn()): ?>
                <input type="text" id="chatInput" placeholder="Type your message..." onkeypress="handleChatKeyPress(event)">
                <button onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm w-100">Login to Chat</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleChat() {
    const chatBox = document.getElementById('chatBox');
    chatBox.classList.toggle('active');
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (message === '') return;
    
    // Add user message to chat
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message user';
    messageDiv.innerHTML = `
        <div class="message-bubble">${escapeHtml(message)}</div>
        <div class="message-time">Just now</div>
    `;
    messagesDiv.appendChild(messageDiv);
    
    // Clear input
    input.value = '';
    
    // Scroll to bottom
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
    
    // Send to server (you'll implement this later)
    // For now, just show auto-reply
    setTimeout(() => {
        const replyDiv = document.createElement('div');
        replyDiv.className = 'chat-message admin';
        replyDiv.innerHTML = `
            <div class="message-bubble">Thank you for your message! An admin will respond shortly.</div>
            <div class="message-time">Just now</div>
        `;
        messagesDiv.appendChild(replyDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }, 1000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Check for new messages (implement later with AJAX)
<?php if (isLoggedIn()): ?>
setInterval(function() {
    // TODO: Check for new messages via AJAX
    // checkNewMessages();
}, 5000);
<?php endif; ?>
</script>