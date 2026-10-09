<?php

namespace App\Service;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

/**
 * RoomPresence
 *
 * Trace la presence de la page conference ouverte pour une salle :
 * la page conference envoie un heartbeat periodique ; sans heartbeat
 * recent, la conference est consideree fermee (fermeture d'onglet,
 * crash, perte reseau...). Les auditeurs s'appuient sur cet etat
 * pour afficher "En direct" ou "En erreur".
 */
class RoomPresence
{
    /** Duree (s) de validite d'un heartbeat */
    private const HEARTBEAT_TTL = 60;

    private static function getAliveFile(string $roomid): string
    {
        return APP_ROOT . '/tmp/room_alive_' . basename($roomid);
    }

    /**
     * Enregistrer un heartbeat : la page conference est ouverte.
     */
    public static function heartbeat(string $roomid): void
    {
        file_put_contents(self::getAliveFile($roomid), time());
        self::cleanup();
    }

    /**
     * Marquer la conference comme fermee (fermeture de la page).
     */
    public static function close(string $roomid): void
    {
        $file = self::getAliveFile($roomid);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * La page conference de cette salle est-elle ouverte ?
     */
    public static function isLive(string $roomid): bool
    {
        $file = self::getAliveFile($roomid);
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }

        return (time() - (int) file_get_contents($file)) < self::HEARTBEAT_TTL;
    }

    /**
     * Supprimer les fichiers de presence expires.
     */
    public static function cleanup(): void
    {
        foreach (glob(APP_ROOT . '/tmp/room_alive_*') ?: [] as $file) {
            if ((time() - (int) file_get_contents($file)) >= self::HEARTBEAT_TTL) {
                @unlink($file);
            }
        }
    }
}
