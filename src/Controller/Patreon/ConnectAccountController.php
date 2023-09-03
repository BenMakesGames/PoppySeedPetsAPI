<?php
namespace App\Controller\Patreon;

use App\Exceptions\PSPFormValidationException;
use App\Repository\UserRepository;
use App\Repository\UserSubscriptionRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Annotations\DoesNotRequireHouseHours;

/**
 * @Route("/patreon")
 */
class ConnectAccountController extends AbstractController
{
    /**
     * @Route("/connectAccount", methods={"GET"})
     * @DoesNotRequireHouseHours()
     */
    public function connectPatreonAccount(
        Request $request, ResponseService $responseService, UserRepository $userRepository,
        UserSubscriptionRepository $userSubscriptionRepository,
        EntityManagerInterface $em
    )
    {
        $code = $request->query->get('code');
        $userId = $request->query->get('state');

        if(!$code || !$userId)
            throw new PSPFormValidationException('Code and state are required.');

        $user = $userRepository->find($userId);

        if(!$user)
            throw new PSPFormValidationException('Invalid state.');

        $patreonOauth = new \Patreon\OAuth($_ENV['PATREON_CLIENT_ID'], $_ENV['PATREON_CLIENT_SECRET']);
        $patreonTokens = $patreonOauth->get_tokens($code, $_ENV['PATREON_REDIRECT_URI']);

        $patreonApi = new \Patreon\API($patreonTokens['access_token']);
        $patreonUser = $patreonApi->get_data('identity');

        if(preg_match('/^[1-9][0-9]*$/', $patreonUser['data']['id']) !== 1)
            throw new \Exception('Patreon user id is not valid! (Expected a natural number; got ' . $patreonUser['data']['id'] . ')');

        $patreonUserId = (int)$patreonUser['data']['id'];

        $existingSubscription = $userSubscriptionRepository->findOneBy([
            'patreonUserId' => $patreonUserId
        ]);

        if($existingSubscription)
            $user->setSubscription($existingSubscription);

        $em->flush();

        return new RedirectResponse('https://poppyseedpets.com/settings/patreon');
    }
}
