/**
 * Configuration Vitest pour les tests JavaScript du projet OTTIS
 * Environnement : Node.js + jsdom pour simuler le navigateur
 * Modules ES natifs supportés
 */

import { defineConfig } from 'vitest/config';
import path from 'path';

export default defineConfig({
  test: {
    // Environnement de test : jsdom pour simuler le DOM du navigateur
    environment: 'jsdom',

    // Dossiers contenant les tests
    include: ['**/*.{test,spec}.js', '**/*.{test,spec}.mjs'],

    // Cache directory pour Vitest (pour éviter les problèmes de permissions)
    cache: {
      dir: '/tmp/vitest-cache',
    },

    // Exclure les fichiers de configuration et node_modules
    exclude: ['node_modules/**', '**/vendor/**', '**/dist/**', '**/build/**'],

    // Configuration de la couverture de code (optionnel, à activer plus tard)
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      include: ['**/utils/**/*.js', '**/lib/**/*.js'],
      exclude: ['**/*.{test,spec}.js', '**/tests/**', '**/node_modules/**'],
    },

    // Global setup/teardown (optionnel)
    setupFiles: ['./tests/setup.js'],

    // Timeout par défaut pour les tests (en ms)
    testTimeout: 10000,

    // Activer le watch mode par défaut
    watch: false,

    // Activer le support de CommonJS pour les fichiers legacy
    transformMode: {
      web: [/\.[cm]?js$/],
    },
  },

  // Résolution des modules
  resolve: {
    alias: {
      // Alias pour importer facilement depuis la racine js/
      '@': path.resolve(__dirname, '.'),
      '@lib': path.resolve(__dirname, './lib'),
      '@utils': path.resolve(__dirname, './utils'),
    },
  },
});
