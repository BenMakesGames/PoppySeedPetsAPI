<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Class for cryptographic operations.
 * All methods in this class should be cryptographically secure.
 */
final class CryptographicFunctions
{
    /**
     * Generates a cryptographically secure random string of specified length.
     *
     * @param int $length The desired length of the random string
     * @param string $allowedCharacters The set of characters to use (default: alphanumeric)
     * @return string A random string of the specified length
     */
    public static function generateSecureRandomString(int $length, string $allowedCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'): string
    {
        $result = '';
        $allowedCharactersCount = strlen($allowedCharacters);

        for ($i = 0; $i < $length; $i++)
            $result .= $allowedCharacters[random_int(0, $allowedCharactersCount - 1)];

        return $result;
    }
} 