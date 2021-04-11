<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/style")
 */
class StyleController extends PoppySeedPetsController
{
    /**
     * @Route("/current", methods={"PATCH"})
     */
    public function saveCurrentStyle(Request $request)
    {
        $user = $this->getUser();
    }
}
