/**
 * Tests unitaires pour js/lib/colors.js
 * Tests des fonctions de gestion des couleurs
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { rgb, getUserColor, ColorsForCR } from '../lib/colors.js';

describe('Colors Module', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  describe('ColorsForCR', () => {
    it('should have default colors defined', () => {
      expect(ColorsForCR).toHaveProperty('----');
      expect(ColorsForCR).toHaveProperty('Intervenant');
    });

    it('should have correct format for colors', () => {
      expect(ColorsForCR['----']).toEqual([[255, 0, 0], [255, 255, 255], 5]);
      expect(ColorsForCR.Intervenant).toEqual([[0, 0, 255], [255, 255, 255], 0]);
    });
  });

  describe('rgb()', () => {
    it('should convert RGB array to string', () => {
      expect(rgb([255, 0, 0])).toBe('rgb(255,0,0)');
      expect(rgb([0, 255, 0])).toBe('rgb(0,255,0)');
      expect(rgb([0, 0, 255])).toBe('rgb(0,0,255)');
      expect(rgb([0, 0, 0])).toBe('rgb(0,0,0)');
      expect(rgb([255, 255, 255])).toBe('rgb(255,255,255)');
    });
  });

  describe('getUserColor()', () => {
    it('should return array with background and text colors', () => {
      const colors = getUserColor('test-user');
      expect(Array.isArray(colors)).toBe(true);
      expect(colors.length).toBe(2);
      expect(Array.isArray(colors[0])).toBe(true);
      expect(Array.isArray(colors[1])).toBe(true);
    });

    it('should generate RGB values in range 55-255', () => {
      const colors = getUserColor('test-user-2');
      const [bgColor] = colors;
      bgColor.forEach((component) => {
        expect(component).toBeGreaterThanOrEqual(55);
        expect(component).toBeLessThanOrEqual(255);
      });
    });

    it('should never return intervenant blue (0,0,255)', () => {
      for (let i = 0; i < 50; i++) {
        const colors = getUserColor(`user-${i}`);
        const [bgColor] = colors;
        expect(bgColor).not.toEqual([0, 0, 255]);
      }
    });

    it('should use bright text for dark backgrounds', () => {
      // Mock Math.floor to return low values for a dark background
      const originalFloor = Math.floor;
      Math.floor = () => 55; // Dark color

      const colors = getUserColor('dark-user');
      const [, textColor] = colors;
      expect(textColor).toEqual([255, 255, 255]);

      Math.floor = originalFloor;
    });

    it('should use dark text for bright backgrounds', () => {
      // Mock Math.floor to return high values for a bright background
      const originalFloor = Math.floor;
      Math.floor = () => 255; // Bright color

      const colors = getUserColor('bright-user');
      const [, textColor] = colors;
      expect(textColor).toEqual([0, 0, 0]);

      Math.floor = originalFloor;
    });

    it('should persist colors in localStorage', () => {
      const colors1 = getUserColor('persistent-user');
      const colors2 = getUserColor('persistent-user');
      expect(colors1).toEqual(colors2);
    });

    it('should regenerate color when stored value is invalid JSON', () => {
      const storageKey = 'chatUserColor_invalid-json';
      localStorage.setItem(storageKey, 'invalid-json{');

      const colors = getUserColor('invalid-json');
      expect(Array.isArray(colors)).toBe(true);
      expect(colors.length).toBe(2);
    });

    it('should handle localStorage setItem failure gracefully', () => {
      const originalSetItem = Storage.prototype.setItem;
      Storage.prototype.setItem = () => {
        throw new Error('localStorage full');
      };

      const consoleWarnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});

      const colors = getUserColor('storage-fail-user');
      expect(Array.isArray(colors)).toBe(true);
      expect(colors.length).toBe(2);
      expect(consoleWarnSpy).toHaveBeenCalled();

      Storage.prototype.setItem = originalSetItem;
      consoleWarnSpy.mockRestore();
    });
  });
});
