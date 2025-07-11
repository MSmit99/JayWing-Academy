
class ChatManager {
    constructor() {
        this.initializeElements();
        this.setupEventListeners();
        this.autoScrollMessages();
    }

    initializeElements() {
        this.newChatModal = document.getElementById('newChatModal');
        this.participantsModal = document.getElementById('participantsModal');
        this.messagesDiv = document.querySelector('.overflow-y-auto');
        
        // Add event listeners for clicking outside modals to close them
        document.addEventListener('mousedown', (e) => this.handleOutsideClick(e));
        
        // Add escape key listener for modals
        document.addEventListener('keydown', (e) => this.handleEscapeKey(e));
    }

    setupEventListeners() {
        // Set up scroll event listener for lazy loading messages if needed
        if (this.messagesDiv) {
            this.messagesDiv.addEventListener('scroll', () => this.handleMessageScroll());
        }

        // Add resize listener to adjust scroll position when window is resized
        window.addEventListener('resize', () => this.autoScrollMessages());
    }

    // Modal management
    showNewChatModal() {
        if (this.newChatModal) {
            this.newChatModal.classList.remove('hidden');
            // Focus the first input in the modal
            const firstInput = this.newChatModal.querySelector('input[type="text"]');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    hideNewChatModal() {
        if (this.newChatModal) {
            this.newChatModal.classList.add('hidden');
            // Clear form inputs
            const form = this.newChatModal.querySelector('form');
            if (form) form.reset();
        }
    }

    showParticipantsModal() {
        if (this.participantsModal) {
            this.participantsModal.classList.remove('hidden');
            // Focus the email input
            const emailInput = this.participantsModal.querySelector('input[type="email"]');
            if (emailInput) {
                setTimeout(() => emailInput.focus(), 100);
            }
        }
    }

    hideParticipantsModal() {
        if (this.participantsModal) {
            this.participantsModal.classList.add('hidden');
            // Clear form inputs
            const form = this.participantsModal.querySelector('form');
            if (form) form.reset();
        }
    }

    // Handle clicking outside modals
    handleOutsideClick(event) {
        if (this.newChatModal && !this.newChatModal.classList.contains('hidden')) {
            const modalContent = this.newChatModal.querySelector('.bg-white');
            if (modalContent && !modalContent.contains(event.target)) {
                this.hideNewChatModal();
            }
        }

        if (this.participantsModal && !this.participantsModal.classList.contains('hidden')) {
            const modalContent = this.participantsModal.querySelector('.bg-white');
            if (modalContent && !modalContent.contains(event.target)) {
                this.hideParticipantsModal();
            }
        }
    }

    // Handle escape key press
    handleEscapeKey(event) {
        if (event.key === 'Escape') {
            this.hideNewChatModal();
            this.hideParticipantsModal();
        }
    }

    // Messages scrolling
    autoScrollMessages() {
        if (this.messagesDiv) {
            // Only auto-scroll if we're already near the bottom
            const isNearBottom = this.messagesDiv.scrollHeight - this.messagesDiv.scrollTop - this.messagesDiv.clientHeight < 100;
            if (isNearBottom) {
                this.messagesDiv.scrollTop = this.messagesDiv.scrollHeight;
            }
        }
    }

    handleMessageScroll() {
        // Could be used for infinite scroll/loading more messages
        if (this.messagesDiv.scrollTop === 0) {
            // Could trigger loading previous messages here
            console.log('Reached top of messages');
        }
    }
}

// Initialize the chat manager
const chatManager = new ChatManager();

// Export functions to be called from HTML
window.showNewChatModal = () => chatManager.showNewChatModal();
window.hideNewChatModal = () => chatManager.hideNewChatModal();
window.showParticipantsModal = () => chatManager.showParticipantsModal();
window.hideParticipantsModal = () => chatManager.hideParticipantsModal();

function showParticipantsModal() {
    document.getElementById('participantsModal').classList.remove('hidden');
}

function hideParticipantsModal() {
    document.getElementById('participantsModal').classList.add('hidden');
}

function deleteChat(chatId) {
    if (confirm("Are you sure you want to delete this chat?")) {
        fetch('../data_src/api/messages/delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ chatId: chatId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'message.php';
            } else {
                console.error("Error:", data.message);
                alert("Failed to leave or delete chat.");
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
            alert("Network error occurred.");
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.getElementById('student-message');
    const form = document.getElementById('message-input'); // Corrected to match your form's ID
    const sendButton = document.getElementById('send-button');

    function autoResizeTextarea() {
        const computedStyle = getComputedStyle(textarea);
        const maxHeight = parseFloat(computedStyle.maxHeight);

        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';

        if (textarea.scrollHeight >= maxHeight) {
            textarea.classList.add('overflow-y-auto');
            textarea.style.height = maxHeight + 'px';
        } else {
            textarea.classList.remove('overflow-y-auto');
        }
    }

    textarea.addEventListener('input', autoResizeTextarea);
    autoResizeTextarea(); // Resize on load

    // Enter to submit, Shift+Enter for newline
    textarea.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault(); // Prevents newline
            if (textarea.value.trim() !== '') {
                form.requestSubmit(); // Trigger form submission
            }
        }
    });
});


// --------------------- Scrollbar JavaScript ------------------------


window.onload = scrollToBottom();

function scrollToBottom() {
    const scrollable = document.getElementById('conversation');
    if (scrollable) {
        scrollable.scrollTop = scrollable.scrollHeight;
    }
}

/**
 * Adjusts padding on the conversation element based on its scrollability.
 *
 * - Checks if the `#conversation` element exists and whether it overflows vertically.
 * - If the element has a vertical scrollbar (`scrollHeight > clientHeight`), adds the
 *   `p-chat-scroll` class and removes `p-chat-noshow` to provide extra padding.
 * - If not scrollable, does the reverse to reduce unnecessary spacing.
 *
 * Useful for ensuring consistent padding behavior whether or not the chat has a scrollbar.
 */
function adjustConversationPadding() {
    const conversation = document.getElementById('conversation');
    if (!conversation) {
        console.warn("Conversation element not found. No chat selected.");
        return;
    }

    const hasScrollbar = conversation.scrollHeight > conversation.clientHeight;
    if (hasScrollbar) {
        conversation.classList.add('p-chat-scroll');
        conversation.classList.remove('p-chat-noshow');
    } else {
        conversation.classList.remove('p-chat-scroll');
        conversation.classList.add('p-chat-noshow');
    }
}

window.addEventListener('load', adjustConversationPadding);
window.addEventListener('resize', adjustConversationPadding);

/**
 * Adjusts padding on the sidebar-chats element based on its scrollability.
 *
 * - Checks if the `#sidebar-chats` element exists and whether it overflows vertically.
 * - If the element has a vertical scrollbar (`scrollHeight > clientHeight`), adds the
 *   `p-sidebar-scroll` class and removes `p-sidebar-noshow` to provide extra padding.
 * - If not scrollable, does the reverse to reduce unnecessary spacing.
 *
 * Useful for ensuring consistent padding behavior whether or not the sidebar has a scrollbar.
 */
function adjustSidebarChatsPadding() {
    const sidebarChasts = document.getElementById('sidebar-chats');
    
    // Check if the element is scrollable (scrollHeight > clientHeight)
    const hasScrollbar = sidebarChasts.scrollHeight > sidebarChasts.clientHeight;

    // Toggle a class or set style directly
    if (hasScrollbar) {
        sidebarChasts.classList.add('p-sidebar-scroll');
        sidebarChasts.classList.remove('p-sidebar-noshow');
    } else {
        sidebarChasts.classList.remove('p-sidebar-scroll');
        sidebarChasts.classList.add('p-sidebar-noshow');
    }
}

window.addEventListener('load', adjustSidebarChatsPadding);
window.addEventListener('resize', adjustSidebarChatsPadding);

/**
 * Saves the current vertical scroll position of the sidebar with ID "sidebar-chats"
 * into sessionStorage before the page unloads or reloads.
 * 
 * This allows restoring the sidebar scroll position on subsequent page loads.
 */
window.addEventListener("beforeunload", function () {
    const sidebar = document.getElementById("sidebar-chats");
    if (sidebar) {
        sessionStorage.setItem("sidebarScrollTop", sidebar.scrollTop);
    }
});

/**
 * Restores the vertical scroll position of the sidebar with ID "sidebar-chats" from sessionStorage.
 * 
 * After restoring scroll, if a `chatId` query parameter exists in the URL, scrolls the corresponding
 * chat element inside the sidebar into view for user convenience.
 * 
 * Removes the stored scroll position from sessionStorage after applying it.
 */
window.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar-chats");
    const saved = sessionStorage.getItem("sidebarScrollTop");

    if (sidebar && saved !== null) {
        sidebar.scrollTop = parseInt(saved, 10);
        sessionStorage.removeItem("sidebarScrollTop");

        // Scroll the selected chat into view AFTER restoring scroll
        const params = new URLSearchParams(window.location.search);
        const chatId = params.get("chatId");
        if (chatId) {
            const chatElement = sidebar.querySelector(`[data-chat-id="${chatId}"]`);
            if (chatElement) {
                // Slight delay ensures scrollTop applies first
                setTimeout(() => {
                    chatElement.scrollIntoView({
                        behavior: "auto",
                        block: "nearest" // try "center" or "start" if needed
                    });
                }, 10);
            }
        }
    }
});
