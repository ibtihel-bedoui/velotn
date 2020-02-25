<?php

namespace VeloBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class frontController extends Controller
{
    public function indexAction()
    {
        return $this->render('@Velo/usr.html.twig');
    }
}
