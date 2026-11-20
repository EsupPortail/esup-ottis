/**
 * Tests unitaires pour js/lib/xhr.js
 * Tests des fonctions XHR et fetch avec gestion d'erreur centralisée
 */

import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest';
import { addCsrfToken } from '../lib/network/utils.js';
import {
  handleXhrError,
  createXhrRequest,
  createFetchRequest,
  AbortTimeout,
  fetchWithErrorHandling,
} from '../lib/xhr.js';

// Capturer le vrai AbortController avant que des beforeEach ne le remplacent
// par un faux signal sans semantique d'annulation (test abort).
const RealAbortController = globalThis.AbortController;

// Constructeurs de mocks : fonctions classiques pour rester constructibles
// avec "new" (les fonctions fleches ne le sont pas sous Vitest 5)
function MockXhrRequest() {
  this.open = vi.fn();
  this.send = vi.fn();
  this.setRequestHeader = vi.fn();
  this.timeout = 0;
  this.readyState = 0;
  this.status = 0;
  this.responseText = '';
  this.onreadystatechange = null;
  this.ontimeout = null;
  this.onerror = null;
  this.addEventListener = vi.fn();
  this.upload = { addEventListener: vi.fn() };
}

function MockAbortController() {
  this.signal = {};
  this.abort = vi.fn();
}

// Mock fetch realiste : rejette si le signal d'annulation est leve
// (une promesse qui ignore le signal ne declencherait jamais le catch).
function abortableFetchMock(fetchUrl, opts) {
  return new Promise((resolve, reject) => {
    opts.signal.addEventListener('abort', () => {
      reject(new DOMException('The operation was aborted.', 'AbortError'));
    });
  });
}

describe('XHR Module - addCsrfToken', () => {
  beforeEach(() => {
    document.body.innerHTML = '<input type="hidden" name="csrf_token" value="test-csrf-token">';
  });

  it('should add CSRF token to FormData', () => {
    const formData = new FormData();
    addCsrfToken(formData);

    expect(formData.get('csrf_token')).toBe('test-csrf-token');
  });

  it('should return the FormData object', () => {
    const formData = new FormData();
    const result = addCsrfToken(formData);
    expect(result).toBe(formData);
  });

  it('should not add token if CSRF input not found', () => {
    document.body.innerHTML = '<div>no csrf</div>';
    const formData = new FormData();
    addCsrfToken(formData);

    expect(formData.get('csrf_token')).toBeNull();
  });

  it('should handle missing value attribute', () => {
    document.body.innerHTML = '<input type="hidden" name="csrf_token">';
    const formData = new FormData();
    addCsrfToken(formData);

    // FormData.get() returns null for empty string value
    expect(formData.get('csrf_token')).toBeNull();
  });
});

describe('XHR Module - handleXhrError', () => {
  let consoleErrorSpy;

  beforeEach(() => {
    consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
  });

  it('should handle network error (status 0)', () => {
    const mockXhr = { status: 0, statusText: '' };
    const result = handleXhrError(mockXhr, 'test-context');

    expect(result).toBe(true);
    expect(consoleErrorSpy).toHaveBeenCalledWith(
      '[test-context] Network error: Unable to connect to server'
    );
  });

  it('should handle HTTP 404 error', () => {
    const mockXhr = { status: 404, statusText: 'Not Found' };
    handleXhrError(mockXhr, 'api-call');

    expect(consoleErrorSpy).toHaveBeenCalledWith('[api-call] HTTP error 404: Not Found');
  });

  it('should handle HTTP 500 error', () => {
    const mockXhr = { status: 500, statusText: 'Internal Server Error' };
    handleXhrError(mockXhr, 'server-call');

    expect(consoleErrorSpy).toHaveBeenCalledWith(
      '[server-call] HTTP error 500: Internal Server Error'
    );
  });

  it('should handle unexpected errors', () => {
    const mockXhr = { status: 200, statusText: 'OK' };
    handleXhrError(mockXhr, 'unexpected');

    expect(consoleErrorSpy).toHaveBeenCalledWith('[unexpected] An unexpected error occurred');
  });

  it('should use default context when not provided', () => {
    const mockXhr = { status: 404, statusText: 'Not Found' };
    handleXhrError(mockXhr);

    expect(consoleErrorSpy).toHaveBeenCalledWith('[unknown] HTTP error 404: Not Found');
  });

  it('should return true', () => {
    const mockXhr = { status: 404 };
    const result = handleXhrError(mockXhr);
    expect(result).toBe(true);
  });
});

describe('XHR Module - createXhrRequest', () => {
  beforeEach(() => {
    globalThis.XMLHttpRequest = MockXhrRequest;
  });

  afterEach(() => {
    delete globalThis.XMLHttpRequest;
  });

  it('should create XMLHttpRequest with correct method and URL', () => {
    const xhr = createXhrRequest('POST', 'test.php');
    expect(xhr.open).toHaveBeenCalledWith('POST', 'test.php', true);
  });

  it('should set timeout', () => {
    const xhr = createXhrRequest('GET', 'test.php', { timeout: 5000 });
    expect(xhr.timeout).toBe(5000);
  });

  it('should use default timeout of 30000', () => {
    const xhr = createXhrRequest('GET', 'test.php');
    expect(xhr.timeout).toBe(30000);
  });

  it('should set Content-Type header for non-FormData POST', () => {
    const xhr = createXhrRequest('POST', 'test.php', {
      data: 'key=value',
      contentType: 'application/x-www-form-urlencoded',
    });
    expect(xhr.setRequestHeader).toHaveBeenCalledWith(
      'Content-Type',
      'application/x-www-form-urlencoded'
    );
  });

  it('should not set Content-Type for FormData', () => {
    const formData = new FormData();
    const xhr = createXhrRequest('POST', 'test.php', { data: formData });
    expect(xhr.setRequestHeader).not.toHaveBeenCalled();
  });

  it('should call onSuccess when request succeeds', () => {
    const onSuccess = vi.fn();
    const xhr = createXhrRequest('GET', 'test.php', { onSuccess });

    xhr.readyState = 4;
    xhr.status = 200;
    if (xhr.onreadystatechange) xhr.onreadystatechange();

    expect(onSuccess).toHaveBeenCalled();
  });

  it('should call onError when request fails', () => {
    const onError = vi.fn();
    const xhr = createXhrRequest('GET', 'test.php', {
      onError,
      context: 'test-context',
    });

    xhr.readyState = 4;
    xhr.status = 500;
    if (xhr.onreadystatechange) xhr.onreadystatechange();

    expect(onError).toHaveBeenCalled();
  });

  it('should handle timeout', () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
    const xhr = createXhrRequest('GET', 'test.php', { context: 'timeout-test' });

    if (xhr.ontimeout) xhr.ontimeout();

    expect(consoleSpy).toHaveBeenCalledWith('[timeout-test] Request timed out');

    consoleSpy.mockRestore();
  });

  it('should handle network error', () => {
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
    const xhr = createXhrRequest('GET', 'test.php', { context: 'network-test' });

    if (xhr.onerror) xhr.onerror();

    expect(consoleSpy).toHaveBeenCalledWith('[network-test] Network error');

    consoleSpy.mockRestore();
  });

  it('should add progress event listeners', () => {
    const onProgress = vi.fn();
    const onUploadProgress = vi.fn();

    const xhr = createXhrRequest('POST', 'test.php', {
      onProgress,
      onUploadProgress,
    });

    expect(xhr.addEventListener).toHaveBeenCalledWith('progress', onProgress, false);
    expect(xhr.upload.addEventListener).toHaveBeenCalledWith('progress', onUploadProgress, false);
  });

  it('should return XMLHttpRequest object', () => {
    const xhr = createXhrRequest('GET', 'test.php');
    expect(xhr).toBeTruthy();
  });
});

describe('XHR Module - AbortTimeout', () => {
  it('should set timeout and ontimeout handler', () => {
    const mockRequest = {
      timeout: 0,
      ontimeout: null,
      abort: vi.fn(),
    };

    AbortTimeout(mockRequest, 'test');

    expect(mockRequest.timeout).toBe(10000);
    expect(typeof mockRequest.ontimeout).toBe('function');

    mockRequest.ontimeout();
    expect(mockRequest.abort).toHaveBeenCalled();
  });
});

describe('XHR Module - fetchWithErrorHandling', () => {
  let consoleErrorSpy;

  beforeEach(() => {
    consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
    globalThis.fetch = vi.fn();
  });

  afterEach(() => {
    consoleErrorSpy.mockRestore();
    delete globalThis.fetch;
  });

  it('should call fetch with correct arguments', async () => {
    const mockResponse = {
      ok: true,
      status: 200,
      text: vi.fn().mockResolvedValue('response text'),
    };
    globalThis.fetch.mockResolvedValue(mockResponse);

    await fetchWithErrorHandling('test.php', { method: 'POST' });

    expect(fetch).toHaveBeenCalledWith('test.php', { method: 'POST' });
  });

  it('should return response on success', async () => {
    const mockResponse = {
      ok: true,
      status: 200,
    };
    globalThis.fetch.mockResolvedValue(mockResponse);

    const response = await fetchWithErrorHandling('test.php');

    expect(response).toBe(mockResponse);
  });

  it('should throw error on HTTP error', async () => {
    const mockResponse = {
      ok: false,
      status: 404,
      statusText: 'Not Found',
      text: vi.fn().mockResolvedValue('Not Found'),
    };
    globalThis.fetch.mockResolvedValue(mockResponse);

    await expect(fetchWithErrorHandling('test.php')).rejects.toThrow('HTTP 404');
    expect(consoleErrorSpy).toHaveBeenCalled();
  });

  it('should handle network errors', async () => {
    const error = new Error('Network error');
    error.name = 'TypeError';
    globalThis.fetch.mockRejectedValue(error);

    await expect(fetchWithErrorHandling('test.php')).rejects.toThrow('Network error');
    expect(consoleErrorSpy).toHaveBeenCalled();
  });

  it('should not log error for AbortError', async () => {
    const error = new Error('Aborted');
    error.name = 'AbortError';
    globalThis.fetch.mockRejectedValue(error);

    await expect(fetchWithErrorHandling('test.php')).rejects.toThrow('Aborted');
    expect(consoleErrorSpy).not.toHaveBeenCalled();
  });
});

describe('XHR Module - createFetchRequest', () => {
  let consoleWarnSpy;
  let consoleErrorSpy;

  beforeEach(() => {
    consoleWarnSpy = vi.spyOn(console, 'warn').mockImplementation(() => {});
    consoleErrorSpy = vi.spyOn(console, 'error').mockImplementation(() => {});
    globalThis.fetch = vi.fn();
    globalThis.AbortController = MockAbortController;
  });

  afterEach(() => {
    consoleWarnSpy.mockRestore();
    consoleErrorSpy.mockRestore();
    delete globalThis.fetch;
    delete globalThis.AbortController;
    vi.restoreAllMocks();
  });

  it('should create fetch request object with send method', () => {
    globalThis.fetch.mockResolvedValue({
      ok: true,
      status: 200,
      statusText: 'OK',
      text: vi.fn().mockResolvedValue('response text'),
      headers: { get: vi.fn() },
      body: null,
    });

    const request = createFetchRequest('GET', 'test.php');

    expect(request).toBeDefined();
    expect(typeof request.send).toBe('function');
    expect(typeof request.abort).toBe('function');
  });

  it('should call fetch with correct method and URL', async () => {
    globalThis.fetch.mockResolvedValue({
      ok: true,
      status: 200,
      text: vi.fn().mockResolvedValue('response'),
      headers: { get: vi.fn() },
      body: null,
    });

    const request = createFetchRequest('POST', 'test.php');
    await request.send();

    expect(fetch).toHaveBeenCalledWith('test.php', {
      method: 'POST',
      signal: expect.any(Object),
      headers: {},
    });
  });

  it('should call onSuccess with response data on success', async () => {
    const onSuccess = vi.fn();
    globalThis.fetch.mockResolvedValue({
      ok: true,
      status: 200,
      statusText: 'OK',
      text: vi.fn().mockResolvedValue('success response'),
      headers: { get: vi.fn().mockReturnValue('text/plain') },
      body: null,
    });

    const request = createFetchRequest('GET', 'test.php', { onSuccess });
    await request.send();

    expect(onSuccess).toHaveBeenCalledWith({
      status: 200,
      statusText: 'OK',
      responseText: 'success response',
      getResponseHeader: expect.any(Function),
    });
  });

  it('should call onError on fetch failure', async () => {
    const onError = vi.fn();
    globalThis.fetch.mockResolvedValue({
      ok: false,
      status: 404,
      statusText: 'Not Found',
      text: vi.fn().mockResolvedValue('Not Found'),
      headers: { get: vi.fn() },
      body: null,
    });

    const request = createFetchRequest('GET', 'test.php', { onError, context: 'test' });
    await request.send();

    expect(onError).toHaveBeenCalledWith(
      {
        status: 404,
        statusText: 'Not Found',
        responseText: 'Not Found',
        getResponseHeader: expect.any(Function),
      },
      'test'
    );
  });

  it('should handle timeout', async () => {
    const onError = vi.fn();
    // Mock realiste : fetch rejette si le signal d'annulation est leve
    // (une promesse qui ignore le signal ne declencherait jamais le catch)
    globalThis.fetch = vi.fn(abortableFetchMock);

    const request = createFetchRequest('GET', 'test.php', {
      timeout: 10,
      onError,
      context: 'timeout-test',
    });

    request.send();

    // Wait for timeout to trigger
    await new Promise((resolve) => {
      setTimeout(resolve, 20);
    });

    expect(onError).toHaveBeenCalled();
    expect(consoleErrorSpy).toHaveBeenCalled();
  });

  it('should fall back to XMLHttpRequest when onUploadProgress is provided', () => {
    globalThis.XMLHttpRequest = MockXhrRequest;

    const onUploadProgress = vi.fn();
    const request = createFetchRequest('POST', 'test.php', {
      onUploadProgress,
    });

    expect(consoleWarnSpy).toHaveBeenCalledWith(
      'createFetchRequest: Upload progress tracking requires XMLHttpRequest fallback'
    );
    expect(request.xhr).toBeDefined(); // Should be XMLHttpRequest

    delete globalThis.XMLHttpRequest;
  });

  it('should send FormData body', async () => {
    const formData = new FormData();
    formData.append('key', 'value');

    globalThis.fetch.mockResolvedValue({
      ok: true,
      status: 200,
      text: vi.fn().mockResolvedValue('response'),
      headers: { get: vi.fn() },
      body: null,
    });

    const request = createFetchRequest('POST', 'test.php', { data: formData });
    await request.send();

    expect(fetch).toHaveBeenCalledWith('test.php', {
      method: 'POST',
      signal: expect.any(Object),
      headers: {},
      body: formData,
    });
  });

  it('should handle object data by converting to URLSearchParams', async () => {
    globalThis.fetch.mockResolvedValue({
      ok: true,
      status: 200,
      text: vi.fn().mockResolvedValue('response'),
      headers: { get: vi.fn() },
      body: null,
    });

    const request = createFetchRequest('POST', 'test.php', {
      data: { key: 'value', another: 'test' },
      contentType: 'application/x-www-form-urlencoded',
    });
    await request.send();

    const callArgs = fetch.mock.calls[0];
    const { body } = callArgs[1];

    expect(typeof body).toBe('string');
    expect(body).toContain('key=value');
    expect(body).toContain('another=test');
  });

  it('should call abort function', async () => {
    // Retablir le vrai AbortController : le faux du beforeEach n'a pas de
    // semantique d'annulation, indispensable pour tester abort().
    globalThis.AbortController = RealAbortController;
    globalThis.fetch = vi.fn(abortableFetchMock);

    const request = createFetchRequest('GET', 'test.php');
    const sendPromise = request.send();
    request.abort();

    // L'abort declenche le chemin AbortError : send() resout avec la reponse d'erreur
    const result = await sendPromise;
    expect(result.status).toBe(0);
    expect(result.statusText).toBeTruthy();
  });
});
