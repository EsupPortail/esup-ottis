<?php

/**
 * Tests unitaires pour App\Security\RoomTokenManager
 *
 * Non-regression du modele d'autorisation des salles, en particulier
 * cleanupExpiredTokens (le bug d'aout 2026 supprimait TOUS les tokens)
 * et la sanitisation du ROOMID par basename().
 *
 * Les fichiers de tokens sont crees dans le dossier de tokens reel (via
 * la meme logique de fallback que la classe), sous des noms de salle
 * pre fixes uniquement, supprimes systematiquement en tearDown.
 */

namespace OTTIS\Tests\Unit\Security;

use App\Security\RoomTokenManager;
use PHPUnit\Framework\TestCase;

class RoomTokenManagerTest extends TestCase
{
    /**
     * Prefixe unique au run : aucun fichier reel du serveur de dev ne
     * peut croiser ces salles, et le nettoyage ne touche que ces tests.
     */
    private static string $prefix = 'test-roomtoken-';

    public static function setUpBeforeClass(): void
    {
        self::$prefix = 'test-roomtoken-' . bin2hex(random_bytes(3)) . '-';
    }

    protected function tearDown(): void
    {
        self::removeTestTokenFiles();
    }

    public static function tearDownAfterClass(): void
    {
        self::removeTestTokenFiles();
    }

    /**
     * Reproduit la resolution du dossier de tokens de RoomTokenManager
     * (APP_ROOT/tmp, sinon dossier temporaire systeme) pour nettoyer
     * uniquement les fichiers crees par cette suite.
     */
    private static function tokenDir(): string
    {
        $dir = APP_ROOT . '/tmp';
        if (!is_dir($dir) || !is_writable($dir)) {
            $dir = rtrim(sys_get_temp_dir(), '/') . '/ottis_tokens/';
        }

        return $dir;
    }

    private static function removeTestTokenFiles(): void
    {
        array_map('unlink', glob(self::tokenDir() . '/room_token_' . self::$prefix . '*') ?: []);
    }

    public function testGeneratedTokenValidatesForItsRoom(): void
    {
        $token = RoomTokenManager::generateRoomToken(self::$prefix . 'room1');

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $token);
        $this->assertTrue(RoomTokenManager::validateRoomToken(self::$prefix . 'room1', $token));
    }

    public function testGenerateReusesExistingToken(): void
    {
        $first = RoomTokenManager::generateRoomToken(self::$prefix . 'room1');
        $second = RoomTokenManager::generateRoomToken(self::$prefix . 'room1');

        $this->assertSame($first, $second);
    }

    public function testValidateRejectsWrongTokenAndUnknownRoom(): void
    {
        $roomid = self::$prefix . 'room1';
        $token = RoomTokenManager::generateRoomToken($roomid);

        $this->assertFalse(RoomTokenManager::validateRoomToken($roomid, str_repeat('0', 32)));
        $this->assertFalse(RoomTokenManager::validateRoomToken(self::$prefix . 'unknown', $token));
        $this->assertFalse(RoomTokenManager::validateRoomToken($roomid, ''));
    }

    public function testRoomIdIsConstrainedToBasename(): void
    {
        RoomTokenManager::generateRoomToken('../' . self::$prefix . 'traversal');

        // basename() : le fichier reste dans le dossier de tokens, aucune
        // ecriture hors de APP_ROOT/tmp via le "../".
        $this->assertFileExists(self::tokenDir() . '/room_token_' . self::$prefix . 'traversal');
        $this->assertFileDoesNotExist(dirname(self::tokenDir()) . '/' . self::$prefix . 'traversal');
    }

    public function testLegacyTokenFormatStillValidates(): void
    {
        $roomid = self::$prefix . 'legacy';
        file_put_contents(self::tokenDir() . '/room_token_' . $roomid, "legacylegacylegacylegacy123\n" . (time() - 60));

        $this->assertTrue(RoomTokenManager::validateRoomToken($roomid, 'legacylegacylegacylegacy123'));
    }

    /**
     * Non-regression d'aout 2026 : cleanupExpiredTokens ne doit supprimer
     * que les tokens expires, jamais les tokens recents des autres salles.
     */
    public function testCleanupOnlyRemovesExpiredTokens(): void
    {
        $freshRoomid = self::$prefix . 'fresh';
        $expiredRoomid = self::$prefix . 'expired';
        $fresh = RoomTokenManager::generateRoomToken($freshRoomid);
        RoomTokenManager::generateRoomToken($expiredRoomid);

        $expiredFile = self::tokenDir() . '/room_token_' . $expiredRoomid;
        file_put_contents($expiredFile, "expiredtokenexpiredtoken12345\n" . (time() - 25 * 3600));

        RoomTokenManager::cleanupExpiredTokens();

        $this->assertFileDoesNotExist($expiredFile, 'le token expire doit etre supprime');
        $this->assertFileExists(self::tokenDir() . '/room_token_' . $freshRoomid, 'le token recent doit etre conserve');
        $this->assertTrue(RoomTokenManager::validateRoomToken($freshRoomid, $fresh), 'le token conserve doit rester valide');
    }

    public function testOwnerUidIsPersistedAndKeepsTokenValid(): void
    {
        $roomid = self::$prefix . 'owned';
        $token = RoomTokenManager::generateRoomToken($roomid);
        $this->assertSame('', RoomTokenManager::getRoomOwnerUid($roomid));

        RoomTokenManager::setRoomOwnerUid($roomid, 'a12345');

        $this->assertSame('a12345', RoomTokenManager::getRoomOwnerUid($roomid));
        $this->assertTrue(RoomTokenManager::validateRoomToken($roomid, $token));
    }

    public function testFindRecentRoomByOwner(): void
    {
        $roomid = self::$prefix . 'owner-room';
        RoomTokenManager::generateRoomToken($roomid);
        RoomTokenManager::setRoomOwnerUid($roomid, 'u42');

        $this->assertSame($roomid, RoomTokenManager::findRecentRoomByOwner('u42'));
        $this->assertNull(RoomTokenManager::findRecentRoomByOwner('nobody'));
    }

    public function testCleanupRoomTokenRemovesTheRoomToken(): void
    {
        $roomid = self::$prefix . 'todelete';
        RoomTokenManager::generateRoomToken($roomid);

        RoomTokenManager::cleanupRoomToken($roomid);

        $this->assertFileDoesNotExist(self::tokenDir() . '/room_token_' . $roomid);
    }

    public function testHasValidRoomToken(): void
    {
        $roomid = self::$prefix . 'present';

        $this->assertFalse(RoomTokenManager::hasValidRoomToken(self::$prefix . 'missing'));

        RoomTokenManager::generateRoomToken($roomid);

        $this->assertTrue(RoomTokenManager::hasValidRoomToken($roomid));
    }
}
