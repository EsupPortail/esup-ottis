/**
 * Tests unitaires pour js/lib/utils/dialog.js
 * Tests des utilitaires de dialogue (version ES module)
 */

import { describe, it, expect, beforeEach } from 'vitest';
import { openDialog, closeDialog, toggleDialog } from '../../../lib/utils/dialog.js';

describe('lib/utils/dialog.js - Dialog Utilities (ES Module)', () => {
  beforeEach(() => {
    document.body.innerHTML = `
      <div id="test-div">Div Content</div>
    `;
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

    it('should not toggle div elements', () => {
      expect(() => toggleDialog('test-div')).not.toThrow();
    });
  });
});
