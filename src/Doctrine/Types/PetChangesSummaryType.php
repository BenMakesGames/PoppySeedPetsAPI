<?php
declare(strict_types = 1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class PetChangesSummaryType extends Type
{
    public const string NAME = 'pet_changes_summary';

    public function getName(): string
    {
        return self::NAME;
    }

    // Maps the Doctrine type to a database type (e.g., TEXT or BLOB)
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    // Converts the database value to a PHP value
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Use unserialize to convert the string back to a PHP object/array
        $unserialized = @unserialize($value);

        if ($unserialized === false && $value !== serialize(false)) {
            // Handle unserialization errors
            throw new \RuntimeException('Error unserializing value from database.');
        }

        return $unserialized;
    }

    // Converts the PHP value to a database value (serialized string)
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        // Use serialize to convert the PHP object/array to a string
        return serialize($value);
    }

    // Specifies that this type requires a comment for DDL generation (optional, but helpful)
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}