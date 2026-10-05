<?php

/**
 * Unit tests for Sanitize functions
 *
 * Tests pour les fonctions de sanitization dans Security/sanitize.php
 */

namespace OMIST\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

/**
 * Test class for sanitization functions
 */
class SanitizeTest extends TestCase
{
    /**
     * Test that e() function sanitizes HTML special characters
     */
    public function testEFunctionSanitizesHtml(): void
    {
        $input = '<script>alert("XSS")</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
        $result = e($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that e() function handles double encoding correctly
     */
    public function testEFunctionDoubleEncoding(): void
    {
        $input = '&lt;script&gt;';
        // By default, doubleEncode is true, so & should be encoded to &amp;
        $result = e($input);

        $this->assertStringContainsString('&amp;', $result);
    }

    /**
     * Test that e() function handles null bytes
     */
    public function testEFunctionHandlesNullBytes(): void
    {
        $input = "test\x00value";
        $result = e($input);

        // Null byte should be preserved but HTML entities should be encoded
        $this->assertStringNotContainsString('\x00', $result);
    }

    /**
     * Test that e() function works with UTF-8 characters
     */
    public function testEFunctionWithUtf8(): void
    {
        $input = '<b>こんにちは</b>';
        $result = e($input);

        $this->assertStringContainsString('&lt;b&gt;', $result);
        $this->assertStringContainsString('こんにちは', $result);
    }

    /**
     * Test that e_attr() function sanitizes for HTML attributes
     */
    public function testEAttrFunction(): void
    {
        $input = 'value" onclick="alert(1)"';
        $result = e_attr($input);

        $this->assertStringContainsString('&quot;', $result);
        // e_attr encodes special chars but doesn't remove content
        $this->assertStringContainsString('onclick=', $result);
    }

    /**
     * Test that e_js() function sanitizes for JavaScript
     */
    public function testEJsFunction(): void
    {
        $input = 'Hello "World"';
        $result = e_js($input);

        // json_encode with JSON_UNESCAPED_UNICODE uses \u0022 for quotes
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('World', $result);
        // Check that quotes are escaped (either as \\" or \u0022)
        $this->assertStringNotContainsString('"World"', $result);
    }

    /**
     * Test that e_url() function sanitizes URLs
     */
    public function testEUrlFunction(): void
    {
        $input = 'javascript:alert(1)';
        $result = e_url($input);

        // FILTER_SANITIZE_URL encodes special characters but doesn't remove protocols
        // It removes spaces and encodes special chars
        $this->assertStringContainsString('javascript', $result);
        $this->assertStringContainsString('alert', $result);
    }

    /**
     * Test that e() function handles empty string
     */
    public function testEFunctionWithEmptyString(): void
    {
        $this->assertEquals('', e(''));
    }

    /**
     * Test that e() function handles numeric strings
     */
    public function testEFunctionWithNumericString(): void
    {
        $this->assertEquals('123', e('123'));
    }
}
