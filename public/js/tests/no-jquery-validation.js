/**
 * jQuery Removal Validation Script
 * ================================
 *
 * This script validates that jQuery has been successfully removed from the project
 * and that all utility functions are properly loaded and functional.
 *
 * Usage:
 * 1. Include this script AFTER all other JavaScript files in your HTML
 * 2. Open browser console (F12)
 * 3. Check for "[jQuery Removal Validation]" messages
 * 4. Look for "✅ PASS" or "❌ FAIL" results
 */

// eslint-disable-next-line wrap-iife -- IIFE classique (script hors module), conflit de formatage Prettier/Airbnb
(function () {
  // Test results storage
  const results = {
    passed: 0,
    failed: 0,
    total: 0,
    errors: [],
  };

  // Helper function to log results
  function logResult(testName, passed, message = '') {
    results.total++;
    if (passed) {
      results.passed++;
      console.log(`[jQuery Removal Validation] ✅ PASS: ${testName}`);
    } else {
      results.failed++;
      results.errors.push({ test: testName, message });
      console.warn(`[jQuery Removal Validation] ❌ FAIL: ${testName}`);
      if (message) {
        console.warn(`   Details: ${message}`);
      }
    }
  }

  // Helper to check if a function exists
  function functionExists(name, context = window) {
    return typeof context[name] === 'function';
  }

  console.log(
    '%c===== jQuery Removal Validation =====',
    'color: #4CAF50; font-weight: bold; font-size: 16px;'
  );
  console.log('[jQuery Removal Validation] Starting validation checks...');

  // ============================================
  // TEST 1: Check that jQuery global is NOT defined
  // ============================================
  try {
    if (typeof jQuery === 'undefined' && typeof $ === 'undefined') {
      logResult('jQuery global variables not defined', true);
    } else {
      logResult(
        'jQuery global variables not defined',
        false,
        `jQuery: ${typeof jQuery}, $: ${typeof $}`
      );
    }
  } catch (e) {
    logResult('jQuery global variables check', false, e.message);
  }

  // ============================================
  // TEST 2: Check that utility functions ARE defined
  // ============================================
  const utilityFunctions = [
    { name: 'get', context: window, description: 'DOM selector utility' },
    { name: 'query', context: window, description: 'DOM querySelector utility' },
    { name: 'queryAll', context: window, description: 'DOM querySelectorAll utility' },
    { name: 'on', context: window, description: 'Event listener utility' },
    { name: 'ready', context: window, description: 'DOM ready utility' },
    { name: 'makeDraggable', context: window, description: 'Draggable utility' },
    { name: 'openDialog', context: window, description: 'Dialog utility' },
    { name: 'closeDialog', context: window, description: 'Dialog utility' },
    { name: '_', context: window, description: 'Legacy DOM utility' },
  ];

  utilityFunctions.forEach(({ name, context, description }) => {
    try {
      logResult(
        `Utility function '${name}' exists (${description})`,
        functionExists(name, context)
      );
    } catch (e) {
      logResult(`Utility function '${name}' check`, false, e.message);
    }
  });

  // ============================================
  // TEST 3: Check that DOM utilities work
  // ============================================
  try {
    if (functionExists('get')) {
      // Test get() function
      const testElement = document.createElement('div');
      testElement.id = 'jquery-validation-test';
      document.body.appendChild(testElement);

      const result = get('jquery-validation-test');
      if (result && result.id === 'jquery-validation-test') {
        logResult('get() function works correctly', true);
      } else {
        logResult(
          'get() function works correctly',
          false,
          `Expected element with id 'jquery-validation-test', got: ${result}`
        );
      }

      document.body.removeChild(testElement);
    }
  } catch (e) {
    logResult('get() function test', false, e.message);
  }

  // ============================================
  // TEST 4: Check that makeDraggable is functional
  // ============================================
  try {
    if (functionExists('makeDraggable')) {
      const testDiv = document.createElement('div');
      testDiv.id = 'draggable-test';
      testDiv.style.position = 'absolute';
      testDiv.style.width = '100px';
      testDiv.style.height = '100px';
      testDiv.style.backgroundColor = 'transparent';
      document.body.appendChild(testDiv);

      // This should not throw an error
      makeDraggable(testDiv);
      logResult('makeDraggable() function works without errors', true);

      document.body.removeChild(testDiv);
    }
  } catch (e) {
    logResult('makeDraggable() function test', false, e.message);
  }

  // ============================================
  // TEST 5: Check for common jQuery patterns in loaded scripts
  // ============================================
  try {
    const scripts = document.querySelectorAll('script[src]');
    let foundJQueryRef = false;

    scripts.forEach((script) => {
      const src = script.src.toLowerCase();
      if (src.includes('jquery.min.js') || src.includes('jquery.js') || src.includes('jquery-ui')) {
        foundJQueryRef = true;
        logResult('No jQuery scripts loaded', false, `Found jQuery reference: ${script.src}`);
      }
    });

    if (!foundJQueryRef) {
      logResult('No jQuery scripts loaded', true);
    }
  } catch (e) {
    logResult('jQuery scripts check', false, e.message);
  }

  // ============================================
  // TEST 6: Check CSS for jQuery UI classes
  // ============================================
  try {
    const stylesheets = document.styleSheets;
    let foundJQueryUIClass = false;

    for (let i = 0; i < stylesheets.length; i++) {
      const sheet = stylesheets[i];
      try {
        const rules = sheet.cssRules || sheet.rules;
        for (let j = 0; j < rules.length; j++) {
          const rule = rules[j];
          if (
            rule.selectorText &&
            (rule.selectorText.includes('.jqueryselect') || rule.selectorText.includes('.ui-'))
          ) {
            foundJQueryUIClass = true;
            logResult(
              'No jQuery UI CSS classes found',
              false,
              `Found jQuery UI class in: ${sheet.href || 'inline styles'}`
            );
            break;
          }
        }
      } catch (e) {
        // Cross-origin stylesheets may throw
        // eslint-disable-next-line no-continue -- passage a la feuille de style suivante
        continue;
      }
    }

    if (!foundJQueryUIClass) {
      logResult('No jQuery UI CSS classes found', true);
    }
  } catch (e) {
    logResult('jQuery UI CSS check', false, e.message);
  }

  // ============================================
  // SUMMARY
  // ============================================
  setTimeout(() => {
    console.log(
      '%c===== Validation Summary =====',
      'color: #4CAF50; font-weight: bold; font-size: 16px;'
    );
    console.log(`[jQuery Removal Validation] Total tests: ${results.total}`);
    console.log(`[jQuery Removal Validation] Passed: ${results.passed}`);
    console.log(`[jQuery Removal Validation] Failed: ${results.failed}`);

    if (results.failed > 0) {
      // eslint-disable-next-line max-len
      console.warn(
        '%c[jQuery Removal Validation] ⚠️  Some tests failed. Please review the errors above.',
        'color: #FF9800;'
      );
    } else {
      // eslint-disable-next-line max-len
      console.log(
        '%c[jQuery Removal Validation] ✅ All validation tests passed! jQuery has been successfully removed.',
        'color: #4CAF50; font-weight: bold;'
      );
    }

    console.log('========================================');
  }, 100);
})();
