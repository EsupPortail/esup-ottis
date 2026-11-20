/**
 * Configuration ESLint 10 (flat config) pour le projet OTTIS
 * Portage de .eslintrc.json : airbnb-base via FlatCompat + security + no-secrets + prettier
 */

import js from '@eslint/js';
import { FlatCompat } from '@eslint/eslintrc';
import security from 'eslint-plugin-security';
import noSecrets from 'eslint-plugin-no-secrets';
import prettier from 'eslint-config-prettier';
import globals from 'globals';

const compat = new FlatCompat({ resolvePluginsRelativeTo: import.meta.dirname });

export default [
  {
    ignores: ['**/node_modules/**', '**/vendor/**', '**/tmp/**', '**/dist/**', '**/build/**'],
  },
  js.configs.recommended,
  ...compat.extends('airbnb-base'),
  security.configs.recommended,
  {
    plugins: {
      'no-secrets': noSecrets,
    },
    rules: {
      'no-secrets/no-secrets': 'warn',
    },
  },
  prettier,
  {
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        ...globals.browser,
        DOMPurify: 'readonly',
        pdfjsLib: 'readonly',
        Swiper: 'readonly',
        PptxGenJS: 'readonly',
        currentconfig: 'writable',
      },
    },
    settings: {
      'import/core-modules': ['vitest/config'],
    },
    rules: {
      'no-var': 'error',
      'prefer-const': 'error',
      'prefer-arrow-callback': 'error',
      'func-style': 'off',
      'func-names': 'off',
      camelcase: 'warn',
      'no-plusplus': 'off',
      'no-underscore-dangle': ['error', { allow: ['_paq'] }],
      'no-console': 'warn',
      'no-unused-vars': ['error', { argsIgnorePattern: '^_', caughtErrors: 'none' }],
      eqeqeq: ['error', 'smart'],
      'max-len': ['error', { code: 120 }],
      quotes: ['error', 'single'],
      semi: ['error', 'always'],
      'comma-dangle': [
        'error',
        {
          arrays: 'always-multiline',
          objects: 'always-multiline',
          imports: 'always-multiline',
          exports: 'always-multiline',
          functions: 'never',
        },
      ],
      indent: ['error', 2],
      'linebreak-style': ['error', 'unix'],
      'no-trailing-spaces': 'error',
      'object-curly-newline': ['error', { multiline: true, consistent: true }],
      'import/prefer-default-export': 'off',
      'import/extensions': ['error', 'always', { ignorePackages: true }],
      'import/no-mutable-exports': 'error',
      'import/no-cycle': 'error',
      'no-param-reassign': 'warn',
      'no-use-before-define': ['error', { functions: false, classes: true, variables: true }],
      'security/detect-object-injection': 'warn',
    },
  },
  {
    files: ['tests/no-jquery-validation.js'],
    languageOptions: {
      globals: {
        get: 'readonly',
        makeDraggable: 'readonly',
      },
    },
    rules: {
      'no-console': 'off',
    },
  },
  {
    files: ['**/*.test.js', '**/*.spec.js', 'tests/setup.js'],
    rules: {
      'no-console': 'off',
      'no-unused-expressions': 'off',
      'import/no-extraneous-dependencies': ['error', { devDependencies: true }],
    },
  },
  {
    files: ['eslint.config.js'],
    rules: {
      'import/no-extraneous-dependencies': 'off',
    },
  },
  {
    files: ['lib/utils/*.js'],
    rules: {
      'import/prefer-default-export': 'off',
    },
  },
];
