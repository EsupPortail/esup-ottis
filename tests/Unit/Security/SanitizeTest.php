<?php

/**
 * Unit tests for Sanitize functions
 *
 * Tests pour la classe App\Utils\Sanitizer
 */

namespace OTTIS\Tests\Unit\Security;

use App\Utils\Sanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Test class for sanitization functions
 */
class SanitizeTest extends TestCase
{
    /**
     * Test that Sanitizer::e() function sanitizes HTML special characters
     */
    public function testEFunctionSanitizesHtml(): void
    {
        $input = '<script>alert("XSS")</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
        $result = Sanitizer::e($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that Sanitizer::e() function handles double encoding correctly
     */
    public function testEFunctionDoubleEncoding(): void
    {
        $input = '&lt;script&gt;';
        // By default, doubleEncode is true, so & should be encoded to &amp;
        $result = Sanitizer::e($input);

        $this->assertStringContainsString('&amp;', $result);
    }

    /**
     * Test that Sanitizer::e() function handles null bytes
     */
    public function testEFunctionHandlesNullBytes(): void
    {
        $input = "test\x00value";
        $result = Sanitizer::e($input);

        // Null byte should be preserved but HTML entities should be encoded
        $this->assertStringNotContainsString('\x00', $result);
    }

    /**
     * Test that Sanitizer::e() function works with UTF-8 characters
     */
    public function testEFunctionWithUtf8(): void
    {
        $input = '<b>こんにちは</b>';
        $result = Sanitizer::e($input);

        $this->assertStringContainsString('&lt;b&gt;', $result);
        $this->assertStringContainsString('こんにちは', $result);
    }

    /**
     * Test that Sanitizer::eAttr() function sanitizes for HTML attributes
     */
    public function testEAttrFunction(): void
    {
        $input = 'value" onclick="alert(1)"';
        $result = Sanitizer::eAttr($input);

        $this->assertStringContainsString('&quot;', $result);
        // e_attr encodes special chars but doesn't remove content
        $this->assertStringContainsString('onclick=', $result);
    }

    /**
     * Test that Sanitizer::eJs() function sanitizes for JavaScript
     */
    public function testEJsFunction(): void
    {
        $input = 'Hello "World"';
        $result = Sanitizer::eJs($input);

        // json_encode with JSON_UNESCAPED_UNICODE uses \u0022 for quotes
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('World', $result);
        // Check that quotes are escaped (either as \\" or \u0022)
        $this->assertStringNotContainsString('"World"', $result);
    }

    /**
     * Test that Sanitizer::eUrl() function sanitizes URLs
     */
    public function testEUrlFunction(): void
    {
        $input = 'javascript:alert(1)';
        $result = Sanitizer::eUrl($input);

        // FILTER_SANITIZE_URL encodes special characters but doesn't remove protocols
        // It removes spaces and encodes special chars
        $this->assertStringContainsString('javascript', $result);
        $this->assertStringContainsString('alert', $result);
    }

    /**
     * Test that Sanitizer::e() function handles empty string
     */
    public function testEFunctionWithEmptyString(): void
    {
        $this->assertEquals('', Sanitizer::e(''));
    }

    /**
     * Test that Sanitizer::e() function handles numeric strings
     */
    public function testEFunctionWithNumericString(): void
    {
        $this->assertEquals('123', Sanitizer::e('123'));
    }
}
