<?php
namespace App\Controller\Zoologist;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/zoologist")
 */
class GetDiscoveredSpeciesController
{
    /**
     * @Route("/showNewSpecies", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function showNewSpecies(
        EntityManagerInterface $em
    )
    {

    }
}