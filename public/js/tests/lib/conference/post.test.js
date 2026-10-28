/**
 * Tests unitaires pour js/lib/conference/post.js
 * Tests que les fonctions existent (les fonctions dépendent de window.QRCode qui n'est pas disponible dans jsdom)
 */

import { describe, it, expect } from 'vitest';

describe('lib/conference/post.js - Conference Post Load', () => {
  it('should export initQRCode function', async () => {
    const { initQRCode } = await import('../../../lib/conference/post.js');
    expect(typeof initQRCode).toBe('function');
  });

  it('should export initDragDrop function', async () => {
    const { initDragDrop } = await import('../../../lib/conference/post.js');
    expect(typeof initDragDrop).toBe('function');
  });

  it('should export initDialogs function', async () => {
    const { initDialogs } = await import('../../../lib/conference/post.js');
    expect(typeof initDialogs).toBe('function');
  });

  it('should export initConferencePost function', async () => {
    const { initConferencePost } = await import('../../../lib/conference/post.js');
    expect(typeof initConferencePost).toBe('function');
  });
});
