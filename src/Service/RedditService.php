<?php
namespace App\Service;

use App\Entity\Article;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;

class RedditService
{
    /** @var HandlerStack  */
    private $clientHandler;

    public function __construct(string $apiClientId, string $apiClientSecret)
    {
        $reauthClient = new Client([
            'base_uri' => 'http://www.reddit.com'
        ]);

        $grant = new ClientCredentials($reauthClient, [
            'client_id' => $apiClientId,
            'client_secret' => $apiClientSecret,
        ]);

        $oauth2Middleware = new OAuth2Middleware($grant);

        $this->clientHandler = HandlerStack::create();
        $this->clientHandler->push($oauth2Middleware);
    }

    private function createClient(): Client
    {
        return new Client([ 'handler' => $this->clientHandler, 'auth' => 'oauth' ]);
    }

    public function getUserAgent(): string
    {
        return 'PHP:Poppy Seed Pets news poster:1 (by /u/BenMakesGames)';
    }

    public function postArticle(Article $article)
    {
        $client = $this->createClient();

        $client->post('/api/submit', [
            'headers' => [
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
