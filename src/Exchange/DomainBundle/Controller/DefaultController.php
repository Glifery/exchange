<?php

namespace Exchange\DomainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ExchangeDomainBundle:Default:index.html.twig', array('name' => $name));
    }
}
