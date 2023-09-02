<?php
namespace App\Controller\Patreon;

use App\Entity\UserSubscription;
use App\Exceptions\PSPFormValidationException;
use App\Repository\UserRepository;
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
        $patreonUser = $patreonApi->get_data('identity' .
            '?include=memberships' .
            '&fields' . urlencode('[member]') . '=patron_status,currently_entitled_amount_cents' .
            '&fields' . urlencode('[relationship]') . '=currently_entitled_tiers' .
            '&fields' . urlencode('[tier]') . '=title'
        );

        // TODO: if user has a subscription, get the tier, and log it
        var_dump($patreonUser);

        // TODO: incorrect; just testing
        $amount = $patreonUser['included'][0]['type'] === 'member' ? 500 : 0;

        if(preg_match('/^[1-9][0-9]*$/', $patreonUser['data']['id']) !== 1)
            throw new \Exception('Patreon user id is not valid! (Expected a natural number; got ' . $patreonUser['data']['id'] . ')');

        $patreonUserId = (int)$patreonUser['data']['id'];

        if(!$user->getSubscription())
            $user->setSubscription(new UserSubscription());

        $user->getSubscription()
            ->setMonthlyAmountInCents($amount)
            ->setPatreonUserId($patreonUserId)
            ->setUpdatedOn();

        $em->flush();

        die;

        return new RedirectResponse('https://poppyseedpets.com/settings/patreon');
    }
}
