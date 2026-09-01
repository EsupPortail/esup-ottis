/**
 * Tests unitaires pour js/common-func.js
 * Se concentre sur les fonctions pures et faciles à tester
 */

import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { JSDOM } from 'jsdom';

// Import des fonctions depuis common-func.js
const commonFunc = require('../common-func.js');

describe('Common Functions - Pure Functions', () => {
  describe('rgb()', () => {
    it('should convert RGB array to string', () => {
      expect(commonFunc.rgb([255, 0, 0])).toBe('rgb(255,0,0)');
      expect(commonFunc.rgb([0, 255, 0])).toBe('rgb(0,255,0)');
      expect(commonFunc.rgb([0, 0, 255])).toBe('rgb(0,0,255)');
      expect(commonFunc.rgb([0, 0, 0])).toBe('rgb(0,0,0)');
      expect(commonFunc.rgb([255, 255, 255])).toBe('rgb(255,255,255)');
    });
  });

  describe('escapeHtml()', () => {
    it('should escape angle brackets', () => {
      const result1 = commonFunc.escapeHtml('<script>');
      expect(result1).not.toContain('<');
      expect(result1).not.toContain('>');
      expect(result1).toContain('script');

      const result2 = commonFunc.escapeHtml('<div>test</div>');
      expect(result2).not.toContain('<div>');
      expect(result2).toContain('test');
    });

    it('should handle single quotes', () => {
      const result = commonFunc.escapeHtml('it\'s');
      expect(result).toContain('&#39;');
    });

    it('should return empty string for non-string input', () => {
      expect(commonFunc.escapeHtml(null)).toBe('');
      expect(commonFunc.escapeHtml(undefined)).toBe('');
      expect(commonFunc.escapeHtml(123)).toBe('');
    });

    it('should prevent XSS attacks with angle brackets', () => {
      const malicious = '<img src=x onerror=alert(1)>';
      const escaped = commonFunc.escapeHtml(malicious);
      expect(escaped).not.toContain('<');
      expect(escaped).not.toContain('>');
    });
  });
});

describe('Common Functions - Browser Detection', () => {
  beforeEach(() => {
    globalThis.navigator = { userAgent: '' };
  });

  afterEach(() => {
    delete globalThis.navigator;
  });

  describe('whichBrowser()', () => {
    it('should detect Chrome', () => {
      globalThis.navigator.userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/91.0.4472.124 Safari/605.1.15';
      expect(commonFunc.whichBrowser()).toBe('Chrome');
    });

    it('should detect Firefox', () => {
      globalThis.navigator.userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0';
      expect(commonFunc.whichBrowser()).toBe('Firefox');
    });

    it('should detect Safari', () => {
      globalThis.navigator.userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/14.1.1 Safari/605.1.15';
      expect(commonFunc.whichBrowser()).toBe('Safari');
    });

    it('should detect Edge', () => {
      // Use a userAgent that contains Edge but not Chrome
      globalThis.navigator.userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/44.19082.1164.0';
      expect(commonFunc.whichBrowser()).toBe('Edge');
    });

    it('should return Unknown for unrecognized browser', () => {
      globalThis.navigator.userAgent = 'SomeUnknownBrowser/1.0';
      expect(commonFunc.whichBrowser()).toBe('Unknown');
    });
  });
});

describe('Common Functions - Error Handling', () => {
  let consoleErrorSpy;

  beforeEach(() => {
    consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
  });

  describe('showError()', () => {
    it('should log error message to console', () => {
      commonFunc.showError('Test error message');
      expect(consoleErrorSpy).toHaveBeenCalledWith('Test error message');
    });

    it('should include context when provided', () => {
      commonFunc.showError('Test error', 'test-context');
      expect(consoleErrorSpy).toHaveBeenCalledWith('[test-context] Test error');
    });
  });

  describe('handleXhrError()', () => {
    it('should handle network error (status 0)', () => {
      const mockXhr = { status: 0, statusText: '' };
      const result = commonFunc.handleXhrError(mockXhr, 'test-context');

      expect(result).toBe(true);
      expect(consoleErrorSpy).toHaveBeenCalledWith('[test-context] Network error: Unable to connect to server');
    });

    it('should handle HTTP 404 error', () => {
      const mockXhr = { status: 404, statusText: 'Not Found' };
      commonFunc.handleXhrError(mockXhr, 'api-call');

      expect(consoleErrorSpy).toHaveBeenCalledWith('[api-call] HTTP error 404: Not Found');
    });

    it('should handle HTTP 500 error', () => {
      const mockXhr = { status: 500, statusText: 'Internal Server Error' };
      commonFunc.handleXhrError(mockXhr, 'server-call');

      expect(consoleErrorSpy).toHaveBeenCalledWith('[server-call] HTTP error 500: Internal Server Error');
    });

    it('should return true', () => {
      const mockXhr = { status: 404 };
      const result = commonFunc.handleXhrError(mockXhr);
      expect(result).toBe(true);
    });
  });
});

describe('Common Functions - User Colors', () => {
  beforeEach(() => {
    localStorage.clear();
  });

  describe('getUserColor()', () => {
    it('should return array with background and text colors', () => {
      const colors = commonFunc.getUserColor('test-user');
      expect(Array.isArray(colors)).toBe(true);
      expect(colors.length).toBe(2);
      expect(Array.isArray(colors[0])).toBe(true);
      expect(Array.isArray(colors[1])).toBe(true);
    });

    it('should generate RGB values in range 55-255', () => {
      const colors = commonFunc.getUserColor('test-user-2');
      const [bgColor] = colors;
      bgColor.forEach((component) => {
        expect(component).toBeGreaterThanOrEqual(55);
        expect(component).toBeLessThanOrEqual(255);
      });
    });

    it('should never return intervenant blue (0,0,255)', () => {
      for (let i = 0; i < 50; i++) {
        const colors = commonFunc.getUserColor(`user-${i}`);
        const [bgColor] = colors;
        expect(bgColor).not.toEqual([0, 0, 255]);
      }
    });

    it('should persist colors in localStorage', () => {
      const colors1 = commonFunc.getUserColor('persistent-user');
      const colors2 = commonFunc.getUserColor('persistent-user');
      expect(colors1).toEqual(colors2);
    });
  });
});

describe('Common Functions - AbortTimeout', () => {
  it('should set timeout and ontimeout handler', () => {
    const mockRequest = {
      timeout: 0,
      ontimeout: null,
      abort: vi.fn(),
    };

    commonFunc.AbortTimeout(mockRequest);

    expect(mockRequest.timeout).toBe(10000);
    expect(typeof mockRequest.ontimeout).toBe('function');

    mockRequest.ontimeout();
    expect(mockRequest.abort).toHaveBeenCalled();
  });
});
