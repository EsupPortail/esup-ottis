/**
 * @module lib/colors
 * Color management utilities for chat participants
 */

/**
 * Global color mapping for chat participants
 * Format: { username: [[bgR, bgG, bgB], [textR, textG, textB], type] }
 */
export const ColorsForCR = {
  '----': [[255, 0, 0], [255, 255, 255], 5],
  Intervenant: [[0, 0, 255], [255, 255, 255], 0],
};

/**
 * Convert RGB array to CSS rgb() string
 * @param {number[]} T - Array of 3 integers [0-255]
 * @returns {string} CSS rgb() string
 */
export function rgb(T) {
  return `rgb(${T[0]},${T[1]},${T[2]})`;
}

/**
 * Generate a random color for a user, avoiding intervenant's blue (0,0,255)
 * Persists the color in localStorage so the same user keeps the same color across sessions
 * @param {string} username - The username to generate color for
 * @returns {number[][]} Array containing [backgroundColor, textColor]
 */
export function getUserColor(username) {
  // Try to get from localStorage first
  const storageKey = `chatUserColor_${username}`;
  const stored = localStorage.getItem(storageKey);
  if (stored) {
    try {
      return JSON.parse(stored);
    } catch (e) {
      // If parsing fails, regenerate
    }
  }

  // Generate random RGB values in 55-255 range to avoid too dark colors
  let r; let g; let
    b;
  do {
    r = Math.floor(Math.random() * 201) + 55;
    g = Math.floor(Math.random() * 201) + 55;
    b = Math.floor(Math.random() * 201) + 55;
  } while (r === 0 && g === 0 && b === 255); // Never use intervenant's blue

  // Calculate brightness to determine text color (black or white)
  // Using relative luminance formula (simplified)
  const brightness = (r * 299 + g * 587 + b * 114) / 1000;
  const textColor = brightness > 130 ? [0, 0, 0] : [255, 255, 255];

  const colors = [[r, g, b], textColor];

  // Store in localStorage for persistence
  try {
    localStorage.setItem(storageKey, JSON.stringify(colors));
  } catch (e) {
    // localStorage might not be available (private browsing, etc.)
    console.warn('Could not save user color to localStorage:', e);
  }

  return colors;
}
