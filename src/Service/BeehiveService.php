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

use App\Entity\Beehive;
use App\Entity\User;
use App\Functions\CalendarFunctions;
use App\Functions\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class BeehiveService
{
    public const array DesiredItems = [
        'Red Clover' => 18,
        'Wheat Flower' => 18,
        'Orange' => 12,
        'Apricot' => 24,
        'Red' => 12,
        'Naner' => 16,
        'Witch-hazel' => 18,
        'Narcissus' => 8,
        'Honeydont' => 36,
        'Sunflower' => 12,
        'Bean Milk' => 24,
        'Creamy Milk' => 12,
    ];

    public const array AlternateDesiredItems = [
        'Slice of Bread' => 8,
        'Really Big Leaf' => 16,
        'Corn' => 10,
        'Meringue' => 12,
        'Onion' => 8,
        'Music Note' => 16,
        'Sweet Roll' => 20,
        'Chanterelle' => 8,
        'Large Bag of Fertilizer' => 40,
        'Sweet Beet' => 12,
        'Smallish Pumpkin' => 24,
        'Potato' => 10,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly IRandom $rng
    )
    {
    }

    public function createBeehive(User $user): void
    {
        if($user->getBeehive())
            throw new \Exception('Player #' . $user->getId() . ' already has a beehive!');

        $beehive = new Beehive(
            user: $user,
            name: $this->rng->rngNextFromArray(self::QueenNames),
            requestedItem: ItemRepository::findOneByName($this->em, array_rand(self::DesiredItems)),
            alternateRequestedItem: ItemRepository::findOneByName($this->em, array_rand(self::AlternateDesiredItems))
        );

        $this->em->persist($beehive);

        $user->setBeehive($beehive);
    }

    public function fedRequestedItem(Beehive $beehive, bool $feedAlternate): void
    {
        if($feedAlternate)
        {
            $item = $beehive->getAlternateRequestedItem();
            $power = self::AlternateDesiredItems[$item->getName()];
        }
        else
        {
            $item = $beehive->getRequestedItem();
            $power = self::DesiredItems[$item->getName()];
        }

        $beehive->setFlowerPower($power);

        $this->rerollRequest($beehive);
    }

    public function reRollRequest(Beehive $beehive): void
    {
        // get the current items
        $requestedItem = $beehive->getRequestedItem()->getName();
        $altRequestedItem = $beehive->getAlternateRequestedItem()->getName();

        // remove the current item from the list of possibilities
        $possibleItems = self::DesiredItems;
        unset($possibleItems[$requestedItem]);

        $possibleAltItems = self::AlternateDesiredItems;
        unset($possibleAltItems[$altRequestedItem]);

        if(CalendarFunctions::isApricotFestival(new \DateTimeImmutable()))
            $possibleItems = [ 'Apricot' => 1 ];

        // pick a new requested item
        $beehive
            ->setRequestedItem(ItemRepository::findOneByName($this->em, array_rand($possibleItems)))
            ->setAlternateRequestedItem(ItemRepository::findOneByName($this->em, array_rand($possibleAltItems)))
        ;
    }

    // a couple of these are princesses; sorry about the non-semantic variable name:
    public const array QueenNames = [
        'Acropolitissa', 'Adelaide', 'Adélina', 'Adosinda', 'Ædgyth', 'Ælfthryth', 'Aénor', 'Afzan', 'Agafiya',
        'Allogia', 'Amalia', 'Anglesia', 'Andregoto', 'Anka', 'Ansi', 'Aphainuchit', 'Aregund', 'Aremburga',
        'Argentaela', 'Argyra', 'Ashina', 'Aspasia', 'Astrid', 'Aud', 'Austerchild',

        'Babukhan', 'Bainun', 'Bao Si', 'Barbara', 'Bartolomea', 'Bé Fáil', 'Berengaria', 'Bertechildis', 'Bian',
        'Bilichild', 'Biltrude', 'Blanche', 'Blotstulka', 'Bonne', 'Božena', 'Brynhildr',

        'Charito', 'Cheng\'ai', 'Chengmu', 'Chen Jiao', 'Chittrawadi', 'Chlothsind', 'Cixilo', 'Claudia', 'Claudine',
        'Clementia', 'Clotilde', 'Cunigunde', 'Cymburgis',

        'Danashiri', 'Darejan', 'Darinka', 'Dauphine', 'Debsirindra', 'Doubravka', 'Draginja', 'Drahomíra',
        'Dubchoblaig', 'Dunlaith',

        'Eilika', 'Eindama', 'Eishō', 'Eithne', 'Ekaterina', 'Elisenda', 'Elvira', 'Emilienne', 'Ermengarde',
        'Ermesinde', 'Erzhu', 'Eschive', 'Esclaramunda', 'Ethelinde', 'Eudokia', 'Euphrosyne', 'Eupraxia', 'Eustachie',

        'Faileube', 'Fara', 'Fastrada', 'Fauziah', 'Findelb', 'Folchiade', 'Françoise', 'Froiliuba', 'Frozza',

        'Galswintha', 'Garsinda', 'Genmei', 'Gerperga', 'Ghadana', 'Gisela', 'Goiswintha', 'Gomentrude', 'Gongsi',
        'Gormflaith', 'Go-Sakuramachi', 'Grimhild', 'Grzymislawa', 'Guanglie', 'Gulkhana', 'Gunhilda', 'Gyrid',

        'Haminah', 'Hanthawaddy', 'Hedwig', 'Hellicha', 'Helvis', 'Hildegard', 'Hiltrud', 'Hortense', 'Huansi', 'Huyan',

        'Ikbal', 'Imma', 'Immilla', 'Iñiguez', 'Ingeborg', 'Inoe', 'Isabelle',

        'Jadwiga', 'Jemaah', 'Jiāng', 'Jing', 'Jiajak', 'Jigda-Khatun', 'Jimena', 'Jingū', 'Jitō', 'Joan', 'Juana',
        'Junshi', 'Jutta',

        'Kamāmalu', 'Kantakouzena', 'Kapi\'olani', 'Katranide', 'Ketevan', 'Kezuhun', 'Khongirad', 'Khorashan',
        'Kira Maria', 'Kōgyoku', 'Konchaka-Agafia', 'Kōken', 'Komnina', 'Kujava', 'Kunigunda', 'Kurshiah',

        'Li', 'Lingsi', 'Liutperga', 'Ljubica', 'Lotitia', 'Ludmilla', 'Lutgard', 'Lü Zhi',

        'Maddalena', 'Maedhbh', 'Máel Muire', 'Manisanda', 'Marcatrude', 'Marguerite', 'Marmohec', 'Marozia',
        'Mathesuentha', 'Mechtild', 'Meishō', 'Melisende', 'Messalina', 'Milica', 'Mingdao', 'Mingyuan', 'Minkhaung',
        'Min Pyan', 'Monomachina', 'Morphia', 'Mù', 'Muirenn', 'Murong', 'Musbah', 'Muzhang', 'Myauk',

        'Najihah', 'Nambui', 'Nanmadaw', 'Nanthild', 'Nestan-Darejan',

        'Oljath', 'Oreguen', 'Órlaith', 'Ostrogotha', 'Ota', 'Otehime',

        'Phannarai', 'Phokaina', 'Phuntsho', 'Piroska', 'Plaisance', 'Polyxena', 'Poppaea', 'Prathuma', 'Prisca',
        'Pwadawgyi',

        'Qiang',

        'Rachanurak', 'Radnashiri', 'Regelinda', 'Regintrude', 'Renata', 'Richardis', 'Richenza', 'Rogneda', 'Roscille',
        'Rusudan', 'Ryksa',

        'Sagdukht', 'Sallustia', 'Sālote', 'Sancha', 'Sangwan', 'Sawatdi', 'Saw Sala', 'Saw Thanda', 'Saw Yin', 'Seishi',
        'Shi', 'Shin Saw', 'Shunlie', 'Sibylla', 'Sigrid', 'Sineenat', 'Siti Aishah', 'Smiltsena', 'Soe Min', 'Song',
        'Statilia', 'Suavegotha', 'Suiko', 'Supayagyi', 'Suriyawongsa', 'Synadena', 'Swanhilde', 'Świętosława',

        'Tai Si', 'Taiwu', 'Taung', 'Teri\'itaria', 'Tetua', 'Thanbula', 'Theodelinda', 'Thermantia', 'Thiri Thuriya',
        'Thonlula', 'Thukomma', 'Thupaba', 'Thyra', 'Tōchi', 'Tove', 'Tsundue',

        'Ulanara', 'Ulvhild', 'Urraca', 'Usaukpan',

        'Vénérande', 'Violant', 'Viridis', 'Vitača', 'Voisava',

        'Wadanthika', 'Waldrada', 'Weluwaddy', 'Wilhelmina', 'Wisigard', 'Wisutkasat', 'Wizala', 'Wu', 'Wulfefundis',
        'Wulfhilde', 'Wuwei', 'Wuxiao', 'Wyszesława',

        'Xia', 'Xianlie', 'Xianmu', 'Xianwen', 'Xiaocheng', 'Xiaojing', 'Xin', 'Xu', 'Xunying',

        'Yadana', 'Yadanabon', 'Yaroslavna', 'Yaza Dewi', 'Yazakumari', 'Yin', 'Yixian', 'Yolanda', 'Yuanfei', 'Yujiulü',
        'Yukiko',

        'Zanariah', 'Zbyslava', 'Zhang', 'Zhangde', 'Zhaoxin', 'Zhejue',
    ];
}
