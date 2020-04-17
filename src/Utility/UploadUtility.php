<?php

namespace App\Utility;

use Exception;
use Symfony\Component\HttpFoundation\File\File;

abstract class UploadUtility
{
    public static function upload (File $file, string $newName, string $entity, array $constraints = null)
    {
        // Validate constraints
        if ($constraints != null)
        foreach ($constraints as $constraint)
            call_user_func ($constraint, $file);
        // Adjust the directory
        $newDir = "uploads/" . strtolower($entity) . "/";
        // Adjust the file name
        $newName .= ".{$file->getClientOriginalExtension()}";
        // Move the temporary uploaded file to
        $file->move($newDir, $newName);

        return $newDir . $newName;
    }
}