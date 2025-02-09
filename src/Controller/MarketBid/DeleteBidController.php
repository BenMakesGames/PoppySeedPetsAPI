<?php
declare(strict_types=1);

namespace App\Controller\MarketBid;

use App\Entity\User;
use App\Exceptions\PSPNotFoundException;
use App\Repository\MarketBidRepository;
use App\Service\ResponseService;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/marketBid")]
class DeleteBidController extends AbstractController
{
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/{bidId}", methods: ["DELETE"], requirements: ["bidId" => "\d+"])]
    public function deleteBid(
        int $bidId, ResponseService $responseService, TransactionService $transactionService,
        MarketBidRepository $marketBidRepository, EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();
        $bid = $marketBidRepository->find($bidId);

        if(!$bid || $bid->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('That bid could not be found (maybe someone else already sold you the item!)');

        $em->remove($bid);

        $transactionService->getMoney($user, $bid->getQuantity() * $bid->getBid(), 'Money refunded from canceling bid on ' . $bid->getQuantity() . 'x ' . $bid->getItem()->getName() . '.');

        return $responseService->success();
    }
}
