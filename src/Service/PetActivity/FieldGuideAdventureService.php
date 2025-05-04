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

namespace App\Service\PetActivity;

use App\Entity\Inventory;
use App\Entity\UserFieldGuideEntry;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Service\PetActivity\FieldGuide\Abandondero;
use App\Service\PetActivity\FieldGuide\Argopelter;
use App\Service\PetActivity\FieldGuide\CosmicGoat;
use App\Service\PetActivity\FieldGuide\HugeToad;
use App\Service\PetActivity\FieldGuide\IleVolcan;
use App\Service\PetActivity\FieldGuide\OnionBoy;
use App\Service\PetActivity\FieldGuide\ShipwreckedFleet;
use App\Service\PetActivity\FieldGuide\Whales;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldGuideAdventureService
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @param ComputedPetSkills[] $petsWithSkills
     */
    public function adventure(UserFieldGuideEntry $fieldGuideEntry, array $petsWithSkills): FieldGuideAdventureResults
    {
        $handler = $this->container->get(match($fieldGuideEntry->getEntry()->getName())
        {
            'Abandondero' => Abandondero::class,
            'Argopelter' => Argopelter::class,
            'Cosmic Goat' => CosmicGoat::class,
            'Huge Toad' => HugeToad::class,
            'Île Volcan' => IleVolcan::class,
            'Onion Boy' => OnionBoy::class,
            'Shipwrecked Fleet' => ShipwreckedFleet::class,
            'Whales' => Whales::class,
        });

        $petChanges = [];

        foreach($petsWithSkills as $pet)
        {
            $pet->getPet()->getHouseTime()->increaseActionPointsSpent(1);
            $petChanges[$pet->getPet()->getId()] = new PetChanges($pet->getPet());
        }

        /** @var FieldGuideAdventureResults $results */
        $results = $handler->adventure($fieldGuideEntry->getUser(), $petsWithSkills);

        $tags = PetActivityLogTagHelpers::findByNames($this->em, $results->tags);

        foreach($petsWithSkills as $pet)
        {
            $log = PetActivityLogFactory::createReadLog($this->em, $pet->getPet(), $results->message)
                ->addTags($tags)
                ->setChanges($petChanges[$pet->getPet()->getId()])
            ;

            foreach($results->loot as $loot)
                $log->addCreatedItem($loot->getItem());
        }

        return $results;
    }
}

#[Exclude]
final class FieldGuideAdventureResults
{
    public function __construct(
        public readonly string $message,

        /** @var string[] */
        public readonly array $tags,

        /** @var Inventory[] */
        public readonly array $loot
    )
    {

    }
}