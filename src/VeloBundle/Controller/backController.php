<?php

namespace VeloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class backController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Velo/admin.html.twig');
    }
}
