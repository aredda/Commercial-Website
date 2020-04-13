<?php

namespace App\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class BaseController extends AbstractController
{
    # Validating request
    protected function validateRequest (Request $request, array $keys)
    {
        foreach ($keys as $key)
            if (!$request->request->has($key))
                throw new Exception ("$key is expected!");
    }

    # Shortcut for getting the manager
    protected function getManager ()
    {
        return $this->getDoctrine()->getManager();
    }

    # Shortcut for entity name constructing
    protected function getEntityName (Request $request)
    {
        return 'App\\Entity\\' . ucfirst ($request->get('entity'));
    }

    # A shortcut for getting entity records
    protected function getRows (string $className)
    {
        return $this->getDoctrine()->getRepository($className)->findAll();
    }

    # A shortcut to retrieve a session's bag
    protected function getFromSession ($session, $name)
    {
        return $session->get($name, null);
    }
}