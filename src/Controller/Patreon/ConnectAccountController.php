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


namespace App\Controller\Patreon;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\User;
use App\Entity\UserSubscription;
use App\Exceptions\PSPFormValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/patreon")]
class ConnectAccountController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/connectAccount", methods: ["GET"])]
    public function connectPatreonAccount(
        Request $request, EntityManagerInterface $em
    ): RedirectResponse
    {
        $code = $request->query->get('code');
        $userId = $request->query->get('state');

        if(!$code || !$userId)
            throw new PSPFormValidationException('Code and state are required.');

        $user = $em->getRepository(User::class)->find($userId);

        if(!$user)
            throw new PSPFormValidationException('Invalid state.');

        $patreonOauth = new \Patreon\OAuth($_ENV['PATREON_CLIENT_ID'], $_ENV['PATREON_CLIENT_SECRET']);
        $patreonTokens = $patreonOauth->get_tokens($code, $_ENV['PATREON_REDIRECT_URI']);

        $patreonApi = new \Patreon\API($patreonTokens['access_token']);
        $patreonUser = $patreonApi->get_data('identity');

        if(preg_match('/^[1-9][0-9]*$/', $patreonUser['data']['id']) !== 1)
            throw new \Exception('Patreon user id is not valid! (Expected a natural number; got ' . $patreonUser['data']['id'] . ')');

        $patreonUserId = (int)$patreonUser['data']['id'];

        $subscription = $em->getRepository(UserSubscription::class)->findOneBy([
            'patreonUserId' => $patreonUserId
        ]);

        if(!$subscription)
        {
            $subscription = new UserSubscription();
            $subscription->setPatreonUserId($patreonUserId);
        }

        $user->setSubscription($subscription);

        $em->flush();

        return new RedirectResponse('https://poppyseedpets.com/settings/patreon');
    }
}
