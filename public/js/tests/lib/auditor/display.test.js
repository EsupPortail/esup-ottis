/**
 * Tests unitaires pour js/lib/auditor/display.js
 * Tests des fonctions d'affichage
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { EcrireNB, EcrireNBSub, Ecrire, hideBanner, showBanner } from '../../../lib/auditor/display.js';

describe('lib/auditor/display.js - Display Functions', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="notebook"></div>
      <div id="output"></div>
      <div id="titre"></div>
      <div id="fixed-title"></div>
      <div id="VOBanner"></div>
      <div id="test-banner" style="transform: scale(1);"></div>
    `;
    vi.restoreAllMocks();

    // Mock DOMPurify
    window.DOMPurify = {
      sanitize: vi.fn((text) => text),
    };
  });

  describe('EcrireNB()', () => {
    it('should not throw error when called', () => {
      expect(() => EcrireNB('Test message')).not.toThrow();
    });
  });

  describe('EcrireNBSub()', () => {
    it('should not throw error when called', () => {
      expect(() => EcrireNBSub('Test message')).not.toThrow();
    });
  });

  describe('Ecrire()', () => {
    it('should not throw error when called', () => {
      expect(() => Ecrire('Test message')).not.toThrow();
    });
  });

  describe('hideBanner()', () => {
    it('should hide banner by setting transform to scale(0)', () => {
      hideBanner('test-banner');
      const element = document.getElementById('test-banner');
      expect(element?.style.transform).toBe('scale(0)');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => hideBanner('non-existent')).not.toThrow();
    });
  });

  describe('showBanner()', () => {
    it('should show banner by setting transform to scale(1)', () => {
      const element = document.getElementById('test-banner');
      if (element) element.style.transform = 'scale(0)';

      showBanner('test-banner');

      expect(element?.style.transform).toBe('scale(1)');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => showBanner('non-existent')).not.toThrow();
    });
  });
});
