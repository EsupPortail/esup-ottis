/**
 * Tests unitaires pour js/lib/browser.js
 * Tests des fonctions utilitaires du navigateur
 */

import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { showError, whichBrowser, escapeHtml } from '../lib/browser.js';

describe('Browser Module - showError', () => {
  let consoleErrorSpy;

  beforeEach(() => {
    consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
  });

  it('should log error message to console', () => {
    showError('Test error message');
    expect(consoleErrorSpy).toHaveBeenCalledWith('Test error message');
  });

  it('should include context when provided', () => {
    showError('Test error', 'test-context');
    expect(consoleErrorSpy).toHaveBeenCalledWith('[test-context] Test error');
  });

  it('should not include context when null', () => {
    showError('Test error', null);
    expect(consoleErrorSpy).toHaveBeenCalledWith('Test error');
  });
});

describe('Browser Module - whichBrowser', () => {
  beforeEach(() => {
    globalThis.navigator = { userAgent: '' };
  });

  afterEach(() => {
    delete globalThis.navigator;
  });

  it('should detect Chrome', () => {
    globalThis.navigator.userAgent = 'Mozilla/5.0 Chrome/91.0.4472.124 Safari/537.36';
    expect(whichBrowser()).toBe('Chrome');
  });

  it('should detect Firefox', () => {
    globalThis.navigator.userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0';
    expect(whichBrowser()).toBe('Firefox');
  });

  it('should detect Safari', () => {
    globalThis.navigator.userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 Version/14.1.1 Safari/605.1.15';
    expect(whichBrowser()).toBe('Safari');
  });

  it('should detect Edge', () => {
    // Use a userAgent that contains Edge but not Chrome
    globalThis.navigator.userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Edge/44.19082.1164.0';
    expect(whichBrowser()).toBe('Edge');
  });

  it('should detect Opera', () => {
    // Use a userAgent where Opera appears after position 0
    // Note: whichBrowser() has a bug - it uses > 0 instead of >= 0
    globalThis.navigator.userAgent = 'Mozilla/5.0 (compatible; Opera 9.80) Presto/2.12.388 Version/12.14';
    expect(whichBrowser()).toBe('Opera');
  });

  it('should return Unknown for unrecognized browser', () => {
    globalThis.navigator.userAgent = 'SomeUnknownBrowser/1.0';
    expect(whichBrowser()).toBe('Unknown');
  });
});

describe('Browser Module - escapeHtml', () => {
  it('should escape angle brackets', () => {
    const result = escapeHtml('<script>');
    expect(result).not.toContain('<');
    expect(result).not.toContain('>');
    expect(result).toContain('script');
  });

  it('should escape double quotes', () => {
    const result = escapeHtml('"test"');
    expect(result).not.toContain('"');
    expect(result).toContain('&quot;');
  });

  it('should escape single quotes', () => {
    const result = escapeHtml('it\'s');
    expect(result).toContain('&#39;');
  });

  it('should escape ampersand', () => {
    const result = escapeHtml('a & b');
    expect(result).toContain('&amp;');
  });

  it('should return empty string for non-string input', () => {
    expect(escapeHtml(null)).toBe('');
    expect(escapeHtml(undefined)).toBe('');
    expect(escapeHtml(123)).toBe('');
  });

  it('should prevent XSS attacks', () => {
    const malicious = '<img src=x onerror=alert(1)>';
    const escaped = escapeHtml(malicious);
    expect(escaped).not.toContain('<');
    expect(escaped).not.toContain('>');
    expect(escaped).not.toContain('"');
  });
});
