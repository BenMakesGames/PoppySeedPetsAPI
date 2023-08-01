<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230801110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A round body with four protruding... limbs? At least two appear to be used as hands; the others might be ears... or maybe they\'re all hands?? Or maybe they\'re all ears??!?" WHERE `id`=13;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="It has a squarish body topped with two, pointed ears. Perhaps its most distinguishing feature, however, is the bulls-eye-shaped coloration on its stomach. It walks upright on its two legs, which are even shorter than its two small arms." WHERE `id`=50;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="The Bulbun has a pear-shaped body topped with two, long ears, which it keeps upright except when hiding. It has a puff-ball tail, two short legs, and two arms which it keeps tucked underneath its long fur." WHERE `id`=15;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="It has a tallish, ovaloid body with a star-shaped marking prominently displayed on its stomach. Its small head is topped with a mess of hair, and two long, drooping, pointed ears. The short fur on the outside of its ears matches the shaggy fur of its nubbin tail. It has four, short legs, but when it sits, its legs are hidden under its body, much like a loafing cat\'s." WHERE `id`=25;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `description`="The sky in the center of the Hollow Earth is host to an entire ecosystem, including several species of whale. One of the most populous species - and the only one suitable to keep as a pet - is the Dwarf Sky Whale.\n\nDespite having no wings, and a single, small tailfin, it\'s somehow able to lift its body upwards. It only manages a low hover here, but in the Hollow Earth it soars." WHERE `id`=72;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="You could mistake it for a common fish - like maybe one of those puffy-lipped ones - except it\'s got those long, stick legs!" WHERE `id`=8;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="Giraffe-shaped, with medium-long legs and neck, pointed ossicones, and, of course, the distinctive, brightly-colored bulb just above its butt." WHERE `id`=53;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A giraffe with buffalo-like antlers, a long, striped tail, and a polka dot-patterned coat." WHERE `id`=19;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="It stands upright, looking like a squat little goblin, with short, pointed ears, and long, floppy arms. Its mouth is hidden behind a long, striped, hairless snout that reveals the Glyryvyru\'s colorful skin." WHERE `id`=75;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A metal box for a body; a smaller metal box for a head. It uses its four wheels to get around, and the articulated grabby arm on the top of its head to hold things." WHERE `id`=39;');
        // ^ 10

        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A slime blop with a single arm extending from its top. It has piercing eyes that are visible even in the darkest environments." WHERE `id`=80;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="The Hatoful resembles many owls, though this is due to convergent evolution rather than shared ancestry. The arrangement of its feathers give its head and face the heart shape it\'s famous for." WHERE `id`=17;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A hedgehog. It\'s not going _anywhere_ fast with those little legs, though." WHERE  `id`=41;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A miniature species of elephant. It has a long snout, and short, pointed ears. Like homo sapiens it has a coccyx at the bottom of its spine; a remnant of its tailed ancestors. It owes its name to its hairless, and remarkably-pliable skin." WHERE `id`=35;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="Its body is a tulip-like flower turned upside-down; long legs poke out from beneath its petals, barely visible. The stem on the top of its head varies in length across individuals, depending on where exactly they broke off from their mother plant." WHERE `id`=56;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A sphere with two tiny legs, and two pointed ears. Its eyes are set far apart on its body. The fur on the top of its head has a striped pattern, which helps it camouflage in the dry bushes of its native territory." WHERE `id`=74;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A large, flightless bird, reminiscent of the emu or ostrich, but with a thicker neck, and shorter wings. The feathers on the back of its head are as long as its tailfeathers. Much more subdued than a peacock, they give an almost elegant, or regal look to the creature." WHERE `id`=47;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="Bear with me for a sec; this one is weird to describe. Take a squat \"J\", and rotate it 180 degrees... what was the tail of the \"J\" is the Noucan\'s bill, which curves downwards. Its long face curves seamlessly into its body, which has stick arms instead of wings, and is completed with equally-stick-ish legs. The top of its head has three feathers that stick up and out from the rest." WHERE `id`=70;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A small ball with smaller wings and even smaller feet.\n\nWait, this is a bird, right? Where is its bill??" WHERE `id`=6;');
        $this->addSql('UPDATE `poppyseedpets`.`pet_species` SET `physical_description`="A horse head whose neck quickly tapers off into a tail. It\'s carried by its two large, leathery bat wings." WHERE `id`=30;');
        // ^ 20
    }

    public function down(Schema $schema): void
    {
    }
}
