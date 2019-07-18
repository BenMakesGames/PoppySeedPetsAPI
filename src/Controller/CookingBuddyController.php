<?php
namespace App\Controller;

use App\Enum\SerializationGroupEnum;
use App\Service\Filter\KnownRecipesFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/cookingBuddy")
 */
class CookingBuddyController extends PsyPetsController
{
    /**
     * @Route("", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getKnownRecipes(
        KnownRecipesFilterService $knownRecipesFilterService, InventoryService $inventoryService, Request $request,
        ResponseService $responseService
    )
    {
        $user = $this->getUser();

        if($inventoryService->countInventory($user, 'Cooking Buddy') === 0)
            throw new NotFoundHttpException();

        $knownRecipesFilterService->addRequiredFilter('user', $user->getId());

        $results = $knownRecipesFilterService->getResults($request->request);

        return $responseService->success($results, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::KNOWN_RECIPE ]);
    }
}