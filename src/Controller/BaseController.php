<?php

namespace App\Controller;

use App\Utility\UploadUtility;
use Exception;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class BaseController extends AbstractController
{
    

    # Validating request
    protected function validateRequest (Request $request, array $keys)
    {
        foreach ($keys as $key)
        {
            if (!$request->request->has($key))
                throw new Exception ("$key is expected!");

            if (empty($request->get($key)))
                throw new Exception ("$key is expected!");
        }
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
    protected function getFromSession ($name)
    {
        return $this->get('session')->get($name, null);
    }

    # A short cut to get the id of the user that's in the session
    protected function getSessionId ()
    {
        return $this->get('session')->get('user')->getId ();
    }

    # A shortcut for uploading & assigning to an instance
    protected function uploadFiles (Request $request, $instance, $deleteOld = false)
    {
        if ($request->files->count() > 0)
        {
            foreach ($request->files->all () as $key => $file)
            {
                if ($file == null)
                    continue;
                // Retrieve the old file
                $oldFile = $instance->{$key}; 
                // Upload and assign the new file
                $instance->{$key} = UploadUtility::upload($file, time(), $request->get('entity'), [
                    function ($file)
                    {
                        $extension = $file->getClientOriginalExtension ();

                        if (!in_array($extension, ['JPEG', 'JPG']))
                            throw new Exception("Invalid image type! Only JPG images are allowed! {$extension} isn't allowed!");
                    }
                ]);
                // If it's update mode then delete the old file
                if ($deleteOld) 
                    if (file_exists($oldFile))
                        unlink($oldFile);
            }
        }

        return $instance;
    }
}