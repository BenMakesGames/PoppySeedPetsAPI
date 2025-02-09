<?php
declare(strict_types=1);

namespace App\Controller\Patreon;

use App\Attributes\DoesNotRequireHouseHours;
use App\Entity\UserSubscription;
use App\Enum\PatreonTierEnum;
use App\Exceptions\PSPFormValidationException;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/patreon")]
class LiveUpdatesController extends AbstractController
{
    #[DoesNotRequireHouseHours]
    #[Route("/liveUpdates", methods: ["POST"])]
    public function connectPatreonAccount(
        Request $request, ResponseService $responseService, EntityManagerInterface $em
    )
    {
        $signature = $request->headers->get('X-Patreon-Signature');
        $event = $request->headers->get('X-Patreon-Event');
        $contentsString = $request->getContent();

        $contents = json_decode($contentsString, true);

        if (json_last_error() !== JSON_ERROR_NONE)
            throw new PSPFormValidationException('Bad request.');

        $hashed = hash_hmac('md5', $contentsString, $_ENV['PATREON_WEBHOOK_SECRET']);

        if(!hash_equals($hashed, $signature))
            throw new PSPFormValidationException('Bad request.');

        $patronId = (int)$contents['data']['relationships']['patron']['data']['id'];
        $rewardId = (int)$contents['data']['relationships']['reward']['data']['id'];

        if($patronId < 1) throw new \Exception('Patron ID is not valid.');
        if($rewardId < 1) throw new \Exception('Reward ID is not valid.');

        switch($event)
        {
            case 'members:pledge:create':
            case 'members:pledge:update':
                self::upsertPledge($patronId, $rewardId, $em);
                break;

            case 'members:pledge:delete':
                self::deletePledge($patronId, $em);
                break;

            default:
                throw new PSPFormValidationException('Bad request.');
        }

        $em->flush();

        return $responseService->success();
    }

    private static function upsertPledge(int $patronId, int $rewardId, EntityManagerInterface $em): void
    {
        $userSubscription = $em->getRepository(UserSubscription::class)->findOneBy([
            'patreonUserId' => $patronId
        ]);

        if(!$userSubscription)
        {
            $userSubscription = (new UserSubscription())
                ->setPatreonUserId($patronId)
            ;

            $em->persist($userSubscription);
        }

        $userSubscription
            ->setTier(PatreonTierEnum::getByRewardId($rewardId))
            ->setUpdatedOn()
        ;
    }

    private static function deletePledge(int $patronId, EntityManagerInterface $em): void
    {
        $userSubscription = $em->getRepository(UserSubscription::class)->findOneBy([
            'patreonUserId' => $patronId
        ]);

        if(!$userSubscription)
            return;

        if($userSubscription->getUser() === null)
            $em->remove($userSubscription);
        else
            $userSubscription->setTier(null);
    }
}
