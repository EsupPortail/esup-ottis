/**
 * Tests unitaires pour lib/network/retry.js
 * Politique anti-cascade : classification des erreurs transport, bornes du
 * backoff, relance bornee.
 */
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import {
  isTransientTransportError,
  backoffDelayMs,
  attemptBoundedRetry,
} from '../../../lib/network/retry.js';

describe('isTransientTransportError', () => {
  it('traite 5xx et 429 comme passagers', () => {
    expect(isTransientTransportError({ status: 503, statusText: 'Service Unavailable' })).toBe(
      true
    );
    expect(isTransientTransportError({ status: 502, statusText: '' })).toBe(true);
    expect(isTransientTransportError({ status: 429, statusText: 'Too Many Requests' })).toBe(true);
  });

  it('traite les 4xx (hors 429) comme definitifs', () => {
    expect(isTransientTransportError({ status: 403, statusText: 'Forbidden' })).toBe(false);
    expect(isTransientTransportError({ status: 400, statusText: 'Bad Request' })).toBe(false);
  });

  it('distingue le timeout de polling et son artefact des vraies erreurs reseau', () => {
    expect(isTransientTransportError({ status: 0, statusText: 'Timeout' })).toBe(false);
    // createFetchRequest appelle 'Timeout' PUIS 'Request aborted' sur un seul
    // depassement : le second doit etre ignore comme le premier
    expect(isTransientTransportError({ status: 0, statusText: 'Request aborted' })).toBe(false);
    expect(isTransientTransportError({ status: 0, statusText: 'Network error' })).toBe(true);
  });

  it('repond faux sur reponse absente ou mal formee', () => {
    expect(isTransientTransportError(null)).toBe(false);
    expect(isTransientTransportError(undefined)).toBe(false);
    expect(isTransientTransportError({})).toBe(true); // status 0 sans Timeout
  });
});

describe('backoffDelayMs', () => {
  it('reste dans [75%, 125%] du delai exponentiel attendu', () => {
    expect(backoffDelayMs(1)).toBeGreaterThanOrEqual(750);
    expect(backoffDelayMs(1)).toBeLessThanOrEqual(1250);
    expect(backoffDelayMs(2)).toBeGreaterThanOrEqual(1500);
    expect(backoffDelayMs(2)).toBeLessThanOrEqual(2500);
  });

  it('borne le delai a MAX_DELAY_MS (8000 ms)', () => {
    // Rang eleve : 1000 * 2**7 depasserait le plafond
    expect(backoffDelayMs(8)).toBeLessThanOrEqual(Math.round(8000 * 1.25));
    expect(backoffDelayMs(8)).toBeGreaterThanOrEqual(6000);
  });
});

describe('attemptBoundedRetry', () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it('renonce immediatement sur erreur definitive (pas de reessai)', () => {
    const send = vi.fn();
    const onGiveUp = vi.fn();
    attemptBoundedRetry(send, { status: 403, statusText: 'Forbidden' }, 1, onGiveUp);
    expect(send).not.toHaveBeenCalled();
    expect(onGiveUp).toHaveBeenCalledTimes(1);
  });

  it('relance apres backoff sur erreur passagere', () => {
    const send = vi.fn();
    const onGiveUp = vi.fn();
    attemptBoundedRetry(send, { status: 503, statusText: '' }, 1, onGiveUp);
    expect(send).not.toHaveBeenCalled();
    expect(onGiveUp).not.toHaveBeenCalled();
    vi.advanceTimersByTime(1500);
    expect(send).toHaveBeenCalledTimes(1);
    expect(onGiveUp).not.toHaveBeenCalled();
  });

  it('renonce quand le nombre maximal d essais est atteint', () => {
    const send = vi.fn();
    const onGiveUp = vi.fn();
    attemptBoundedRetry(send, { status: 503, statusText: '' }, 3, onGiveUp);
    vi.advanceTimersByTime(20000);
    expect(send).not.toHaveBeenCalled();
    expect(onGiveUp).toHaveBeenCalledTimes(1);
  });
});
