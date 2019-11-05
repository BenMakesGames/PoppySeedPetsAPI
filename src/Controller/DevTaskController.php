<?php
namespace App\Controller;

use App\Entity\DevTask;
use App\Enum\DevTaskStatusEnum;
use App\Enum\DevTaskTypeEnum;
use App\Enum\SerializationGroupEnum;
use App\Repository\DevTaskRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/devTask")
 */
class DevTaskController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function getTasks(ResponseService $responseService, DevTaskRepository $devTaskRepository)
    {
        $tasks = $devTaskRepository->findBy([
            'status' => [ DevTaskStatusEnum::IN_DEVELOPMENT, DevTaskStatusEnum::SELECTED_FOR_DEVELOPMENT, DevTaskStatusEnum::IN_TEST ]
        ]);

        return $responseService->success($tasks, SerializationGroupEnum::DEV_TASK);
    }

    /**
     * @Route("/search", methods={"GET"})
     */
    public function searchTasks(ResponseService $responseService, DevTaskRepository $devTaskRepository, Request $request)
    {
        // TODO
        $tasks = [];

        return $responseService->success($tasks, SerializationGroupEnum::DEV_TASK);
    }

    /**
     * @Route("/{task}/changeStatus", methods={"PATCH"}, requirements={"task"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function updateTask(ResponseService $responseService, DevTask $task, Request $request, EntityManagerInterface $em)
    {
        $status = $request->request->getInt('status', -1);

        if(!DevTaskStatusEnum::isAValue($status))
            throw new UnprocessableEntityHttpException('the status given is not a valid task status.');

        $task->setStatus($status);

        $em->flush();

        return $responseService->success();
    }

    /**
     * @Route("", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function newTask(ResponseService $responseService, Request $request, EntityManagerInterface $em)
    {
        $title = trim($request->request->get('title', ''));
        $description = trim($request->request->get('description', ''));
        $type = $request->request->getInt('type', -1);

        if(!DevTaskTypeEnum::isAValue($type))
            throw new UnprocessableEntityHttpException('the type given is not a valid task type.');

        $task = (new DevTask())
            ->setTitle($title)
            ->setDescription($description)
            ->setType($type)
        ;

        $em->persist($task);
        $em->flush();

        return $responseService->success($task, SerializationGroupEnum::DEV_TASK);
    }

    /**
     * @Route("/{task}", methods={"PATCH"}, requirements={"task"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function editTask(ResponseService $responseService, Request $request, EntityManagerInterface $em, DevTask $task)
    {
        $title = trim($request->request->get('title', ''));
        $description = trim($request->request->get('description', ''));
        $type = $request->request->getInt('type', -1);

        if(!DevTaskTypeEnum::isAValue($type))
            throw new UnprocessableEntityHttpException('the type given is not a valid task type.');

        $task
            ->setTitle($title)
            ->setDescription($description)
            ->setType($type)
        ;

        $em->flush();

        return $responseService->success($task, SerializationGroupEnum::DEV_TASK);
    }
}
