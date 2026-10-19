/**
 * Tests unitaires pour js/lib/dom.js
 * Tests des utilitaires DOM de base
 */

import { describe, it, expect, beforeEach } from 'vitest';
import { _, hide, show, resizeWindow, ChangeColor, ChangeOpacity } from '../../lib/dom.js';

describe('lib/dom.js - DOM Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-element">Test Content</div>
      <div id="test-div" style="display: block; background-color: rgba(255, 0, 0, 0.5);">Content</div>
      <div id="color-box"></div>
      <input type="text" id="selectopacity" value="0.8">
      <div id="window-element" style="width: 100px; height: 100px; position: absolute;"></div>
    `;
  });

  describe('_() - getElementById shortcut', () => {
    it('should return element by ID', () => {
      const element = _('test-element');
      expect(element).not.toBeNull();
      expect(element?.textContent).toBe('Test Content');
    });

    it('should return null for non-existent ID', () => {
      const element = _('non-existent');
      expect(element).toBeNull();
    });
  });

  describe('hide()', () => {
    it('should hide element by ID', () => {
      hide('test-element');
      const element = _('test-element');
      expect(element?.style.display).toBe('none');
    });

    it('should hide element by element reference', () => {
      const element = _('test-element');
      hide(element);
      expect(element?.style.display).toBe('none');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => hide('non-existent')).not.toThrow();
    });
  });

  describe('show()', () => {
    it('should show hidden element by ID', () => {
      const element = _('test-element');
      if (element) element.style.display = 'none';

      show('test-element');
      expect(_('test-element')?.style.display).toBe('block');
    });

    it('should show element by element reference', () => {
      const element = _('test-element');
      if (element) element.style.display = 'none';

      show(element);
      expect(element?.style.display).toBe('block');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => show('non-existent')).not.toThrow();
    });
  });

  describe('resizeWindow()', () => {
    it('should resize window element to fit viewport', () => {
      const originalInnerWidth = window.innerWidth;
      const originalInnerHeight = window.innerHeight;

      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 1000,
      });
      Object.defineProperty(window, 'innerHeight', {
        writable: true,
        configurable: true,
        value: 800,
      });

      resizeWindow('window-element');

      const element = _('window-element');
      expect(element?.style.width).toBe('980px');
      expect(element?.style.height).toBe('752px');
      expect(element?.style.top).toBe('48px');
      expect(element?.style.left).toBe('0px');

      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: originalInnerWidth,
      });
      Object.defineProperty(window, 'innerHeight', {
        writable: true,
        configurable: true,
        value: originalInnerHeight,
      });
    });

    it('should not throw error for non-existent element', () => {
      expect(() => resizeWindow('non-existent')).not.toThrow();
    });
  });

  describe('ChangeColor()', () => {
    it('should change div color with all three color components', () => {
      ChangeColor('color-box', '255,0,0|0,255,0|255,255,255');
      const element = _('color-box');

      expect(element?.style.color).toBe('rgb(255, 255, 255)');
      expect(element?.style.backgroundColor).toContain('255, 0, 0');
      expect(element?.style.boxShadow).toContain('rgb(255,0,0)');
    });

    it('should use default text color when textcolor is empty', () => {
      ChangeColor('color-box', '0,0,0|0,0,0|');
      const element = _('color-box');

      expect(element?.style.color).toBe('rgb(255, 255, 255)');
    });

    it('should use opacity from selectopacity element', () => {
      ChangeColor('color-box', '255,0,0|0,0,0|0,0,0');
      const element = _('color-box');

      expect(element?.style.backgroundColor).toContain('0.8');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => ChangeColor('non-existent', '255,0,0|0,0,0|0,0,0')).not.toThrow();
    });
  });

  describe('ChangeOpacity()', () => {
    it('should change element opacity', () => {
      const element = _('test-div');
      if (element) {
        element.style.backgroundColor = 'rgba(255, 0, 0, 0.5)';
      }

      ChangeOpacity('test-div', 0.3);

      const updatedElement = _('test-div');
      expect(updatedElement?.style.backgroundColor).toContain('0.3');
    });

    it('should not throw error for non-existent element', () => {
      expect(() => ChangeOpacity('non-existent', 0.5)).not.toThrow();
    });

    it('should not throw error for element without background color', () => {
      document.body.innerHTML += '<div id="no-color"></div>';
      expect(() => ChangeOpacity('no-color', 0.5)).not.toThrow();
    });

    it('should not throw error for empty background color', () => {
      document.body.innerHTML += '<div id="empty-color" style="background-color: "></div>';
      expect(() => ChangeOpacity('empty-color', 0.5)).not.toThrow();
    });
  });
});
