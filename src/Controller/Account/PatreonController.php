<?php
namespace App\Controller\Account;

use App\Exceptions\PSPFormValidationException;
use App\Repository\UserRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/account/patreon")
 */
class PatreonController extends AbstractController
{
    /**
     * @Route("/connect", methods={"GET"})
     */
    public function connectPatreonAccount(
        Request $request, ResponseService $responseService, UserRepository $userRepository
    )
    {
        $code = $request->query->get('code');
        $userId = $request->query->get('state');

        if(!$code || !$userId)
            throw new PSPFormValidationException('Code and state are required.');

        $patreonOauth = new \Patreon\OAuth($_ENV['PATREON_CLIENT_ID'], $_ENV['PATREON_CLIENT_SECRET']);
        $patreonTokens = $patreonOauth->get_tokens($code, $_ENV['PATREON_REDIRECT_URI']);

        var_dump($patreonTokens);

        $patreonApi = new \Patreon\API($patreonTokens['access_token']);
        $patreonUser = $patreonApi->fetch_user();

        var_dump($patreonUser);

        die;
    }
}
