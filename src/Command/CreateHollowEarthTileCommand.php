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


namespace App\Command;

use App\Entity\HollowEarthTileCard;
use App\Entity\HollowEarthTileType;
use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Functions\ArrayFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class CreateHollowEarthTileCommand extends PoppySeedPetsCommand
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:create-hollow-earth-tile')
            ->setDescription('Create a Hollow Earth tile, including item.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the tile to create.')
        ;
    }

    protected function doCommand(): int
    {
        $tileTypes = $this->em->getRepository(HollowEarthTileType::class)->createQueryBuilder('t')
            ->select('t')
            ->andWhere('t.name != :fixed')
            ->setParameter('fixed', 'Fixed')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->execute();

        $tileTypeNames = array_map(fn(HollowEarthTileType $t) => $t->getName(), $tileTypes);

        $tile = new HollowEarthTileCard();
        $item = new Item();

        $item
            ->setHollowEarthTileCard($tile)
            ->setFuel(30)
            ->setRecycleValue(1)
        ;

        $name = $this->askName();

        $tile->setName($name);
        $item->setName('Tile: ' . $name);

        $type = $this->askChoice('Type', $tileTypeNames, null);

        $tileType = ArrayFunctions::find_one($tileTypes, fn(HollowEarthTileType $t) => $t->getName() === $type);

        $tile->setType($tileType);

        $image = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');

        $image = trim($this->askString('Image, without leading `tile/`', $image));

        $tile->setImage($image);
        $item->setImage('tile/' . $image);

        $rarity = $this->askChoice('Rarity', [ 'Common', 'Uncommon', 'Rare' ], null);

        if($rarity === 'Common')
            $item->setMuseumPoints(1);
        else if($rarity === 'Uncommon')
            $item->setMuseumPoints(5);
        else
            $item->setMuseumPoints(15);

        $itemGroup = $this->em->getRepository(ItemGroup::class)->findOneBy([ 'name' => 'Hollow Earth Booster Pack: ' . $rarity ]);

        if(!$itemGroup)
            throw new \Exception('Item group for that rarity not found.');

        $item->addItemGroup($itemGroup);

        $tile->setEvent([]);

        $this->em->persist($tile);
        $this->em->persist($item);

        $this->em->flush();

        $this->output->writeln('Item and tile created successfully!');
        $this->output->writeln('');
        $this->output->writeln('Run `php bin/console app:update-hollow-earth-tile-event "' . $tile->getName() . '"` to create a basic event.');

        return self::SUCCESS;
    }

    private function askName(): string
    {
        $name = trim($this->input->getArgument('name'));

        while(true)
        {
            $name = trim($this->askString('Name', $name));

            $nameInUse = false;

            if($this->em->getRepository(HollowEarthTileCard::class)->findOneBy([ 'name' => $name ]))
            {
                $this->output->writeln('A tile with the name "' . $name . '" already exists.');
                $nameInUse = true;
            }

            if($this->em->getRepository(Item::class)->findOneBy([ 'name' => 'Tile: ' . $name ]))
            {
                $this->output->writeln('An item with the name "Tile: ' . $name . '" already exists.');
                $nameInUse = true;
            }

            if(!$nameInUse)
                return $name;
        }
    }
}
