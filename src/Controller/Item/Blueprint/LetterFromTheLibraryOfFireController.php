<?php
namespace App\Controller\Item\Blueprint;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Dragon;
use App\Entity\Inventory;
use App\Entity\User;
use App\Functions\ColorFunctions;
use App\Repository\DragonRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetAssistantService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/letterFromTheLibraryOfFire")]
class LetterFromTheLibraryOfFireController extends AbstractController
{
    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function readNote(
        Inventory $inventory, ResponseService $responseService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'letterFromTheLibraryOfFire/#/read');

        $user = $this->getUser();

        if($user->getFireplace() === null)
        {
            return $responseService->itemActionSuccess('Weird: the letter is blank...');
        }
        else
        {
            return $responseService->itemActionSuccess('# Greetings!

An anonymous benefactor has sponsored your registration as a member of the Library of Fire!

Though you may not heard of The Library of Fire, you\'re no doubt familiar with the myth of the burning of the Library of Alexandria.

The Library of Fire was founded by Aristarchus in 138 BC, shortly after his death, to preserve the spirit of the Library of Alexandria. Named for the enduring flame of knowledge, whispers of the Library of Fire made their way to the material world, where its name become entangled with the story of the decline of the Library of Alexandria.

Today, the Library of Fire is home to over 120 trillion works, in the form of books, journals, songs, videos, ixettes, paintings, transmissions, and more!

Beyond public access to floors 1 through 7, your level of membership entitles you to:
* Browsing & borrowing access to floors 8 through 414 of the library
* Supervised browsing access to floors 415 through 481 of the library
* Your name on a brick in the Sponsors\' Arboretum (east wing, floors 1 through 4)
* One Magma Whelp (melt the seal on this envelope to claim)

Please note that repairs to the north wing of floors 29 and 30 are still ongoing. We do not currently have an estimated date of completion. We apologize for the inconvenience.

For questions & support regarding Magma Whelps, the information desk (floor 2) can put you in contact with The Offices of Doon Westergren. 

The Library of Fire is always open. We look forward to seeing you!');
        }
    }
    #[Route("/{inventory}/meltSeal", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function meltSeal(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, DragonRepository $dragonRepository, IRandom $squirrel3,
        PetAssistantService $petAssistantService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'letterFromTheLibraryOfFire/#/meltSeal');

        /** @var User $user */
        $user = $this->getUser();
        $fireplace = $user->getFireplace();
        $dragon = $dragonRepository->findOneBy([ 'owner' => $user ]);

        if($fireplace && !$dragon)
        {
            $colors = [
                ColorFunctions::HSL2Hex(0, 0.52, 0.5),
                ColorFunctions::HSL2Hex($squirrel3->rngNextInt(0, 1000) / 1000, 0.4, 0.42),
            ];

            if($squirrel3->rngNextInt(1, 3) === 1)
            {
                $temp = $colors[0];
                $colors[0] = $colors[1];
                $colors[1] = $temp;
            }

            $dragon = (new Dragon())
                ->setOwner($user)
                ->setName($squirrel3->rngNextFromArray([
                    'Tanin', 'Draak', 'Dragua', 'Zenido', 'Vishap', 'Herensuge', 'Ghuṛi Biśēṣa',
                    'Chinjoka', 'Qiú', 'Lohikäärme', 'Drak\'oni', 'Ḍrēgana', 'Naga', 'Ajagar',
                    'Zaj', 'Sárkány', 'Dreki', 'Ryū', 'Aydahar', 'Neak', 'Yong', 'Zîha',
                    'Ajıdaar', 'Mangkon', 'Pūķis', 'Zmej', 'Tarakona', 'Luu', 'Smok', 'Balaur',
                    'Tarako', 'Dhiragoni', 'Makarā', 'Masduulaagii', 'Joka', 'Aƶdaho', 'Ṭirākaṉ',
                    'Mạngkr', 'Ejderha', 'Ajdaho', 'Inamba',
                ]))
                ->setColorA($squirrel3->rngNextTweakedColor($colors[0]))
                ->setColorB($squirrel3->rngNextTweakedColor($colors[1]))
            ;

            $em->persist($dragon);

            if($fireplace->getHelper())
            {
                $helper = $fireplace->getHelper();
                $petAssistantService->stopAssisting($user, $helper);

                $message = 'A small dragon appears on the hearth of your fireplace! ' . $helper->getName() . ', feeling a little crowded, leaves... Oh, yeah: and the letter? It dissolves into Paper and Quintessence! (I totes understand if you need a minute to unpack everything that just transpired... it was a bit!)';
            }
            else
                $message = 'A small dragon appears on the hearth of your fireplace! (Also, the letter dissolves into Paper and Quintessence, but, like, whoa, who even cares about that? Small dragon! SMALL DRAGON!)';
        }
        else
            $message = 'The letter dissolves into Paper and Quintessence. Handy.';

        $inventoryService->receiveItem('Paper', $user, $user, 'The remains of a letter that had been sent to ' . $user->getName() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());
        $inventoryService->receiveItem('Quintessence', $user, $user, 'The remains of a letter that had been sent to ' . $user->getName() . '.', $inventory->getLocation(), $inventory->getLockedToOwner());

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
