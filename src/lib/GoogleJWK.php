<?php
namespace App\Lib;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;

class GoogleJWK
{
    public static function getPublicKey(mixed $kid): OpenSSLAsymmetricKey|OpenSSLCertificate|array|string|bool
    {
        $public_keys_url    = 'https://www.googleapis.com/oauth2/v3/certs';
        $public_keys_json   = file_get_contents($public_keys_url);
        $public_keys        = json_decode($public_keys_json, true);

        $public_key = null;
        foreach ($public_keys['keys'] as $key) {
            if ($key['kid'] === $kid) {
                $public_key = $key;
                break;
            }
        }

        if (!$public_key) {
            echo "Could not find an public key.";
            return false;
        }


        $pem        = self::createPemFromModulusAndExponent($public_key["n"], $public_key["e"]);
        $public_key = openssl_pkey_get_public($pem);

        if ($public_key === false) {
            echo "Could not extract public key from certificate.";
            return false;
        }

        return $public_key;
    }

    private static function encodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), chr(0));

        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }

    private static function convertBase64UrlToBase64(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return strtr($input, '-_', '+/');
    }

    private static function urlsafeB64Decode(string $input): string
    {
        return base64_decode(self::convertBase64UrlToBase64($input));
    }

    # yoiked from https://github.com/firebase/php-jwt/blob/main/src/JWK.php#L231
    private static function createPemFromModulusAndExponent(
        string $n,
        string $e
    ): string {
        $mod = self::urlsafeB64Decode($n);
        $exp = self::urlsafeB64Decode($e);

        $modulus = pack('Ca*a*', 2, self::encodeLength(strlen($mod)), $mod);
        $publicExponent = pack('Ca*a*', 2, self::encodeLength(strlen($exp)), $exp);

        $rsaPublicKey = \pack(
            'Ca*a*a*',
            48,
            self::encodeLength(strlen($modulus) + strlen($publicExponent)),
            $modulus,
            $publicExponent
        );

        // sequence(oid(1.2.840.113549.1.1.1), null)) = rsaEncryption.
        $rsaOID = pack('H*', '300d06092a864886f70d0101010500'); // hex version of MA0GCSqGSIb3DQEBAQUA
        $rsaPublicKey = chr(0) . $rsaPublicKey;
        $rsaPublicKey = chr(3) . self::encodeLength(strlen($rsaPublicKey)) . $rsaPublicKey;

        $rsaPublicKey = pack(
            'Ca*a*',
            48,
            self::encodeLength(strlen($rsaOID . $rsaPublicKey)),
            $rsaOID . $rsaPublicKey
        );

        return "-----BEGIN PUBLIC KEY-----\r\n" .
            chunk_split(base64_encode($rsaPublicKey), 64) .
            '-----END PUBLIC KEY-----';
    }
}
