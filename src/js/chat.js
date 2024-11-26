
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