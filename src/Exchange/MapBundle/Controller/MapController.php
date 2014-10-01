<?php

namespace Exchange\MapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MapController extends Controller
{
    public function indexAction()
    {
        return $this->render('ExchangeMapBundle:Map:filter.html.twig');
    }
}
