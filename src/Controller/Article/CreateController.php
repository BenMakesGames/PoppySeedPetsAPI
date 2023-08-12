<?php
namespace App\Controller\Article;

use App\Controller\AdminController;
use App\Entity\Article;
use App\Exceptions\PSPFormValidationException;
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
class CreateController extends AdminController
{
    /**
     * @DoesNotRequireHouseHours()
     * @Route("", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function createNew(
        Request $request, ResponseService $responseService, EntityManagerInterface $em,
        DesignGoalRepository $designGoalRepository
    )
    {
        $this->adminIPsOnly($request);

        $title = trim($request->request->get('title', ''));
        $body = trim($request->request->get('body', ''));
        $imageUrl = trim($request->request->get('imageUrl', ''));

        if($title === '' || $body === '')
            throw new PSPFormValidationException('title and body are both required.');

        if(\mb_strlen($title) > 255)
            throw new PSPFormValidationException('title may not be longer than 255 characters.');

        $designGoals = $designGoalRepository->findByIdsFromParameters($request->request, 'designGoals');

        $article = (new Article())
            ->setImageUrl($imageUrl == '' ? null : $imageUrl)
            ->setTitle($title)
            ->setBody($body)
            ->setAuthor($this->getUser())
        ;

        foreach($designGoals as $designGoal)
            $article->addDesignGoal($designGoal);

        $em->persist($article);
        $em->flush();

        $em->createQuery('UPDATE App\Entity\User u SET u.unreadNews=u.unreadNews+1')->execute();

        return $responseService->success();
    }
}
