<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class HotPotatoService
{
    private $userRepository;
    private $em;
    private $responseService;

    public function __construct(
        UserRepository $userRepository, EntityManagerInterface $em, ResponseService $responseService
    )
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->responseService = $responseService;
    }

    public function countTosses(Inventory $inventory)
    {
        $numberOfTosses = 0;

        foreach($inventory->getComments() as $comment)
        {
            if(strpos($comment, ' tossed this to ') !== false)
                $numberOfTosses++;
        }

        return $numberOfTosses;
    }

    public function tossItem(Inventory $inventory)
    {
        $owner = $inventory->getOwner();

        $target = $this->userRepository->findOneRecentlyActive($owner);

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

        return $this->responseService->itemActionSuccess('You toss the ' . $inventory->getFullItemName() . ' to <a href="/poppyopedia/resident/' . $target->getId() . '">' . $target->getName() . '</a>!', [ 'itemDeleted' => true ]);
    }
}