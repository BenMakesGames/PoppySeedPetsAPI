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


namespace App\Service;

use App\Entity\Article;
use GuzzleHttp\Client;

class RedditService
{
    private string $apiClientId;
    private string $apiClientSecret;
    private string $username;
    private string $password;

    public function __construct(
        string $apiClientId, string $apiClientSecret, string $username, string $password
    )
    {
        $this->apiClientId = $apiClientId;
        $this->apiClientSecret = $apiClientSecret;
        $this->username = $username;
        $this->password = $password;
    }

    public static function getUserAgent(): string
    {
        return 'PHP:Poppy Seed Pets news poster:1 (by /u/BenMakesGames)';
    }

    private function getAccessToken(): string
    {
        $client = new Client([
            'base_uri' => 'https://www.reddit.com/'
        ]);

        $response = $client->post('api/v1/access_token', [
            'form_params' => [
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ],
            'headers' => [
                'User-Agent' => RedditService::getUserAgent(),
            ],
            'auth' => [ $this->apiClientId, $this->apiClientSecret ]
        ]);

        $responseData = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        return $responseData['access_token'];
    }

    public function postArticle(Article $article): void
    {
        $accessToken = $this->getAccessToken();

        $client = new Client([
            'base_uri' => 'https://oauth.reddit.com/'
        ]);

        $client->post('api/submit', [
            'headers' => [
                'Authorization' => 'bearer ' . $accessToken,
                'User-Agent' => RedditService::getUserAgent(),
            ],
            'form_params' => [
                'sr' => 'poppyseedpets',
                'title' => $article->getTitle(),
                'text' => $article->getBody(),
                'kind' => 'self',
            ]
        ]);
    }
}
