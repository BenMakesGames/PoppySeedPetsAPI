<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace Functions;

use App\Functions\JsonResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * JUSTIFICATION: This test ensures that the JsonResponseFactory ACTUALLY handles UTF-8 encoding properly.
 * When the API has choked on someone's unicode-containing pet name, it's been hard to debug, while making
 * the game completely unplayable for that player.
 */
class JsonResponseFactoryTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testHandlesUtf8Properly(): void
    {
        // Test data with various UTF-8 edge cases
        $testData = [
            'normalText' => 'Hello World',
            'emoji' => '👋🌍',
            'specialChars' => 'é è ñ',
            'malformedUtf8' => "Test\xC3\x28", // Invalid UTF-8 sequence
            'mixedEncoding' => mb_convert_encoding('Hello ñ World', 'ISO-8859-1', 'UTF-8')
        ];

        $response = JsonResponseFactory::create($this->serializer, $testData);
        
        // Get the response content
        $content = $response->getContent();
        
        // Assert that the response is valid JSON
        $this->assertJson($content);
        
        // Decode the JSON
        $decodedContent = json_decode($content, true);
        
        // Verify all our test data made it through
        $this->assertArrayHasKey('normalText', $decodedContent);
        $this->assertArrayHasKey('emoji', $decodedContent);
        $this->assertArrayHasKey('specialChars', $decodedContent);
        $this->assertArrayHasKey('malformedUtf8', $decodedContent);
        $this->assertArrayHasKey('mixedEncoding', $decodedContent);
        
        // Verify the content is correct
        $this->assertEquals('Hello World', $decodedContent['normalText']);
        $this->assertEquals('👋🌍', $decodedContent['emoji']);
        $this->assertEquals('é è ñ', $decodedContent['specialChars']);
        
        // The malformed UTF-8 should be replaced with the Unicode replacement character (�)
        $this->assertStringContainsString('�', $decodedContent['malformedUtf8']);
    }
} 