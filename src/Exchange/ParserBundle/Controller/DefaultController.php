<?php

namespace Exchange\ParserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ExchangeParserBundle:Default:index.html.twig', array('name' => $name));
    }
}
