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


namespace App\Service;

use App\Entity\Dragon;
use App\Entity\DragonHostage;
use App\Entity\Item;
use App\Enum\DragonHostageTypeEnum;
use App\Functions\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class DragonHostageService
{
    public function __construct(
        private readonly IRandom $rng,
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function generateHostage(Dragon $dragon): DragonHostage
    {
        $type = $this->rng->rngNextFromArray(DragonHostageTypeEnum::cases());

        $crownColor = $this->rng->rngNextFromArray(self::CrownColors);
        $creatureColor = $this->rng->rngNextFromArray(self::HostageColors[$type->value]);
        $name = $this->generateHostageName($type);
        $dialog = $this->generateHostageDialog();

        return (new DragonHostage())
            ->setDragon($dragon)
            ->setType($type)
            ->setAppearance('1') // later, make more appearances: $this->rng->rngNextInt(1, 3)
            ->setColorA($crownColor)
            ->setColorB($creatureColor)
            ->setName($name)
            ->setDialog($dialog)
        ;
    }

    public function generateHostageName(DragonHostageTypeEnum $type): string
    {
        return $this->rng->rngNextFromArray(self::HostageNames[$type->value]);
    }

    public function generateHostageDialog(): string
    {
        $dialog = $this->rng->rngNextFromArray(self::HostageDialog);

        $complaint = $this->rng->rngNextFromArray([ 'Outrageous! Unfathomable!', 'Fate is so cruel!', 'Is there no justice??' ]);
        $beautiful = $this->rng->rngNextFromArray([ 'beautiful', 'handsome', 'hot', 'dexterous' ]);
        $terrible = $this->rng->rngNextFromArray([ 'terrible', 'cruel', 'vicious', 'great and powerful' ]);

        return str_replace(
            [ '%Complaint!%', '%beautiful%', '%terrible%' ],
            [ $complaint, $beautiful, $terrible ],
            $dialog
        );
    }

    public function generateLoot(DragonHostageTypeEnum $type): DragonHostageLoot
    {
        $item = ItemRepository::findOneByName($this->em, $this->rng->rngNextFromArray(self::HostageLoot[$type->value]));

        return new DragonHostageLoot(
            $item,
            'A member of ' . ucfirst($type->value) . ' royalty dropped this as you shooed them out of your dragon\'s den.',
            'The ' . $type->value . ' made a fuss and tried to hide from you, but you eventually shooed it out of the den. After the ordeal was over, you noticed it dropped ' . $item->getNameWithArticle() . '!'
        );
    }

    private const array HostageColors = [
        DragonHostageTypeEnum::Magpie->value => [ '3d484f', '4e4642', '696969' ],
        DragonHostageTypeEnum::Raccoon->value => [ '' ],
        DragonHostageTypeEnum::Squid->value => [ 'e59db9', 'e7d5b2' ],
    ];

    private const array CrownColors = [
        'a11b1b', '10a913', '7c1ae9', 'db28b2'
    ];

    private const array HostageLoot = [
        DragonHostageTypeEnum::Magpie->value => [
            'Ruby Feather',
            'Black Feathers',
        ],
        DragonHostageTypeEnum::Raccoon->value => [
            'Little Strongbox',
            'Minor Scroll of Riches',
        ],
        DragonHostageTypeEnum::Squid->value => [
            'Scroll of the Sea',
            'Secret Seashell',
        ]
    ];

    private const array HostageDialog = [
        'What\'s this? It appears I\'ve been captured by a %terrible% dragon! %Complaint!% \\*sobs unconvincingly\\*',
        'Oh, how I wish a %beautiful% knight would come and save me! I\'m in such terrible peril, after all!',
        'Help, oh help! A %terrible% dragon has taken me well and truly hostage! I\'m much too young and %beautiful% to die!',
        'Woe is me! Taken hostage in the prime of my life! %Complaint!% Where, oh where, is my %beautiful% knight??'
    ];

    private const array HostageNames = [
        DragonHostageTypeEnum::Magpie->value => [
            'Acel', 'Adalicia', 'Adelaide', 'Adelynn', 'Adrianna', 'Aimee',
            'Alisanne', 'Aloin', 'Alyssandra', 'Amoux', 'Ancil', 'Angela',
            'Archard', 'Armand', 'Avelaine', 'Baylen', 'Beaumont', 'Bellamy',
            'Berangér', 'Boise', 'Bonamy', 'Bowden', 'Burnell', 'Calandre',
            'Cammi', 'Cannan', 'Caressa', 'Carvel', 'Cecille', 'Cendrillon',
            'Cheval', 'Clair', 'Clovis', 'Coralie', 'Cosette', 'D\'arcy',
            'Dartagnan', 'Delphine', 'Delrick', 'Deston', 'Devanna', 'Diamanta',
            'Dorine', 'Dumont', 'Eglantina', 'Fabron', 'Ferrand', 'Fleur',
            'Fontaine', 'Garion', 'Hamlin', 'Hilaire', 'Jay', 'Jolie',
            'Laci', 'Laramie', 'Lavern', 'Leola', 'Leroi', 'Lirienne',
            'Lucia', 'Madalene', 'Maelynn', 'Marceau', 'Marielle', 'Marque',
            'Masselin', 'Merlin', 'Millicent', 'Mirla', 'Morell', 'Musette',
            'Nanon', 'Noeline', 'Orlena', 'Papillon', 'Pascaline', 'Perrin',
            'Romaine', 'Roussel', 'Solaina', 'Violetta'
        ],
        DragonHostageTypeEnum::Raccoon->value => [
            'Aleksy', 'Andnej', 'Artur', 'Casimir', 'Cyprian', 'Cyryl',
            'Dodek', 'Emmilian', 'Feliks', 'Florian', 'Gerik', 'Janek',
            'Maksym', 'Mikolai', 'Pawelek', 'Pawl', 'Piotr', 'Seweryn',
            'Telek', 'Tola', 'Wicus', 'Wit', 'Ziven',
        ],
        DragonHostageTypeEnum::Squid->value => [
            'Abayomi', 'Ain', 'Akiiki', 'Amsi', 'Aswad', 'Azizi',
            'Badru', 'Bebti', 'Chenzira', 'Chisisi', 'Dakarai', 'Dendera',
            'Ebonique', 'Garai', 'Gyasi', 'Hasani', 'Husani', 'Jamila',
            'Kamilah', 'Kamuzu', 'Kasiya', 'Lateef', 'Makalani', 'Muminah',
            'Onuris', 'Safiya', 'Sagira', 'Siti', 'Tehuti', 'Tumaini',
            'Umayma', 'Zaliki',
        ],
    ];
}

class DragonHostageLoot
{
    public Item $item;
    public string $comment;
    public string $flashMessage;

    public function __construct(Item $item, string $comment, string $flashMessage)
    {
        $this->item = $item;
        $this->comment = $comment;
        $this->flashMessage = $flashMessage;
    }
}