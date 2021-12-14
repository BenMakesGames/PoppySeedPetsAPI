<?php
namespace App\Service;

use App\Entity\DragonHostage;
use App\Enum\DragonHostageTypeEnum;

class DragonHostageService
{
    private IRandom $rng;

    public function __construct(Squirrel3 $rng)
    {
        $this->rng = $rng;
    }

    public function generateHostage(): DragonHostage
    {
        $type = $this->rng->rngNextFromArray(DragonHostageTypeEnum::getValues());

        //$colors = $this->generateHostageColors($type);
        $name = $this->generateHostageName($type);
        $dialog = $this->generateHostageDialog($type);

        return (new DragonHostage())
            ->setType($type)
            ->setAppearance(1) // later, make more appearances: $this->rng->rngNextInt(1, 3)
            /*->setColorA($colors[0])
            ->setColorB($colors[1])*/
            ->setName($name)
            ->setDialog($dialog)
        ;
    }

    public function generateHostageName(string $type): string
    {
        return $this->rng->rngNextFromArray(self::HOSTAGE_NAMES[$type]);
    }

    public function generateHostageDialog(string $type): string
    {
        $dialog = $this->rng->rngNextFromArray(self::HOSTAGE_DIALOG[$type]);

        $outrageous = $this->rng->rngNextFromArray([ 'Outrageous!', 'Unbelievable!', 'Unhand me at once!', 'You _dare?!_' ]);
        $parent = $this->rng->rngNextFromArray([ 'father', 'mother', 'uncle', 'aunt', 'cousins' ]);

        $dialog = str_replace([ '%Outrageous!%', '%parent%' ], [ $outrageous, $parent ], $dialog);

        return $dialog;
    }

    private const HOSTAGE_DIALOG = [
        DragonHostageTypeEnum::MAGPIE => [
            '*squawk!* %Outrageous!% My %parent% will hear of this!',
        ],
        DragonHostageTypeEnum::RACCOON => [
            '%Outrageous!% My %parent% will hear of this!',
        ],
        DragonHostageTypeEnum::SQUID => [
            '%Outrageous!% My %parent% will hear of this!',
        ],
    ];

    private const HOSTAGE_NAMES = [
        DragonHostageTypeEnum::MAGPIE => [
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
        DragonHostageTypeEnum::RACCOON => [
            'Aleksy', 'Andnej', 'Artur', 'Casimir', 'Cyprian', 'Cyryl',
            'Dodek', 'Emmilian', 'Feliks', 'Florian', 'Gerik', 'Janek',
            'Maksym', 'Mikolai', 'Pawelek', 'Pawl', 'Piotr', 'Seweryn',
            'Telek', 'Tola', 'Wicus', 'Wit', 'Ziven',
        ],
        DragonHostageTypeEnum::SQUID => [
            'Abayomi', 'Ain', 'Akiiki', 'Amsi', 'Aswad', 'Azizi',
            'Badru', 'Bebti', 'Chenzira', 'Chisisi', 'Dakarai', 'Dendera',
            'Ebonique', 'Garai', 'Gyasi', 'Hasani', 'Husani', 'Jamila',
            'Kamilah', 'Kamuzu', 'Kasiya', 'Lateef', 'Makalani', 'Muminah',
            'Onuris', 'Safiya', 'Sagira', 'Siti', 'Tehuti', 'Tumaini',
            'Umayma', 'Zaliki',
        ],
    ];
}
