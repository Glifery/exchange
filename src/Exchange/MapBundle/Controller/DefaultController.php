<?php

namespace Exchange\MapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ExchangeMapBundle:Default:index.html.twig');
    }
}
