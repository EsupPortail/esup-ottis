/**
 * Tests unitaires pour js/lib/download.js
 * Tests des utilitaires de téléchargement de fichiers
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { DownloadSpeechLesson } from '../../lib/download.js';

describe('lib/download.js - Download Utilities', () => {
  beforeEach(() => {
    document.body.innerHTML = '';
    vi.restoreAllMocks();
  });

  describe('DownloadSpeechLesson()', () => {
    it('should create download link with correct attributes', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      const clickSpy = vi.fn();

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn(),
        click: clickSpy,
      });

      DownloadSpeechLesson('test.txt', 'Hello World');

      expect(createElementSpy).toHaveBeenCalledWith('a');
      expect(clickSpy).toHaveBeenCalled();
    });

    it('should replace <br> tags with newlines', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      let hrefValue = '';

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn((name, value) => {
          if (name === 'href') hrefValue = value;
        }),
        click: vi.fn(),
      });

      DownloadSpeechLesson('test.txt', 'Line 1<br>Line 2');

      expect(hrefValue).toContain('Line%201%0ALine%202');
      expect(hrefValue).not.toContain('<br>');
    });

    it('should use default mime type when not specified', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      let hrefValue = '';

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn((name, value) => {
          if (name === 'href') hrefValue = value;
        }),
        click: vi.fn(),
      });

      DownloadSpeechLesson('test.txt', 'content');

      expect(hrefValue).toContain('data:text/plain');
    });

    it('should use custom mime type when specified', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      let hrefValue = '';

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn((name, value) => {
          if (name === 'href') hrefValue = value;
        }),
        click: vi.fn(),
      });

      DownloadSpeechLesson('test.json', '{"key": "value"}', 'application/json');

      expect(hrefValue).toContain('data:application/json');
    });

    it('should set correct download filename', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      let downloadValue = '';

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn((name, value) => {
          if (name === 'download') downloadValue = value;
        }),
        click: vi.fn(),
      });

      DownloadSpeechLesson('my-file.txt', 'content');

      expect(downloadValue).toBe('my-file.txt');
    });

    it('should encode content with encodeURIComponent', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      let hrefValue = '';

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn((name, value) => {
          if (name === 'href') hrefValue = value;
        }),
        click: vi.fn(),
      });

      DownloadSpeechLesson('test.txt', 'Hello & goodbye');

      expect(hrefValue).toContain('Hello%20%26%20goodbye');
    });

    it('should include charset=utf-8 in data URL', () => {
      const createElementSpy = vi.spyOn(document, 'createElement');
      let hrefValue = '';

      createElementSpy.mockReturnValue({
        setAttribute: vi.fn((name, value) => {
          if (name === 'href') hrefValue = value;
        }),
        click: vi.fn(),
      });

      DownloadSpeechLesson('test.txt', 'content');

      expect(hrefValue).toContain('charset=utf-8');
    });
  });
});
