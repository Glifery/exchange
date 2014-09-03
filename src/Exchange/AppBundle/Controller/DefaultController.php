<?php

namespace Exchange\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ExchangeAppBundle:Default:index.html.twig');
    }
}
