/**
 * Tests unitaires pour js/lib/auditor/speech.js
 * Tests des fonctions de synthèse vocale
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';

describe('lib/auditor/speech.js - Speech Functions', () => {
  beforeEach(() => {
    vi.restoreAllMocks();

    // Mock global dependencies
    global._ = vi.fn((id) => (id === 'titre' ? { innerHTML: '' } : null));
    global.DOMPurify = { sanitize: vi.fn((text) => text) };
    global.builtin_voice = null;
    global.builtin_discoursrate = 1;
    global.ToSay = {};
    global.currentdiscours = 0;

    // Mock speechSynthesis
    global.speechSynthesis = {
      speak: vi.fn(),
    };
    global.SpeechSynthesisUtterance = vi.fn();
  });

  it('should export Parle function', async () => {
    const { Parle } = await import('../../../lib/auditor/speech.js');
    expect(typeof Parle).toBe('function');
  });

  it('should export ParleThen function', async () => {
    const { ParleThen } = await import('../../../lib/auditor/speech.js');
    expect(typeof ParleThen).toBe('function');
  });

  it('should export CompleteSpeech function', async () => {
    const { CompleteSpeech } = await import('../../../lib/auditor/speech.js');
    expect(typeof CompleteSpeech).toBe('function');
  });

  it('Parle should not throw when called', async () => {
    const { Parle } = await import('../../../lib/auditor/speech.js');
    expect(() => Parle('test')).not.toThrow();
  });

  it('ParleThen should not throw when called', async () => {
    const { ParleThen } = await import('../../../lib/auditor/speech.js');
    expect(() => ParleThen('test', () => {})).not.toThrow();
  });

  it('CompleteSpeech should not throw when called', async () => {
    const { CompleteSpeech } = await import('../../../lib/auditor/speech.js');
    expect(() => CompleteSpeech()).not.toThrow();
  });
});
