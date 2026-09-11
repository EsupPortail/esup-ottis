/**
 * Tests unitaires pour js/lib/index.js
 * Tests que le module principale exporte correctement toutes les fonctions
 */

import { describe, it, expect } from 'vitest';

describe('lib/index.js - Main Module Exports', () => {
  it('should export all functions from dom.js', async () => {
    const { _ } = await import('../../lib/index.js');
    expect(typeof _).toBe('function');
  });

  it('should export all functions from colors.js', async () => {
    const { getUserColor, rgb } = await import('../../lib/index.js');
    expect(typeof getUserColor).toBe('function');
    expect(typeof rgb).toBe('function');
  });

  it('should export all functions from xhr.js', async () => {
    const { handleXhrError, fetchWithErrorHandling } = await import('../../lib/index.js');
    expect(typeof handleXhrError).toBe('function');
    expect(typeof fetchWithErrorHandling).toBe('function');
  });

  it('should export all functions from download.js', async () => {
    const { DownloadSpeechLesson } = await import('../../lib/index.js');
    expect(typeof DownloadSpeechLesson).toBe('function');
  });

  it('should export all functions from browser.js', async () => {
    const { showError, whichBrowser } = await import('../../lib/index.js');
    expect(typeof showError).toBe('function');
    expect(typeof whichBrowser).toBe('function');
  });
});
