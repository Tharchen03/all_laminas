<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function apAction()
    {
        return new ViewModel();
    }

    public function shortsAction()
    {
        return new ViewModel();
    }

    public function servicesAction()
    {
        return new ViewModel();
    }


    public function contactAction()
    {
        return new ViewModel();
    }

    public function aboutAction()
    {
        return new ViewModel();
    }
}
