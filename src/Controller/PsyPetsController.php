<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @method null|User getUser()
 */
abstract class PsyPetsController extends AbstractController
{
    public function adminIPsOnly(Request $request)
    {
        if(!preg_match('/' . $this->getParameter('adminIpRegex') . '/', $request->getClientIp()))
            throw new AccessDeniedHttpException('Access denied.');
    }
}