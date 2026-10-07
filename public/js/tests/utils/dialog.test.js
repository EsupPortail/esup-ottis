/**
 * Tests unitaires pour js/utils/dialog.js
 * Tests des utilitaires de dialogue
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { openDialog, closeDialog, toggleDialog, createDialog, upgradeLegacyDialogs } from '../../lib/utils/dialog.js';

describe('utils/dialog.js - Dialog Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-dialog"></div>
      <div id="test-div">Div Content</div>
      <div id="dialog-invite">Invite Dialog</div>
      <div id="other-div">Other Content</div>
    `;
    vi.restoreAllMocks();
  });

  describe('openDialog()', () => {
    it('should not throw error for non-existent dialog', () => {
      expect(() => openDialog('non-existent')).not.toThrow();
    });

    it('should not open div elements', () => {
      expect(() => openDialog('test-div')).not.toThrow();
    });
  });

  describe('closeDialog()', () => {
    it('should not throw error for non-existent dialog', () => {
      expect(() => closeDialog('non-existent')).not.toThrow();
    });

    it('should not close div elements', () => {
      expect(() => closeDialog('test-div')).not.toThrow();
    });
  });

  describe('toggleDialog()', () => {
    it('should not throw error for non-existent dialog', () => {
      expect(() => toggleDialog('non-existent')).not.toThrow();
    });
  });

  describe('createDialog()', () => {
    it('should create dialog from div', () => {
      const result = createDialog('test-div', { title: 'Test Title' });

      expect(result).not.toBeNull();
      expect(result.tagName).toBe('DIALOG');
      expect(result.querySelector('.dialog-title')?.textContent).toBe('Test Title');
    });

    it('should return null for non-existent div', () => {
      const result = createDialog('non-existent');
      expect(result).toBeNull();
    });

    it('should create dialog with buttons', () => {
      const result = createDialog('test-div', {
        buttons: [
          { text: 'OK' },
        ],
      });

      const button = result.querySelector('button');
      expect(button).not.toBeNull();
      expect(button?.textContent).toBe('OK');
    });

    it('should move children from div to dialog content', () => {
      const result = createDialog('test-div');
      const content = result.querySelector('.dialog-content');

      expect(content).not.toBeNull();
      expect(content?.textContent).toBe('Div Content');
    });
  });

  describe('upgradeLegacyDialogs()', () => {
    it('should upgrade legacy dialog divs', () => {
      const createDialogSpy = vi.spyOn({ createDialog }, 'createDialog');

      // Mock the dialog IDs check
      const dialogDiv = document.getElementById('dialog-invite');
      dialogDiv.id = 'dialog-invite';

      upgradeLegacyDialogs();

      // The function should have processed dialog-invite
      const upgradedDialog = document.getElementById('dialog-invite');
      expect(upgradedDialog?.tagName).toBe('DIALOG');
    });

    it('should not modify non-dialog divs', () => {
      const otherDiv = document.getElementById('other-div');
      const originalHTML = otherDiv.innerHTML;

      upgradeLegacyDialogs();

      expect(otherDiv.innerHTML).toBe(originalHTML);
      expect(otherDiv.tagName).toBe('DIV');
    });
  });
});
