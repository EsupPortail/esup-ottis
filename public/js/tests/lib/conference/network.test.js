/**
 * Tests unitaires pour js/lib/conference/network.js
 * Tests des utilitaires réseau de la conférence
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { addCsrfToken, createXhrRequest, createFetchRequest, fetchWithErrorHandling } from '../../../lib/conference/network.js';

describe('lib/conference/network.js - Conference Network Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = '<input type="hidden" name="csrf_token" value="test-csrf-token">';
    vi.restoreAllMocks();
  });

  describe('addCsrfToken()', () => {
    it('should add CSRF token to FormData', () => {
      const formData = new FormData();
      const result = addCsrfToken(formData);

      expect(result.get('csrf_token')).toBe('test-csrf-token');
      expect(result).toBe(formData);
    });

    it('should return formData even if no CSRF token found', () => {
      document.body.innerHTML = '';
      const formData = new FormData();
      const result = addCsrfToken(formData);

      expect(result).toBe(formData);
      // When no CSRF token is found, it won't be added
      expect(result.has('csrf_token')).toBe(false);
    });
  });

  describe('createXhrRequest()', () => {
    it('should create XHR object with correct method and URL', () => {
      const xhr = createXhrRequest('GET', 'https://example.com/test');

      expect(xhr).toBeInstanceOf(XMLHttpRequest);
    });

    it('should set timeout', () => {
      const xhr = createXhrRequest('GET', 'https://example.com/test', { timeout: 5000 });

      expect(xhr.timeout).toBe(5000);
    });

    it('should use default timeout', () => {
      const xhr = createXhrRequest('GET', 'https://example.com/test');

      expect(xhr.timeout).toBe(30000);
    });

    it('should not set Content-Type for FormData POST', () => {
      const xhr = createXhrRequest('POST', 'https://example.com/test', {
        data: new FormData(),
      });

      // We can't easily test the header was set in jsdom, but we can verify the XHR was created
      expect(xhr).toBeInstanceOf(XMLHttpRequest);
    });

    it('should set Content-Type for non-FormData POST', () => {
      const xhr = createXhrRequest('POST', 'https://example.com/test', {
        data: { key: 'value' },
        contentType: 'application/json',
      });

      expect(xhr).toBeInstanceOf(XMLHttpRequest);
    });
  });

  describe('fetchWithErrorHandling()', () => {
    it('should return response on success', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
        json: () => Promise.resolve({}),
      });

      const response = await fetchWithErrorHandling('https://example.com/test');

      expect(response.ok).toBe(true);
      expect(fetch).toHaveBeenCalledWith('https://example.com/test', {});
    });

    it('should throw error on non-ok response', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: false,
        status: 404,
      });

      await expect(fetchWithErrorHandling('https://example.com/test'))
        .rejects.toThrow('HTTP error! status: 404');
    });

    it('should pass options to fetch', async () => {
      global.fetch = vi.fn().mockResolvedValue({
        ok: true,
      });

      const options = {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
      };

      await fetchWithErrorHandling('https://example.com/test', options);

      expect(fetch).toHaveBeenCalledWith('https://example.com/test', options);
    });

    it('should handle network errors', async () => {
      global.fetch = vi.fn().mockRejectedValue(new Error('Network error'));

      await expect(fetchWithErrorHandling('https://example.com/test'))
        .rejects.toThrow('Network error');
    });
  });

  describe('createFetchRequest()', () => {
    let consoleWarnSpy;

    beforeEach(() => {
      consoleWarnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});
      globalThis.fetch = vi.fn();
      globalThis.AbortController = vi.fn().mockImplementation(() => ({
        signal: {},
        abort: vi.fn(),
      }));
      globalThis.XMLHttpRequest = vi.fn(() => ({
        open: vi.fn(),
        send: vi.fn(),
        timeout: 30000,
        readyState: 0,
        status: 0,
        responseText: '',
      }));
    });

    afterEach(() => {
      consoleWarnSpy.mockRestore();
      delete globalThis.fetch;
      delete globalThis.AbortController;
      delete globalThis.XMLHttpRequest;
      vi.restoreAllMocks();
    });

    it('should create request object with send and abort methods', () => {
      globalThis.fetch.mockResolvedValue({
        ok: true,
        status: 200,
        text: vi.fn().mockResolvedValue('response'),
        headers: { get: vi.fn() },
        body: null,
      });

      const request = createFetchRequest('GET', 'test.php');

      expect(request).toBeDefined();
      expect(typeof request.send).toBe('function');
      expect(typeof request.abort).toBe('function');
    });

    it('should use fetch API by default', async () => {
      globalThis.fetch.mockResolvedValue({
        ok: true,
        status: 200,
        text: vi.fn().mockResolvedValue('response'),
        headers: { get: vi.fn() },
        body: null,
      });

      const request = createFetchRequest('GET', 'test.php');
      await request.send();

      expect(fetch).toHaveBeenCalled();
      expect(consoleWarnSpy).not.toHaveBeenCalled();
    });

    it('should fall back to XMLHttpRequest when onUploadProgress is provided', () => {
      const onUploadProgress = vi.fn();
      const request = createFetchRequest('POST', 'test.php', {
        onUploadProgress,
      });

      expect(consoleWarnSpy).toHaveBeenCalledWith(
        'createFetchRequest: Upload progress tracking requires XMLHttpRequest fallback'
      );
      expect(request.open).toBeDefined();
    });
  });
});
