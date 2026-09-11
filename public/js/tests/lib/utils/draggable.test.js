/**
 * Tests unitaires pour js/lib/utils/draggable.js
 * Tests des utilitaires de glisser-déposer
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { makeDraggable } from '../../../lib/utils/draggable.js';

describe('lib/utils/draggable.js - Draggable Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="draggable-element" style="position: absolute; left: 100px; top: 100px;">Drag me</div>
      <div id="non-existent"></div>
    `;
    vi.restoreAllMocks();
  });

  describe('makeDraggable()', () => {
    it('should not throw error for valid element ID', () => {
      expect(() => makeDraggable('draggable-element')).not.toThrow();
    });

    it('should not throw error for element reference', () => {
      const element = document.getElementById('draggable-element');
      expect(() => makeDraggable(element)).not.toThrow();
    });

    it('should not throw error for non-existent element', () => {
      expect(() => makeDraggable('non-existent')).not.toThrow();
    });

    it('should not throw error for null element', () => {
      expect(() => makeDraggable(null)).not.toThrow();
    });

    it('should set onmousedown handler on element', () => {
      makeDraggable('draggable-element');

      const element = document.getElementById('draggable-element');
      expect(typeof element?.onmousedown).toBe('function');
    });

    it('should set ontouchstart handler on element', () => {
      makeDraggable('draggable-element');

      const element = document.getElementById('draggable-element');
      expect(typeof element?.ontouchstart).toBe('function');
    });
  });
});
