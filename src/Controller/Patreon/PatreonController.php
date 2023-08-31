<?php
namespace App\Controller\Patreon;

use App\Exceptions\PSPFormValidationException;
use App\Repository\UserRepository;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/patreon")
 */
class PatreonController extends AbstractController
{
    /**
     * @Route("/connectAccount", methods={"GET"})
     * @DoesNotRequireHouseHours()
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

        $patreonApi = new \Patreon\API($patreonTokens['access_token']);
        $patreonUser = $patreonApi->get_data('identity' .
            '?include=tiers' .
            '&fields' . urlencode('[member]') . '=patron_status' .
            '&fields' . urlencode('[tier]') . '=title'
        );

        var_dump(json_encode($patreonUser));

        die;
    }
}
