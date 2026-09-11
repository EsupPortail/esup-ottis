/**
 * Tests unitaires pour js/lib/chat/network.js
 * Tests que les fonctions principales existent
 */

import { describe, it, expect } from 'vitest';

describe('lib/chat/network.js - Chat Network Functions', () => {
  it('should export saveTextQCommon function', async () => {
    const { saveTextQCommon } = await import('../../../lib/chat/network.js');
    expect(typeof saveTextQCommon).toBe('function');
  });

  it('should export readTextQCommon function', async () => {
    const { readTextQCommon } = await import('../../../lib/chat/network.js');
    expect(typeof readTextQCommon).toBe('function');
  });

  it('should export TranslateQ function', async () => {
    const { TranslateQ } = await import('../../../lib/chat/network.js');
    expect(typeof TranslateQ).toBe('function');
  });
});
