/**
 * Tests unitaires pour js/lib/conference/dom-helpers.js
 * Tests des helpers DOM de la conférence
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { _, query, queryAll, openDialog, closeDialog } from '../../../lib/conference/dom-helpers.js';

describe('lib/conference/dom-helpers.js - Conference DOM Helpers', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-element">Test Content</div>
      <p class="test-class">Test Paragraph</p>
      <span class="test-class">Test Span</span>
    `;
  });

  describe('_()', () => {
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

  describe('openDialog()', () => {
    it('should not throw error for non-existent dialog', () => {
      expect(() => openDialog('non-existent')).not.toThrow();
    });

    it('should call showModal on dialog element', () => {
      document.body.innerHTML += '<dialog id="test-dialog"></dialog>';
      const dialog = document.getElementById('test-dialog');
      dialog.showModal = vi.fn();

      openDialog('test-dialog');

      expect(dialog.showModal).toHaveBeenCalled();
    });

    it('should not call showModal on non-dialog element', () => {
      const element = document.getElementById('test-element');
      element.showModal = vi.fn();

      openDialog('test-element');

      expect(element.showModal).not.toHaveBeenCalled();
    });
  });

  describe('closeDialog()', () => {
    it('should not throw error for non-existent dialog', () => {
      expect(() => closeDialog('non-existent')).not.toThrow();
    });

    it('should call close on dialog element', () => {
      document.body.innerHTML += '<dialog id="test-dialog2" open></dialog>';
      const dialog = document.getElementById('test-dialog2');
      dialog.close = vi.fn();

      closeDialog('test-dialog2');

      expect(dialog.close).toHaveBeenCalled();
    });

    it('should not call close on non-dialog element', () => {
      const element = document.getElementById('test-element');
      element.close = vi.fn();

      closeDialog('test-element');

      expect(element.close).not.toHaveBeenCalled();
    });
  });
});
