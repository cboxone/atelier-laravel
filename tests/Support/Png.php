<?php

namespace Tests\Support;

// Vraies images PNG pour les tests (générées une fois, sans dépendance à GD).
class Png
{
    public const SIGNATURE_200x80 = 'iVBORw0KGgoAAAANSUhEUgAAAMgAAABQCAIAAADTD63nAAABRElEQVR42u3aMQqEQBBE0R3Z+195TDeQDbRaepj3M1N5dIE45pwfKd3hFQgsgSWwpOeNMX4fv96Igp7AUhUpsJTxdPnFCizdv09/PoKCpaQnsJSZPLD0hiewlJk8sFR7osBSoSeweKoiBRZPhb/igWXywFJ7T2CZPLC0wokCiyew1H7ywOIJLK0zeWDxBBZP60weWE4UWDwt6wkskwcWT2Bpt8kDiyewTB5YcqLA4gkskwcWTzyBZfLA4gkskyewnCiweALL5AksnsAyeWDxpB1hmTywnCiweNImsEweWDyBZfK0CSyewDJ5YDlR2gQWT2CZPHWFxRNYJk+NYfEElslTY1hOFFg8qSsskwcWT2oMy+QpCYsnJWGZPIVhOVFKwuJJSVgmT0lYPCkMy+QpCYsnlU8hUkrC4knPO6jSG7AksNS3E/ew2S1PcqKzAAAAAElFTkSuQmCC';
    public const PIXEL_1x1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public static function signatureDataUrl(): string
    {
        return 'data:image/png;base64,' . self::SIGNATURE_200x80;
    }
}
