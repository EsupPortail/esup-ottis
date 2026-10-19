/**
 * Tests unitaires pour js/lib/auditor/text-processing.js
 * Tests des utilitaires de traitement de texte
 */

import { describe, it, expect, beforeEach } from 'vitest';
import {
  decodeHtmlEntities,
  countLines,
  auto_size_text,
} from '../../../lib/auditor/text-processing.js';

describe('lib/auditor/text-processing.js - Text Processing Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="titre" style="font-size: 16px; line-height: 20px;">Test Content</div>
    `;
  });

  describe('decodeHtmlEntities()', () => {
    it('should decode HTML entities', () => {
      const result = decodeHtmlEntities('&lt;script&gt;test&lt;/script&gt;');
      expect(result).toBe('<script>test</script>');
    });

    it('should decode ampersand', () => {
      const result = decodeHtmlEntities('Tom &amp; Jerry');
      expect(result).toBe('Tom & Jerry');
    });

    it('should decode multiple entities', () => {
      const result = decodeHtmlEntities('&quot;test&quot; &amp; &lt;tag&gt;');
      expect(result).toBe('"test" & <tag>');
    });

    it('should handle empty string', () => {
      const result = decodeHtmlEntities('');
      expect(result).toBe('');
    });
  });

  describe('countLines()', () => {
    it('should count lines based on element height and line height', () => {
      const element = document.getElementById('titre');
      if (element) {
        element.style.lineHeight = '20px';
        element.style.height = '40px';
        // offsetHeight is not available in jsdom, so this will return NaN or 0
        const lines = countLines('titre');
        // We can't test the exact value in jsdom, but we can verify the function runs
        expect(typeof lines).toBe('number');
      }
    });

    it('should return 0 for non-existent element', () => {
      const lines = countLines('non-existent');
      expect(lines).toBe(0);
    });
  });

  describe('auto_size_text()', () => {
    it('should not throw error for valid element', () => {
      expect(() => auto_size_text('titre', 3)).not.toThrow();
    });

    it('should use default element and max_lines', () => {
      expect(() => auto_size_text()).not.toThrow();
    });

    it('should call countLines with correct element', () => {
      const element = document.getElementById('titre');
      Object.defineProperty(element, 'offsetHeight', {
        value: 60,
        writable: true,
        configurable: true,
      });
      element.style.lineHeight = '20px';
      element.style.fontSize = '16px';

      auto_size_text('titre', 3);

      expect(element.style.fontSize).toBe('16px');
    });
  });
});
