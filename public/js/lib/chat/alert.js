/**
 * chat-alert.js
 * Chat notification alert component for OMIST
 * Provides a blinking alert indicator when new messages are received
 * Works with both auditor and conference interfaces
 */
import { _ } from '../dom/selector.js';
import { safeSetInterval } from '../utils/cleanup.js';

// Global state for chat alert
const chatAlertState = {
  hasNewMessages: false,
  alertCount: 0,
  isVisible: false,
  blinkInterval: null,
  currentUser: '',
};

/**
 * @summary Initialize the chat alert component
 * Must be called after DOM is loaded and user info is available
 *
 * @param {string} userName - Current user's name to filter out their own messages
 * @returns {void}
 */
export function initChatAlert(userName) {
  chatAlertState.currentUser = userName || '';

  // Create alert element if it doesn't exist
  createChatAlertElement();

  // Start blink animation when alert is shown
  startBlinkAnimation();
}

/**
 * @summary Create the chat alert DOM element
 * Floating element that appears when new messages arrive
 *
 * @returns {void}
 */
export function createChatAlertElement() {
  if (_('chat-alert')) {
    return; // Already exists
  }

  const alertElement = document.createElement('div');
  alertElement.id = 'chat-alert';
  alertElement.className = 'chat-alert';
  alertElement.setAttribute('role', 'alert');
  alertElement.setAttribute('aria-live', 'assertive');
  alertElement.setAttribute('aria-hidden', 'true');
  alertElement.title = 'New chat message received. Click to open chat.';

  // Icon for the alert
  const icon = document.createElement('i');
  icon.className = 'fa fa-comments chat-alert-icon';
  alertElement.appendChild(icon);

  // Badge with count
  const badge = document.createElement('span');
  badge.className = 'chat-alert-badge';
  badge.id = 'chat-alert-badge';
  badge.textContent = '0';
  alertElement.appendChild(badge);

  // Add to body
  document.body.appendChild(alertElement);

  // Add click handler - placeholder for future implementation
  alertElement.addEventListener('click', () => {
    // Click handler will be implemented later to show chat
    // For now, just reset the alert
    resetChatAlert();

    // Log for future implementation
  });
}

/**
 * @summary Show the chat alert with blinking animation
 *
 * @returns {void}
 */
export function showChatAlert() {
  let alertElement = _('chat-alert');
  if (!alertElement) {
    createChatAlertElement();
    alertElement = _('chat-alert');
  }

  if (!alertElement) return;

  chatAlertState.hasNewMessages = true;
  chatAlertState.isVisible = true;
  alertElement.setAttribute('aria-hidden', 'false');
  alertElement.classList.add('chat-alert-visible');

  // Update badge count
  updateAlertBadge();
}

/**
 * @summary Hide the chat alert
 *
 * @returns {void}
 */
export function hideChatAlert() {
  const alertElement = _('chat-alert');
  if (!alertElement) return;

  chatAlertState.isVisible = false;
  alertElement.setAttribute('aria-hidden', 'true');
  alertElement.classList.remove('chat-alert-visible');
}

/**
 * @summary Reset the chat alert (hide and clear count)
 *
 * @returns {void}
 */
export function resetChatAlert() {
  chatAlertState.hasNewMessages = false;
  chatAlertState.alertCount = 0;
  hideChatAlert();
  updateAlertBadge();
}

/**
 * @summary Increment the alert count and show alert
 *
 * @returns {void}
 */
export function incrementAlertCount() {
  chatAlertState.alertCount++;
  chatAlertState.hasNewMessages = true;
  showChatAlert();
  updateAlertBadge();
}

/**
 * @summary Update the badge count display
 *
 * @returns {void}
 */
export function updateAlertBadge() {
  const badge = _('chat-alert-badge');
  if (!badge) return;

  if (chatAlertState.alertCount > 0) {
    badge.textContent = chatAlertState.alertCount;
    badge.style.display = 'inline';
  } else {
    badge.textContent = '0';
    badge.style.display = 'none';
  }
}

/**
 * @summary Start the blinking animation for the alert
 *
 * @returns {void}
 */
export function startBlinkAnimation() {
  // Clear any existing interval
  if (chatAlertState.blinkInterval) {
    clearInterval(chatAlertState.blinkInterval);
  }

  chatAlertState.blinkInterval = safeSetInterval(() => {
    const alertElement = _('chat-alert');
    if (!alertElement || !chatAlertState.hasNewMessages) return;

    // Toggle blink class for pulsating effect
    if (alertElement.classList.contains('chat-alert-blink')) {
      alertElement.classList.remove('chat-alert-blink');
    } else {
      alertElement.classList.add('chat-alert-blink');
    }
  }, 500); // 500ms interval for blinking
}

/**
 * @summary Stop the blinking animation
 *
 * @returns {void}
 */
export function stopBlinkAnimation() {
  if (chatAlertState.blinkInterval) {
    clearInterval(chatAlertState.blinkInterval);
    chatAlertState.blinkInterval = null;

    // Remove blink class
    const alertElement = _('chat-alert');
    if (alertElement) {
      alertElement.classList.remove('chat-alert-blink');
    }
  }
}

/**
 * @summary Trigger alert for new message
 * Called from message handler when a new message arrives
 *
 * @param {string} senderName - Name of the message sender
 * @returns {void}
 */
export function triggerNewMessageAlert(senderName) {
  // Don't trigger alert for own messages
  if (senderName && senderName === chatAlertState.currentUser) {
    return;
  }

  // Increment count and show alert
  incrementAlertCount();
}

/**
 * @summary Get current alert state
 *
 * @returns {object} Current alert state
 */
export function getChatAlertState() {
  return chatAlertState;
}
