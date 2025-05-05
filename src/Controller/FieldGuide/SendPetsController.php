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

namespace App\Controller\FieldGuide;

use App\Entity\FieldGuideEntry;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\UserFieldGuideEntry;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetLocationEnum;
use App\Exceptions\PSPFormValidationException;
use App\Exceptions\PSPNotFoundException;
use App\Model\ComputedPetSkills;
use App\Service\PetActivity\FieldGuide\CosmicGoat;
use App\Service\PetActivity\FieldGuideAdventureService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;

#[Route("/fieldGuide")]
class SendPetsController
{
    #[Route("/sendPets", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function sendPets(
        #[MapRequestPayload] SendPetsRequest $request,

        UserAccessor $userAccessor, EntityManagerInterface $em, ResponseService $responseService,
        FieldGuideAdventureService $fieldGuideAdventureService,
    )
    {
        $user = $userAccessor->getUserOrThrow();

        /** @var UserFieldGuideEntry $fieldGuideEntry */
        $fieldGuideEntry = $em->getRepository(UserFieldGuideEntry::class)
            ->createQueryBuilder('e')
            ->leftJoin('e.entry', 'f')
            ->andWhere('e.user = :user')
            ->andWhere('f.name = :entryName')
            ->setParameter('user', $user->getId())
            ->setParameter('entryName', $request->entry)
            ->getQuery()
            ->getOneOrNullResult()
            ?? throw new PSPNotFoundException('Field Guide entry not found.');

        $requirements = $fieldGuideEntry->getEntry()->getActionRequirements()
            ?? throw new PSPFormValidationException('That Field Guide entry does not represent something that pets can visit.');

        $selectedPets = array_map(
            fn(Pet $p) => $p->getComputedSkills(),
            $em->getRepository(Pet::class)->findBy([
                'id' => $request->petIds,
                'owner' => $user,
                'location' => PetLocationEnum::HOME,
            ])
        );

        if(count($selectedPets) < count($request->petIds))
            throw new PSPNotFoundException('Some pets not found.');

        $keyItems = $em->createQueryBuilder()
            ->from(Inventory::class, 'inventory')
            ->select('DISTINCT(item.name)')
            ->join(Item::class, 'item')
            ->andWhere('inventory.owner=:user')
            ->andWhere('inventory.location=:location')
            ->andWhere('item.name IN (:items)')
            ->setParameter('user', $user->getId())
            ->setParameter('location', LocationEnum::HOME)
            ->setParameter('items', [
                'Submarine'
            ])
            ->getQuery()
            ->getSingleColumnResult()
        ;

        if(!array_all($selectedPets, fn(ComputedPetSkills $pet) => self::petMeetsRequirements($pet, $keyItems, $requirements)))
            throw new PSPFormValidationException('Some pets do not meet the requirements.');

        $results = $fieldGuideAdventureService->adventure($fieldGuideEntry, $selectedPets);

        return $responseService->success([
            'message' => $results->message,
            'loot' => array_map(
                fn(Inventory $i) => [
                    'id' => $i->getItem()->getId(),
                    'name' => $i->getItem()->getName(),
                    'image' => $i->getItem()->getImage(),
                ],
                $results->loot
            )
        ]);
    }

    /**
     * @param string[] $keyItems
     * @param string[] $requirements
     */
    private static function petMeetsRequirements(ComputedPetSkills $petWithSkills, array $keyItems, array $requirements): bool
    {
        if($petWithSkills->getPet()->getHouseTime()->getActionPoints() < 1)
            return false;

        foreach($requirements as $requirement)
        {
            switch($requirement)
            {
                case 'access to the Umbra':
                    if(
                        !$petWithSkills->getPet()->hasMerit(MeritEnum::NATURAL_CHANNEL) &&
                        $petWithSkills->getPet()->getHallucinogenLevel() === 'none' &&
                        $petWithSkills->getPet()->getTool()?->getItem()->getTool()->getAdventureDescription() !== 'The Umbra' &&
                        $petWithSkills->getPet()->getTool()?->getEnchantment()?->getEffects()->getAdventureDescription() !== 'The Umbra'
                    )
                    {
                        return false;
                    }
                    break;

                case 'access to Project-E':
                    if(
                        !$petWithSkills->getPet()->hasMerit(MeritEnum::PROTOCOL_7) &&
                        $petWithSkills->getPet()->getTool()?->getItem()->getTool()->getAdventureDescription() !== 'Project-E' &&
                        $petWithSkills->getPet()->getTool()?->getEnchantment()?->getEffects()->getAdventureDescription() !== 'Project-E'
                    )
                    {
                        return false;
                    }
                    break;

                case 'a Chocolate Key':
                    if($petWithSkills->getPet()->getTool()->getItem()->getName() !== 'Chocolate Key')
                        return false;
                    break;

                case 'access to the deep sea':
                    if(!in_array('Submarine', $keyItems))
                        return false;
                    break;

                case 'protection from heat':
                    if(!$petWithSkills->getHasProtectionFromHeat())
                        return false;
                    break;
            }
        }

        return true;
    }
}

final class SendPetsRequest
{
    #[Assert\Count(min: 1, max: 4)]
    public array $petIds = [];

    #[Assert\NotBlank]
    public string $entry;
}