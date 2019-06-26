<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Entity\PetSkills;
use App\Entity\User;
use App\Enum\SerializationGroup;
use App\Functions\ArrayFunctions;
use App\Repository\PetSpeciesRepository;
use App\Repository\UserRepository;
use App\Service\Filter\UserFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/florist")
 */
class FloristController extends PsyPetsController
{
    private const ITEMS_FOR_SALE = [
        'Agrimony' => 10,
        'Bird\'s-foot Trefoil' => 10,
        'Coriander' => 10,
        'Green Carnation' => 10,
        'Iris' => 10,
        'Purple Violet' => 10,
        'Red Clover' => 10,
        'Viscaria' => 10,
        'Witch-hazel' => 10,
        'Wheat' => 10,
    ];

    /**
     * @Route("/send", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function send(Request $request)
    {
        $flower = $request->request->get('flower');
        $recipientId = $request->request->get('recipient');


    }

}
