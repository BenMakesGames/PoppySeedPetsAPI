<?php
namespace App\Controller\Article;

use App\Controller\AdminController;
use App\Entity\Article;
use App\Entity\DesignGoal;
use App\Exceptions\PSPFormValidationException;
use App\Functions\ArrayFunctions;
use App\Repository\DesignGoalRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Annotations\DoesNotRequireHouseHours;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
* @Route("/article")
*/
class UpdateController extends AdminController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("/{article}", methods={"POST"}, requirements={"article"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function handle(
        Article $article, ResponseService $responseService, Request $request, EntityManagerInterface $em,
        DesignGoalRepository $designGoalRepository
    )
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->get('title', ''));
        $body = trim($request->request->get('body', ''));
        $imageUrl = trim($request->request->get('imageUrl', ''));

        if($title === '' || $body === '')
            throw new PSPFormValidationException('title and body are both required.');

        $designGoals = $designGoalRepository->findByIdsFromParameters($request->request, 'designGoals');

        $article
            ->setImageUrl($imageUrl == '' ? null : $imageUrl)
            ->setTitle($title)
            ->setBody($body)
        ;

        $currentDesignGoals = $article->getDesignGoals()->toArray();
        $designGoalsToAdd = ArrayFunctions::except($designGoals, $currentDesignGoals, fn(DesignGoal $dg) => $dg->getId());
        $designGoalsToRemove = ArrayFunctions::except($currentDesignGoals, $designGoals, fn(DesignGoal $dg) => $dg->getId());

        foreach($designGoalsToRemove as $toRemove)
            $article->removeDesignGoal($toRemove);

        foreach($designGoalsToAdd as $toAdd)
            $article->addDesignGoal($toAdd);

        $em->flush();

        return $responseService->success();
    }
}
