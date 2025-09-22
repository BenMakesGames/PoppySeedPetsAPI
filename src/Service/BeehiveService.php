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
use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use Doctrine\ORM\EntityManagerInterface;

class BeehiveService
{
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
        );

        $this->em->persist($beehive);

        $user->setBeehive($beehive);
    }

    /**
     * @return Inventory[]
     */
    public function findFlowers(User $user, ?array $inventoryIds = null): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('i')->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location IN (:home)')
            ->leftJoin('i.item', 'item')
            ->leftJoin('item.food', 'food')
            ->andWhere($qb->expr()->orX(
                'food.floral>0',
                'food.fruity>0',
                'food.planty>0'
            ))
            ->addOrderBy('item.name', 'ASC')
            ->setParameter('owner', $user->getId())
            ->setParameter('home', LocationEnum::Home)
        ;

        if($inventoryIds)
        {
            $qb
                ->andWhere('i.id IN (:inventoryIds)')
                ->setParameter('inventoryIds', $inventoryIds)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public static function computeFlowerPower(Inventory $i): int
    {
        return
            $i->getItem()->getFood()->getFloral() * 8 +
            $i->getItem()->getFood()->getPlanty() * 4 +
            $i->getItem()->getFood()->getFruity() * 4 +
            $i->getItem()->getFood()->getLove() * 2 +
            (
                $i->getSpice()?->getEffects() === null ? 0 : (
                    $i->getSpice()->getEffects()->getFloral() * 8 +
                    $i->getSpice()->getEffects()->getPlanty() * 4 +
                    $i->getSpice()->getEffects()->getFruity() * 4 +
                    $i->getSpice()->getEffects()->getLove() * 2
                )
            )
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
