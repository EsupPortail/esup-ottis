<?php

/**
 * Tests unitaires pour App\Service\ChatService
 *
 * Messages du tchat (LinkQuestions_) et rate limiting (ratelimit_) crees
 * dans tmp/ sous un prefixe unique par run, supprimes systematiquement.
 */

namespace OTTIS\Tests\Unit\Service;

use App\Service\ChatService;
use PHPUnit\Framework\TestCase;

class ChatServiceTest extends TestCase
{
    private const PREFIX = 'svc-test-chat-';

    private ChatService $service;
    private static string $room = '';

    public static function setUpBeforeClass(): void
    {
        self::$room = self::PREFIX . bin2hex(random_bytes(3));
    }

    protected function setUp(): void
    {
        $this->service = new ChatService();
    }

    protected function tearDown(): void
    {
        @unlink(APP_ROOT . '/tmp/LinkQuestions_' . self::$room);
        @unlink(APP_ROOT . '/tmp/ratelimit_' . self::$room . '.json');
    }

    public function testPostMessageStripsTagsAndKeepsRole(): void
    {
        $result = $this->service->postMessage(self::$room, [
            'lineid' => 'u1',
            'inputlanguage' => 'fr',
            'text' => 'salut <b>toi</b>',
            'sender_role' => 'intervenant',
        ]);

        $this->assertSame(200, $result['status']);
        $this->assertSame(['success' => true], $result['body']);
        $content = (string) file_get_contents(APP_ROOT . '/tmp/LinkQuestions_' . self::$room);
        $this->assertStringContainsString('u1;fr;salut toi;intervenant', $content);
    }

    public function testPostMessageMissingTextIsRejected(): void
    {
        $result = $this->service->postMessage(self::$room, [
            'lineid' => 'u1',
            'inputlanguage' => 'fr',
            'text' => null,
        ]);

        $this->assertSame(400, $result['status']);
        $this->assertArrayHasKey('error', $result['body']);
    }

    public function testPostMessageRoleFallsBackToAuditor(): void
    {
        $result = $this->service->postMessage(self::$room, [
            'lineid' => 'u2',
            'inputlanguage' => 'fr',
            'text' => 'hello',
            'sender_role' => 'pirate',
        ]);

        $this->assertSame(200, $result['status']);
        $content = (string) file_get_contents(APP_ROOT . '/tmp/LinkQuestions_' . self::$room);
        $this->assertStringContainsString('u2;fr;hello;auditor', $content, 'un role inconnu devient auditor');
    }

    public function testPostMessageRateLimitedWithinOneSecond(): void
    {
        $fields = ['lineid' => 'u3', 'inputlanguage' => 'fr', 'text' => 'msg'];

        $first = $this->service->postMessage(self::$room, $fields);
        $second = $this->service->postMessage(self::$room, $fields);

        $this->assertSame(200, $first['status']);
        $this->assertSame(429, $second['status'], 'un message maximum par seconde');
    }

    public function testIsMessageAllowedEnforcesTenPerMinute(): void
    {
        // 10 messages recents (5 s de moins que maintenant) : la limite
        // par minute doit declencher meme hors limite par seconde.
        $now = time();
        $data = ['u4' => ['timestamps' => array_fill(0, 10, $now - 5), 'last_check' => $now - 5]];
        file_put_contents(APP_ROOT . '/tmp/ratelimit_' . self::$room . '.json', json_encode($data));

        $this->assertFalse($this->service->isMessageAllowed('u4', self::$room));
    }

    public function testReadQuestionsMissingRoomIsEmpty(): void
    {
        $this->assertSame('', $this->service->readQuestions(self::$room . '-missing'));
    }
}
