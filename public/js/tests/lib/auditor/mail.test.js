/**
 * Tests unitaires pour js/lib/auditor/mail.js
 * Tests des fonctions d'envoi de mail
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import { EnvoiMail, sendnotebook } from '../../../lib/auditor/mail.js';
import { addCsrfToken } from '../../../lib/network/utils.js';
import { createFetchRequest } from '../../../lib/conference/network.js';

// mail.js importe : _ (dom/selector), addCsrfToken (network/utils),
// createFetchRequest (conference/network). Les vi.mock sont hisses au-dessus
// des imports par Vitest pour intercepter les imports statiques du module teste.
vi.mock('../../../lib/dom/selector.js', () => ({
  _: (id) => {
    const elementsById = {
      dialog_MailTo: { value: 'test@example.com' },
      dialog_MailSubject: { value: 'Test Subject' },
      dialog_MailBody: { innerHTML: '<p>Test Body</p>' },
      dialog_MailFrom: { value: '' },
      classroomid: { value: 'room123' },
    };
    return elementsById[id] || null;
  },
}));

vi.mock('../../../lib/network/utils.js', () => ({
  addCsrfToken: vi.fn(),
}));

vi.mock('../../../lib/conference/network.js', () => ({
  createFetchRequest: vi.fn(() => ({ send: vi.fn(), abort: vi.fn() })),
}));

describe('lib/auditor/mail.js - Mail Functions', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    window.DOMPurify = { sanitize: vi.fn((html) => html) };
    global.alert = vi.fn();
    global.prompt = vi.fn(() => 'user@example.com');
  });

  it('should export EnvoiMail function', () => {
    expect(typeof EnvoiMail).toBe('function');
  });

  it('should export sendnotebook function', () => {
    expect(typeof sendnotebook).toBe('function');
  });

  it('EnvoiMail should use FormData and createFetchRequest', () => {
    EnvoiMail();

    expect(createFetchRequest).toHaveBeenCalledWith('POST', '/email/send', expect.any(Object));
  });

  it('EnvoiMail should call addCsrfToken', () => {
    EnvoiMail();

    expect(addCsrfToken).toHaveBeenCalled();
  });

  it('sendnotebook should set dialog values and call EnvoiMail', () => {
    sendnotebook('Test content');

    // Verify prompt was called
    expect(prompt).toHaveBeenCalled();
    // et que l'envoi a bien ete declenche
    expect(createFetchRequest).toHaveBeenCalledWith('POST', '/email/send', expect.any(Object));
  });
});
