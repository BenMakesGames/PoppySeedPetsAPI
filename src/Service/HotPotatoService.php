<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\UserFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class HotPotatoService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ResponseService $responseService
    )
    {
    }

    public static function countTosses(Inventory $inventory): int
    {
        $numberOfTosses = 0;

        foreach($inventory->getComments() as $comment)
        {
            if(strpos($comment, ' tossed this to ') !== false)
                $numberOfTosses++;
        }

        return $numberOfTosses;
    }

    public function tossItem(Inventory $inventory, ?string $messageStart = null): JsonResponse
    {
        $owner = $inventory->getOwner();

        $target = UserFunctions::findOneRecentlyActive($this->em, $owner, 24);

        if($target === null)
            return $this->responseService->itemActionSuccess('Hm... there\'s no one to toss it to! (I guess no one\'s been playing Poppy Seed Pets...)');

        $inventory
            ->setOwner($target)
            ->addComment($owner->getName() . ' tossed this to ' . $target->getName() . '!')
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
            ->setSellPrice(null)
        ;

        $this->em->flush();

        if($messageStart == null)
            $messageStart = 'You toss the ' . $inventory->getFullItemName();

        return $this->responseService->itemActionSuccess($messageStart . ' to <a href="/poppyopedia/resident/' . $target->getId() . '">' . $target->getName() . '</a>!', [ 'itemDeleted' => true ]);
    }
}