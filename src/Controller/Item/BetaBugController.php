<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\Merit;
use App\Entity\PetSpecies;
use App\Entity\User;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\MeritRepository;
use App\Repository\PetRepository;
use App\Service\PetFactory;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/item/betaBug")
 */
class BetaBugController extends AbstractController
{
    private const ALLOWED_ITEMS = [
        'Cooking Buddy',
        'Cooking "Alien"',
        'Sentient Beetle',
        'Rainbowsaber'
    ];

    /**
     * @Route("/{inventory}/eligibleItems", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getEligibleItems(Inventory $inventory, ResponseService $responseService, InventoryRepository $inventoryRepository)
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'betaBug');

        $qb = $inventoryRepository->createQueryBuilder('i');
        $items = $qb
            ->join('i.item', 'item')
            ->andWhere('i.owner=:ownerId')
            ->andWhere('item.name IN (:allowedItemNames)')
            ->andWhere('i.location=:home')
            ->setParameter('ownerId', $user->getId())
            ->setParameter('allowedItemNames', self::ALLOWED_ITEMS)
            ->setParameter('home', LocationEnum::HOME)
            ->getQuery()
            ->execute();

        return $responseService->success($items, [ SerializationGroupEnum::MY_INVENTORY ]);
    }

    /**
     * @Route("/{inventory}/use", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function useBug(
        Inventory $inventory, Request $request, InventoryRepository $inventoryRepository,
        ResponseService $responseService, EntityManagerInterface $em, PetFactory $petFactory,
        Squirrel3 $rng, MeritRepository $meritRepository,
        PetRepository $petRepository, ItemRepository $itemRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'betaBug');

        $item = $inventoryRepository->findOneBy([
            'id' => $request->request->getInt('item'),
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        if(!$item)
            throw new PSPNotFoundException("Couldn't find that item!");

        switch($item->getItem()->getName())
        {
            case 'Cooking Buddy': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, $meritRepository->getRandomStartingMerit(), null); break;
            case 'Cooking "Alien"': self::createCookingBuddy($responseService, $em, $petFactory, $rng, $item, $user, MeritRepository::findOneByName($em, MeritEnum::BEHATTED), 'Antenna'); break;
            case 'Sentient Beetle': self::makeBeetleEvil($responseService, $em, $user, $item); break;
            case 'Rainbowsaber': self::makeGlitchedOutRainbowsaber($responseService, $em, $user, $item); break;
            default: throw new PSPInvalidOperationException("The Beta Bug cannot be used on that item!");
        }

        $em->remove($inventory);
        $em->flush();

        return $responseService->success();
    }

    private static function makeBeetleEvil(
        ResponseService $responseService, EntityManagerInterface $em,
        User $user, Inventory $beetle
    )
    {
        $beetle->changeItem(ItemRepository::findOneByName($em, 'EVIL Sentient Beetle'));
        $beetle->addComment($user->getName() . ' introduced a Beta Bug into the Sentient Beetle, turning it EVIL!');

        $responseService->addFlashMessage('Oh dang! Introducing a Beta Bug into the Sentient Beetle turned it EVIL!');
        $responseService->setReloadInventory();
    }

    private static function makeGlitchedOutRainbowsaber(
        ResponseService $responseService, EntityManagerInterface $em,
        User $user, Inventory $rainbowsaber
    )
    {
        $rainbowsaber->changeItem(ItemRepository::findOneByName($em, 'Glitched-out Rainbowsaber'));
        $rainbowsaber->addComment($user->getName() . ' introduced a Beta Bug into the Rainbowsaber, glitching it out!');

        $responseService->addFlashMessage('Oh dang! Introducing a Beta Bug into the Rainbowsaber made it all glitchy!');
        $responseService->setReloadInventory();
    }

    // if this list of names changes, it must also be changed in the front-end (cooking-buddy.component.ts)
    private const COOKING_BUDDY_NAMES = [
        'Asparagus', 'Arugula',
        'Biryani', 'Bisque',
        'Cake', 'Ceviche',
        'Cookie', 'Couscous',
        'Dal',
        'Egg Roll', 'Edamame',
        'Falafel',
        'Gnocchi', 'Gobi', 'Goulash', 'Gumbo',
        'Haggis', 'Halibut', 'Hummus',
        'Kabuli', 'Kebab', 'Kimchi', 'Kiwi', 'Kuli Kuli',
        'Larb',
        'Masala', 'Moose',
        'Pinto', 'Pho', 'Polenta', 'Pudding',
        'Reuben',
        'Schnitzel', 'Shawarma', 'Soba', 'Stew', 'Succotash',
        'Taco', 'Tart',
        'Walnut',
        'Yuzu',
        'Ziti',
    ];

    private static function createCookingBuddy(
        ResponseService $responseService, EntityManagerInterface $em, PetFactory $petFactory, Squirrel3 $rng,
        Inventory $inventoryItem, User $user, Merit $startingMerit, ?string $startingHatItem
    )
    {
        $newPet = $petFactory->createPet(
            $user,
            self::COOKING_BUDDY_NAMES[$inventoryItem->getId() % count(self::COOKING_BUDDY_NAMES)],
            $em->getRepository(PetSpecies::class)->findOneBy([ 'name' => 'Cooking Buddy' ]),
            'd8d8d8', // body
            '236924', // "eyes"
            FlavorEnum::getRandomValue($rng),
            $startingMerit
        );

        if($startingHatItem)
        {
            $inventory = new Inventory();
            $inventory
                ->setOwner($user)
                ->setCreatedBy($user)
                ->setLocation(LocationEnum::WARDROBE)
                ->setItem(ItemRepository::findOneByName($em, $startingHatItem))
                ->setWearer($newPet)
            ;

            $em->persist($inventory);
        }

        $em->remove($inventoryItem);

        $numberOfPetsAtHome = PetRepository::getNumberAtHome($em, $user);

        $petJoinsHouse = $numberOfPetsAtHome < $user->getMaxPets();

        $message = 'The Cooking Buddy starts to shake violently, then shuts down. After a moment of silence, it starts back up again, nuzzles you, and joins the rest of your pets in your house!';

        if(!$petJoinsHouse)
        {
            $newPet->setLocation(PetLocationEnum::DAYCARE);
            $message .= "\n\n(Seeing your house is full, it shortly thereafter wanders to daycare.)";
        }
        else
        {
            $responseService->setReloadPets(true);
        }

        $responseService->addFlashMessage($message);
    }
}