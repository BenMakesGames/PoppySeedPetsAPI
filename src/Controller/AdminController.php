<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AdminController extends AbstractController
{
    public function adminIPsOnly(Request $request)
    {
        if(!preg_match('/' . $this->getParameter('adminIpRegex') . '/', $request->getClientIp()))
            throw new AccessDeniedHttpException('Sorry: the device you\'re using is not trusted to perform administrative actions.');
    }
}