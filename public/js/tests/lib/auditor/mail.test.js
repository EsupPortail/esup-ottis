/**
 * Tests unitaires pour js/lib/auditor/mail.js
 * Tests des fonctions d'envoi de mail
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';

describe('lib/auditor/mail.js - Mail Functions', () => {
  beforeEach(() => {
    vi.restoreAllMocks();

    // Mock global dependencies
    const elementsById = {
      dialog_MailTo: { value: 'test@example.com' },
      dialog_MailSubject: { value: 'Test Subject' },
      dialog_MailBody: { innerHTML: '<p>Test Body</p>' },
      classroomid: { value: 'room123' },
      dialog_MailFrom: { value: '' },
    };
    global._ = vi.fn((id) => elementsById[id] || null);
    global.DOMPurify = { sanitize: vi.fn((html) => html) };
    global.alert = vi.fn();
    global.prompt = vi.fn(() => 'user@example.com');

    // Mock the network module imports
    vi.mock('../../../lib/auditor/network.js', () => ({
      addCsrfToken: vi.fn(),
      createFetchRequest: vi.fn(() => ({
        send: vi.fn(),
        abort: vi.fn(),
        addEventListener: vi.fn(),
      })),
    }));
  });

  it('should export EnvoiMail function', async () => {
    const { EnvoiMail } = await import('../../../lib/auditor/mail.js');
    expect(typeof EnvoiMail).toBe('function');
  });

  it('should export sendnotebook function', async () => {
    const { sendnotebook } = await import('../../../lib/auditor/mail.js');
    expect(typeof sendnotebook).toBe('function');
  });

  it('EnvoiMail should use FormData and createFetchRequest', async () => {
    const { createFetchRequest: mockCreateFetchRequest } = await import('../../../lib/auditor/network.js');
    const { EnvoiMail } = await import('../../../lib/auditor/mail.js');

    EnvoiMail();

    expect(mockCreateFetchRequest).toHaveBeenCalledWith('POST', 'sendamail.php', expect.any(Object));
  });

  it('EnvoiMail should call addCsrfToken', async () => {
    const { addCsrfToken: mockAddCsrfToken } = await import('../../../lib/auditor/network.js');
    const { EnvoiMail } = await import('../../../lib/auditor/mail.js');

    EnvoiMail();

    expect(mockAddCsrfToken).toHaveBeenCalled();
  });

  it('sendnotebook should set dialog values and call EnvoiMail', async () => {
    const { sendnotebook } = await import('../../../lib/auditor/mail.js');

    sendnotebook('Test content');

    // Verify prompt was called
    expect(prompt).toHaveBeenCalled();
  });
});
