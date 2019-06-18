<?php
namespace App\Service;

class HuntingService
{
    private $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

}