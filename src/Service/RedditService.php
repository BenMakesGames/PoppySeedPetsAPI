<?php
namespace App\Service;

use App\Entity\Article;
use GuzzleHttp\Client;

class RedditService
{
    private $apiClientId;
    private $apiClientSecret;
    private $username;
    private $password;

    public function __construct(
        string $apiClientId, string $apiClientSecret, string $username, string $password
    )
    {
        $this->apiClientId = $apiClientId;
        $this->apiClientSecret = $apiClientSecret;
        $this->username = $username;
        $this->password = $password;
    }

    public function getUserAgent(): string
    {
        return 'PHP:Poppy Seed Pets news poster:1 (by /u/BenMakesGames)';
    }

    private function getAccessToken()
    {
        $client = new Client([
            'base_uri' => 'https://www.reddit.com/'
        ]);

        $response = $client->post('api/v1/access_token', [
            'data' => [
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ],
            'headers' => [
                'User-Agent' => $this->getUserAgent(),
            ],
            'auth' => [ $this->apiClientId, $this->apiClientSecret ]
        ]);

        return $response['access_token'];
    }

    public function postArticle(Article $article)
    {
        $accessToken = $this->getAccessToken();

        $client = new Client([
            'base_uri' => 'https://oauth.reddit.com/'
        ]);

        $client->post('api/submit', [
            'headers' => [
                'Authorization' => 'bearer ' . $accessToken,
                'User-Agent' => $this->getUserAgent(),
            ],
            'json' => [
                'sr' => 'poppyseedpets',
                'title' => $article->getTitle(),
                'text' => $article->getBody(),
            ]
        ]);
    }
}
