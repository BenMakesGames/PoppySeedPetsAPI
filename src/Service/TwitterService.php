<?php
namespace App\Service;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Entity\Article;

class TwitterService
{
    private $connection;

    public function __construct(string $apiConsumerKey, string $apiConsumerSecret, string $apiAccessToken, string $apiAccessTokenSecret)
    {
        $this->connection = new TwitterOAuth($apiConsumerKey, $apiConsumerSecret, $apiAccessToken, $apiAccessTokenSecret);
        $this->connection->setTimeouts(15, 15);
        $this->connection->get('account/verify_credentials');
    }

    public function postArticle(Article $article)
    {
        $response = $this->connection->post('statuses/update', [ 'status' => $article->getTitle() ]);

        if(property_exists($response, 'errors') && count($response->errors) > 0)
            throw new \Exception(implode(' ', array_map(function($r) { return $r->message; }, $response->errors)));
    }
}