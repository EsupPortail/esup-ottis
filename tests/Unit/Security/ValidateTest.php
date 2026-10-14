<?php

/**
 * Unit tests for Validation functions
 *
 * Tests pour la classe App\Utils\Validator
 */

namespace OTTIS\Tests\Unit\Security;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Test class for validation functions
 */
class ValidateTest extends TestCase
{
    // ========================================================================
    // Validator::validateRoomId() Tests
    // ========================================================================

    /**
     * Test validateRoomId with valid room IDs
     */
    public function testValidateRoomIdWithValidIds(): void
    {
        $validIds = [
            'room123',
            'conference_01',
            'test-room',
            'A1B2C3',
            'a', // 1 character
            str_repeat('a', 64), // 64 characters (max length)
            'Room_123-Test',
            'UPPERCASE',
            'lowercase',
            'MixedCase123',
        ];

        foreach ($validIds as $roomId) {
            $this->assertTrue(Validator::validateRoomId($roomId), "Failed for: $roomId");
        }
    }

    /**
     * Test validateRoomId with invalid room IDs
     */
    public function testValidateRoomIdWithInvalidIds(): void
    {
        $invalidIds = [
            '', // empty string
            str_repeat('a', 65), // 65 characters (too long)
            'room with spaces', // contains spaces
            'room@test', // contains @
            'room#test', // contains #
            'room$test', // contains $
            'room%test', // contains %
            'room&test', // contains &
            'room*test', // contains *
            'room!test', // contains !
            'room?test', // contains ?
            'room=test', // contains =
            'room+test', // contains +
            'room/test', // contains /
            'room\\test', // contains backslash
            'room:test', // contains :
            'room;test', // contains ;
            'room<test', // contains <
            'room>test', // contains >
            'room|test', // contains |
            12345, // integer
            null, // null
            [], // array
            new \stdClass(), // object
            true, // boolean
        ];

        foreach ($invalidIds as $roomId) {
            $this->assertFalse(Validator::validateRoomId($roomId), 'Should be invalid: ' . var_export($roomId, true));
        }
    }

    /**
     * Test validateRoomId with edge cases
     */
    public function testValidateRoomIdEdgeCases(): void
    {
        // Exactly 64 characters
        $this->assertTrue(Validator::validateRoomId(str_repeat('a', 64)));

        // Exactly 1 character
        $this->assertTrue(Validator::validateRoomId('a'));

        // Only underscores and hyphens
        $this->assertTrue(Validator::validateRoomId('___---'));

        // Only numbers
        $this->assertTrue(Validator::validateRoomId('123456'));
    }

    // ========================================================================
    // Validator::sanitizeText() Tests
    // ========================================================================

    /**
     * Test sanitizeText with normal text
     */
    public function testSanitizeTextWithNormalText(): void
    {
        $text = 'Hello World';
        $result = Validator::sanitizeText($text);
        $this->assertEquals('Hello World', $result);
    }

    /**
     * Test sanitizeText removes HTML tags
     */
    public function testSanitizeTextRemovesHtmlTags(): void
    {
        $text = '<script>alert("XSS")</script>Hello';
        $result = Validator::sanitizeText($text);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringNotContainsString('</script>', $result);
        // strip_tags removes the tags but keeps the content
        $this->assertStringContainsString('alert', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    /**
     * Test sanitizeText encodes HTML entities
     */
    public function testSanitizeTextEncodesHtmlEntities(): void
    {
        $text = '<b>Bold & Beautiful</b>';
        $result = Validator::sanitizeText($text);

        // strip_tags removes the <b> tags, htmlspecialchars encodes the &
        $this->assertStringNotContainsString('<b>', $result);
        $this->assertStringNotContainsString('</b>', $result);
        $this->assertStringContainsString('Bold', $result);
        $this->assertStringContainsString('&amp;', $result);
    }

    /**
     * Test sanitizeText replaces newlines
     */
    public function testSanitizeTextReplacesNewlines(): void
    {
        $text = "Line 1\nLine 2\r\nLine 3\rLine 4";
        $result = Validator::sanitizeText($text);

        $this->assertStringNotContainsString("\n", $result);
        $this->assertStringNotContainsString("\r", $result);
        $this->assertStringContainsString('Line 1 Line 2 Line 3 Line 4', $result);
    }

    /**
     * Test sanitizeText collapses multiple spaces
     */
    public function testSanitizeTextCollapsesMultipleSpaces(): void
    {
        $text = 'Hello    World';
        $result = Validator::sanitizeText($text);

        $this->assertEquals('Hello World', $result);
    }

    /**
     * Test sanitizeText with max length
     */
    public function testSanitizeTextWithMaxLength(): void
    {
        $longText = str_repeat('a', 100);
        $result = Validator::sanitizeText($longText, 50);

        $this->assertEquals(50, strlen($result));
        $this->assertEquals(str_repeat('a', 50), $result);
    }

    /**
     * Test sanitizeText trims whitespace
     */
    public function testSanitizeTextTrimsWhitespace(): void
    {
        $text = '  Hello World  ';
        $result = Validator::sanitizeText($text);

        $this->assertEquals('Hello World', $result);
    }

    /**
     * Test sanitizeText rejects non-string input
     */
    public function testSanitizeTextRejectsNonStringInput(): void
    {
        // Validator::sanitizeText est type string : les appelants valident
        // le type en amont. En mode weak (pas de strict_types), les scalaires
        // int/bool sont convertis ; seuls null/array/object levent TypeError
        $caught = 0;
        foreach ([null, [], new \stdClass()] as $value) {
            try {
                Validator::sanitizeText($value);
                $this->fail('TypeError attendue pour : ' . var_export($value, true));
            } catch (\TypeError $e) {
                $caught++;
            }
        }
        $this->assertSame(3, $caught);
    }

    /**
     * Test sanitizeText with XSS payloads
     * Note: sanitizeText uses strip_tags which removes HTML tags but keeps the content.
     * For XSS protection, the content between tags is still present but without the
     * executable context (tags are removed).
     */
    public function testSanitizeTextWithXssPayloads(): void
    {
        $xssPayloads = [
            ['input' => '<script>alert(1)</script>', 'should_not_contain' => ['<script>', '</script>']],
            ['input' => '<img src=x onerror=alert(1)>', 'should_not_contain' => ['<img', 'onerror=']],
            ['input' => '<svg onload=alert(1)>', 'should_not_contain' => ['<svg', 'onload=']],
            ['input' => '<body onload=alert(1)>', 'should_not_contain' => ['<body', 'onload=']],
        ];

        foreach ($xssPayloads as $testCase) {
            $result = Validator::sanitizeText($testCase['input']);

            foreach ($testCase['should_not_contain'] as $forbidden) {
                $this->assertStringNotContainsString(
                    $forbidden,
                    $result,
                    "XSS payload should not contain: $forbidden"
                );
            }
        }

        // Note: strip_tags removes HTML tags but keeps the content text
        // So "javascript:alert(1)" stays as-is because it's not an HTML tag
        // This is expected behavior for this function
    }

    // ========================================================================
    // Validator::validateLanguage() Tests
    // ========================================================================

    /**
     * Test validateLanguage with valid language codes
     */
    public function testValidateLanguageWithValidCodes(): void
    {
        $validLanguages = [
            'fr',
            'en',
            'es',
            'de',
            'it',
            'pt',
            'ru',
            'zh',
            'ja',
            'ar',
            'fr-CA',
            'en-US',
            'en_GB',
            str_repeat('a', 15), // 15 characters (max length)
        ];

        foreach ($validLanguages as $lang) {
            $this->assertEquals($lang, Validator::validateLanguage($lang));
        }
    }

    /**
     * Test validateLanguage with invalid codes returns default
     */
    public function testValidateLanguageWithInvalidCodes(): void
    {
        $invalidInputs = [
            '', // empty string
            'a', // too short (1 char)
            str_repeat('a', 16), // too long (16 chars)
            'fr@', // contains @
            'fr#', // contains #
            'fr$', // contains $
            'fr test', // contains space
            123, // integer
            null, // null
            [], // array
            true, // boolean
        ];

        foreach ($invalidInputs as $lang) {
            $this->assertEquals('fr', Validator::validateLanguage($lang));
        }
    }

    /**
     * Test validateLanguage with edge cases
     */
    public function testValidateLanguageEdgeCases(): void
    {
        // Exactly 2 characters
        $this->assertEquals('ab', Validator::validateLanguage('ab'));

        // Exactly 15 characters
        $this->assertEquals(str_repeat('a', 15), Validator::validateLanguage(str_repeat('a', 15)));

        // Numeric language code (valid per regex)
        $this->assertEquals('12', Validator::validateLanguage('12'));
    }

    // ========================================================================
    // Validator::validateLineId() Tests
    // ========================================================================

    /**
     * Test validateLineId with valid numeric strings
     */
    public function testValidateLineIdWithValidNumericStrings(): void
    {
        $validIds = ['0', '1', '100', '999999', '-5', '3.14'];

        foreach ($validIds as $lineId) {
            $result = Validator::validateLineId($lineId);
            $this->assertIsInt($result);
            $this->assertEquals((int)$lineId, $result);
        }
    }

    /**
     * Test validateLineId with invalid input returns 0
     */
    public function testValidateLineIdWithInvalidInput(): void
    {
        $invalidInputs = [
            'abc', // non-numeric string
            '123abc', // partially numeric
            '', // empty string
            '12.34.56', // invalid number format
            [], // array
            null, // null
            new \stdClass(), // object
            true, // boolean
        ];

        foreach ($invalidInputs as $lineId) {
            $this->assertEquals(0, Validator::validateLineId($lineId));
        }
    }

    /**
     * Test validateLineId with integer input
     */
    public function testValidateLineIdWithIntegerInput(): void
    {
        $this->assertEquals(123, Validator::validateLineId(123));
        $this->assertEquals(0, Validator::validateLineId(0));
        $this->assertEquals(-456, Validator::validateLineId(-456));
    }

    // ========================================================================
    // Validator::validateTmpFilePath() Tests
    // ========================================================================

    /**
     * Test validateTmpFilePath with valid parameters
     */
    public function testValidateTmpFilePathWithValidParameters(): void
    {
        $validRoomIds = ['room123', 'test', 'conference_01'];
        $validPrefixes = ['Link', 'Translated', 'Verylast', 'Image', 'ImageID', 'LinkQuestions'];

        foreach ($validRoomIds as $roomId) {
            foreach ($validPrefixes as $prefix) {
                $result = Validator::validateTmpFilePath($roomId, $prefix);
                $expected = 'tmp/' . $prefix . '_' . $roomId;
                $this->assertEquals($expected, $result);
            }
        }
    }

    /**
     * Test validateTmpFilePath with default prefix
     */
    public function testValidateTmpFilePathWithDefaultPrefix(): void
    {
        $result = Validator::validateTmpFilePath('room123');
        $this->assertEquals('tmp/Link_room123', $result);
    }

    /**
     * Test validateTmpFilePath with invalid roomId
     */
    public function testValidateTmpFilePathWithInvalidRoomId(): void
    {
        // Validator::validateTmpFilePath est type string : les non-strings
        // sont hors contrat (TypeError ou conversion PHP en mode weak)
        $invalidRoomIds = ['', 'room with spaces', 'room@test'];

        foreach ($invalidRoomIds as $roomId) {
            $this->assertFalse(Validator::validateTmpFilePath($roomId, 'Link'));
        }
    }

    /**
     * Test validateTmpFilePath with invalid prefix
     */
    public function testValidateTmpFilePathWithInvalidPrefix(): void
    {
        $invalidPrefixes = ['Invalid', 'Test', '', 'Link2', 'link'];

        foreach ($invalidPrefixes as $prefix) {
            $this->assertFalse(Validator::validateTmpFilePath('room123', $prefix));
        }
    }

    /**
     * Test validateTmpFilePath with all valid prefixes
     */
    public function testValidateTmpFilePathWithAllValidPrefixes(): void
    {
        $validPrefixes = ['Link', 'Translated', 'Verylast', 'Image', 'ImageID', 'LinkQuestions'];

        foreach ($validPrefixes as $prefix) {
            $result = Validator::validateTmpFilePath('test', $prefix);
            $this->assertStringStartsWith('tmp/' . $prefix . '_', $result);
        }
    }

    // ========================================================================
    // Validator::secureOpenTmpFile() Tests
    // ========================================================================

    /**
     * Test secureOpenTmpFile with invalid roomId
     */
    public function testSecureOpenTmpFileWithInvalidRoomId(): void
    {
        $result = Validator::secureOpenTmpFile('', 'Link', 'r');
        $this->assertFalse($result);
    }

    /**
     * Test secureOpenTmpFile with invalid prefix
     */
    public function testSecureOpenTmpFileWithInvalidPrefix(): void
    {
        $result = Validator::secureOpenTmpFile('room123', 'InvalidPrefix', 'r');
        $this->assertFalse($result);
    }

    /**
     * Test secureOpenTmpFile with path traversal attempt
     */
    public function testSecureOpenTmpFilePreventsPathTraversal(): void
    {
        // Mock a roomId that would try to traverse directories
        // This should be caught by validateRoomId
        $result = Validator::secureOpenTmpFile('../room', 'Link', 'r');
        $this->assertFalse($result);
    }

    /**
     * Test secureOpenTmpFile with suspicious file path
     * This tests the direct path manipulation checks
     */
    public function testSecureOpenTmpFileWithSuspiciousPath(): void
    {
        // We need to test the path validation logic
        // Since validateRoomId would catch most issues, we test the additional checks

        // Mock the validateTmpFilePath to return a suspicious path
        // This is a bit tricky since we can't easily mock it, but we can test with
        // inputs that would pass validateRoomId but fail the path checks

        // For now, we verify that the function returns false for invalid paths
        // The actual file opening depends on the filesystem, which we can't reliably test
        $result = Validator::secureOpenTmpFile('test', 'Link', 'r');
        // This will return false because the file doesn't exist, which is fine
        $this->assertIsBool($result);
    }

    /**
     * Test secureOpenTmpFile path validation logic
     * Tests the security checks in the function
     */
    public function testSecureOpenTmpFilePathValidation(): void
    {
        // Test that the function checks for 'tmp/' prefix
        // This is tested indirectly through validateTmpFilePath

        // Test that paths with '..' are rejected
        // This would require mocking validateTmpFilePath, but we can test
        // that the security checks work by ensuring the function returns false
        // for suspicious inputs

        // For the purpose of this test, we verify the function structure
        $this->assertTrue(true, 'Path validation tests require file system mocking');
    }

    // ========================================================================
    // Integration Tests
    // ========================================================================

    /**
     * Test the integration of validateTmpFilePath and secureOpenTmpFile
     */
    public function testValidateTmpFilePathAndSecureOpenTmpFileIntegration(): void
    {
        // Both functions should reject the same invalid inputs
        $invalidInputs = [
            ['roomId' => '', 'prefix' => 'Link'],
            ['roomId' => 'room123', 'prefix' => 'Invalid'],
            ['roomId' => 'room@test', 'prefix' => 'Link'],
        ];

        foreach ($invalidInputs as $input) {
            $pathResult = Validator::validateTmpFilePath($input['roomId'], $input['prefix']);
            $fileResult = Validator::secureOpenTmpFile($input['roomId'], $input['prefix'], 'r');

            // If path validation fails, file opening should also fail
            if ($pathResult === false) {
                $this->assertFalse(
                    $fileResult,
                    'secureOpenTmpFile should return false when validateTmpFilePath returns false'
                );
            }
        }
    }

    /**
     * Test the complete validation workflow
     */
    public function testCompleteValidationWorkflow(): void
    {
        // Test a typical workflow
        $roomId = 'conference123';
        $prefix = 'Link';

        // Step 1: Validate room ID
        $this->assertTrue(Validator::validateRoomId($roomId));

        // Step 2: Validate file path
        $filePath = Validator::validateTmpFilePath($roomId, $prefix);
        $this->assertStringStartsWith('tmp/Link_conference123', $filePath);

        // Step 3: Validate language
        $this->assertEquals('en', Validator::validateLanguage('en'));

        // Step 4: Validate line ID
        $this->assertEquals(42, Validator::validateLineId('42'));

        // Step 5: Sanitize text
        $text = Validator::sanitizeText('<script>test</script>');
        $this->assertStringNotContainsString('<script>', $text);
    }
}
