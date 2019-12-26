<?php
namespace App\Service;

use App\Entity\Beehive;
use App\Entity\User;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class BeehiveService
{
    private $em;
    private $itemRepository;

    public const DESIRED_ITEMS = [
        'Red Clover' => 18,
        'Wheat Flower' => 18,
        'Orange' => 12,
        'Apricot' => 24,
        'Red' => 12,
        'Naner' => 16,
        'Crooked Stick' => 8,
        'Witch-hazel' => 18,
        'Narcissus' => 8,
        'Yellow Dye' => 12,
        'Honeydont' => 36,
        'Sunflower' => 12,
        'Bean Milk' => 24,
        'Creamy Milk' => 12,
    ];

    public function __construct(EntityManagerInterface $em, ItemRepository $itemRepository)
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;
    }

    public function createBeehive(User $user)
    {
        if($user->getBeehive())
            throw new \InvalidArgumentException('User already has a beehive!');

        $beehive = (new Beehive())
            ->setQueenName(ArrayFunctions::pick_one(self::QUEEN_NAMES))
            ->setRequestedItem($this->itemRepository->findOneByName(array_rand(self::DESIRED_ITEMS)))
        ;

        $this->em->persist($beehive);

        $user->setBeehive($beehive);
    }

    public function fedRequestedItem(Beehive $beehive)
    {
        $beehive->setFlowerPower(self::DESIRED_ITEMS[$beehive->getRequestedItem()->getName()]);

        $this->rerollRequest($beehive);
    }

    public function reRollRequest(Beehive $beehive)
    {
        // get the current item
        $requestedItem = $beehive->getRequestedItem()->getName();

        // remove the current item from the list of possibilities
        $possibleItems = self::DESIRED_ITEMS;
        unset($possibleItems[$requestedItem]);

        // pick a new requested item
        $beehive->setRequestedItem($this->itemRepository->findOneByName(array_rand($possibleItems)));
    }

    // a couple of these are princesses; sorry about the non-semantic variable name:
    public const QUEEN_NAMES = [
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