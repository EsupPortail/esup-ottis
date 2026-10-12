/**
 * Fichier de configuration globale pour les tests Vitest
 * Ce fichier est exécuté avant chaque test
 */

import { beforeAll, afterAll, afterEach, vi } from 'vitest';

// Mock des fonctions globales si nécessaire
beforeAll(() => {
  // Mock de console.log pour éviter les logs pendant les tests
  globalThis.console.log = vi.fn();
  globalThis.console.warn = vi.fn();
  globalThis.console.error = vi.fn();
});

// Nettoyage après chaque test
afterEach(() => {
  vi.clearAllMocks();
});

// Nettoyage final
afterAll(() => {
  vi.restoreAllMocks();
});

// Mock des dépendances externes courantes dans le projet
globalThis.DOMPurify = {
  sanitize: vi.fn((html) => html),
};

// Mock des variables globales utilisées dans le projet
globalThis.ottisConfig = {
  apiUrl: 'http://localhost:8080',
  debug: false,
};
