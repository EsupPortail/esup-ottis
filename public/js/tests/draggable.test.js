/**
 * Tests unitaires pour js/utils/draggable.js
 * Tests des fonctions de drag-and-drop
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { makeDraggable, makeAllDraggable } from '../lib/utils/draggable.js';

describe('Draggable Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="draggable-div" style="position: absolute; left: 0px; top: 0px; width: 100px; height: 100px;">
        Draggable
      </div>
      <div id="non-draggable">Not draggable</div>
    `;
    // Clear event listeners
    vi.clearAllMocks();
  });

  describe('makeDraggable()', () => {
    it('should make element draggable by ID', () => {
      makeDraggable('draggable-div');
      const element = document.getElementById('draggable-div');

      expect(element.onmousedown).toBeDefined();
      expect(typeof element.onmousedown).toBe('function');
    });

    it('should make element draggable by element object', () => {
      const element = document.getElementById('draggable-div');
      makeDraggable(element);

      expect(element.onmousedown).toBeDefined();
      expect(element.ontouchstart).toBeDefined();
    });

    it('should not throw for non-existent element', () => {
      expect(() => makeDraggable('non-existent')).not.toThrow();
    });

    it('should return undefined for non-existent element', () => {
      const result = makeDraggable('non-existent');
      expect(result).toBeUndefined();
    });
  });

  describe('makeAllDraggable()', () => {
    it('should make multiple elements draggable', () => {
      document.body.innerHTML += `
        <div class="draggable-item">Item 1</div>
        <div class="draggable-item">Item 2</div>
        <div class="draggable-item">Item 3</div>
      `;

      makeAllDraggable('.draggable-item');

      const items = document.querySelectorAll('.draggable-item');
      items.forEach((item) => {
        expect(item.onmousedown).toBeDefined();
        expect(item.ontouchstart).toBeDefined();
      });
    });

    it('should not throw for non-matching selector', () => {
      expect(() => makeAllDraggable('.non-existent-class')).not.toThrow();
    });
  });
});
