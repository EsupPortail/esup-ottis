/**
 * Tests unitaires pour js/lib/dom.js
 * Tests des utilitaires DOM (version ES module)
 * Anciennement pour utils/dom.js, migré vers lib/dom.js
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import {
  get,
  query,
  queryAll,
  on,
  off,
  ready,
  css,
  attr,
  val,
  getPosition as position,
} from '../../lib/dom.js';

describe('lib/dom.js - DOM Utilities (migrated from utils/dom.js)', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-id">Test Element</div>
      <p class="test-class">Test Paragraph</p>
      <span class="test-class">Test Span</span>
      <input type="text" id="test-input" value="initial">
      <div id="test-css" style="color: red;"></div>
      <div id="test-attr" data-test="value"></div>
    `;
    vi.restoreAllMocks();
  });

  describe('get()', () => {
    it('should return element by ID', () => {
      const element = get('test-id');
      expect(element).not.toBeNull();
      expect(element?.textContent).toBe('Test Element');
    });

    it('should return null for non-existent ID', () => {
      const element = get('non-existent');
      expect(element).toBeNull();
    });
  });

  describe('query()', () => {
    it('should return first element matching selector', () => {
      const element = query('.test-class');
      expect(element).not.toBeNull();
      expect(element?.tagName).toBe('P');
    });

    it('should return null for non-matching selector', () => {
      const element = query('.non-existent');
      expect(element).toBeNull();
    });
  });

  describe('queryAll()', () => {
    it('should return NodeList of matching elements', () => {
      const elements = queryAll('.test-class');
      expect(elements.length).toBe(2);
    });

    it('should return empty NodeList for non-matching selector', () => {
      const elements = queryAll('.non-existent');
      expect(elements.length).toBe(0);
    });
  });

  describe('on()', () => {
    it('should add event listener to element by ID', () => {
      const fn = vi.fn();
      on('test-id', 'click', fn);

      const element = get('test-id');
      element?.click();

      expect(fn).toHaveBeenCalled();
    });

    it('should add event listener to element reference', () => {
      const fn = vi.fn();
      const element = get('test-id');
      on(element, 'click', fn);

      element?.click();

      expect(fn).toHaveBeenCalled();
    });

    it('should not throw error for non-existent element', () => {
      expect(() => on('non-existent', 'click', vi.fn())).not.toThrow();
    });
  });

  describe('off()', () => {
    it('should remove event listener from element', () => {
      const fn = vi.fn();
      const element = get('test-id');

      element?.addEventListener('click', fn);
      off(element, 'click', fn);

      element?.click();

      expect(fn).not.toHaveBeenCalled();
    });

    it('should not throw error for non-existent element', () => {
      expect(() => off('non-existent', 'click', vi.fn())).not.toThrow();
    });
  });

  describe('ready()', () => {
    it('should not throw error when called', () => {
      const fn = vi.fn();
      expect(() => ready(fn)).not.toThrow();
    });
  });

  describe('css()', () => {
    it('should set multiple CSS properties', () => {
      css('test-css', { color: 'blue', fontSize: '16px' });

      const element = get('test-css');
      expect(element?.style.color).toBe('blue');
      expect(element?.style.fontSize).toBe('16px');
    });

    it('should handle element reference', () => {
      const element = get('test-css');
      css(element, { backgroundColor: 'yellow' });

      expect(element?.style.backgroundColor).toBe('yellow');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => css('non-existent', { color: 'red' })).not.toThrow();
    });
  });

  describe('attr()', () => {
    it('should get attribute value', () => {
      const value = attr('test-attr', 'data-test');
      expect(value).toBe('value');
    });

    it('should set attribute value', () => {
      attr('test-attr', 'data-new', 'new-value');

      const element = get('test-attr');
      expect(element?.getAttribute('data-new')).toBe('new-value');
    });

    it('should return null for non-existent element', () => {
      const value = attr('non-existent', 'data-test');
      expect(value).toBeNull();
    });
  });

  describe('val()', () => {
    it('should get input value', () => {
      const value = val('test-input');
      expect(value).toBe('initial');
    });

    it('should set input value', () => {
      val('test-input', 'new-value');

      const element = get('test-input');
      expect(element?.value).toBe('new-value');
    });

    it('should return null for non-existent element', () => {
      const value = val('non-existent');
      expect(value).toBeNull();
    });
  });

  describe('position()', () => {
    it('should return position object', () => {
      const element = get('test-id');
      if (element) {
        element.style.position = 'absolute';
        element.style.left = '100px';
        element.style.top = '200px';
      }

      const pos = position('test-id');

      expect(pos).toHaveProperty('left');
      expect(pos).toHaveProperty('top');
    });

    it('should return object with 0 values for non-positioned element', () => {
      const pos = position('non-existent');
      // position() returns {left: 0, top: 0} for non-existent elements
      expect(pos).toHaveProperty('left');
      expect(pos).toHaveProperty('top');
    });
  });

  describe('ready()', () => {
    it('should not throw error when called', () => {
      const fn = vi.fn();
      expect(() => ready(fn)).not.toThrow();
    });

    it('should add event listener when document is loading', () => {
      const fn = vi.fn();
      const originalReadyState = document.readyState;
      Object.defineProperty(document, 'readyState', {
        value: 'loading',
        configurable: true,
      });

      ready(fn);

      expect(fn).not.toHaveBeenCalled();

      // Simulate DOMContentLoaded event
      const event = new Event('DOMContentLoaded');
      document.dispatchEvent(event);

      expect(fn).toHaveBeenCalled();

      Object.defineProperty(document, 'readyState', {
        value: originalReadyState,
        configurable: true,
      });
    });
  });
});
