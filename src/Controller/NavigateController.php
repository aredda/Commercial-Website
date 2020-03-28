<?php

namespace App\Controller;

use App\Entity\Product;
use App\Utility\OperatorUtility as OU;
use App\Utility\ReflectionUtility;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NavigateController extends AbstractController
{
    /**
     * @Route("/filter", name="filter")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Product::class);

        $result = self::filter (Product::class, $repo->findAll(), [
            'category.name' => 'Condiments',
            'price' => [2, OU::get(OU::BIGGER_THAN)],
            'quantity' => [5, OU::get(OU::SMALLER_THAN)]
        ]);

        return new Response(count ($result));
    }

    public static function filter (string $targetEntity, array $data, array $params)
    {
        # Declare an empty container
        $result = [];
        # Default operator
        $defaultOperator = function ($a, $b) { return strcmp ($a, $b) == 0; };
        # Loop through data
        foreach ($data as $instance)
        {
            # 
            $pass = true;
            # Loop through criteria parameters
            foreach ($params as $column => $options)
            {
                if (!$pass) break;
                # Extract criteria value
                $criteriaValue = is_array($options) ? $options[0] : $options;
                # Get instance value
                $instanceValue = $instance;
                # Retrieve the operator
                $operator = is_array($options) ? (count($options) > 0 ? $options[1] : $defaultOperator) : $defaultOperator;
                # Try to get property with the same name as column
                $property = ReflectionUtility::getProperty ($targetEntity, $column);
                # Check if the column is a path to the actual property
                if (strpos($column, '.') !== false)
                {
                    # Split the string
                    $pathTree = explode ('.', $column);
                    # Prepare requirements
                    $className = $targetEntity;
                    # Loop through tree
                    foreach ($pathTree as $pathNode)
                    {
                        # Check if property exists
                        if(($property = ReflectionUtility::getProperty($className, $pathNode)) == null) continue;
                        # Update requirements
                        $instanceValue = ReflectionUtility::getGetter($property)->invoke($instanceValue);
                        # Update class name
                        if (is_object($instanceValue)) $className = get_class($instanceValue);
                    }
                }
                else if ($property == null) continue; 
                else $instanceValue = ReflectionUtility::getGetter($property)->invoke($instance);
                # Compare using the callable operator
                $pass = call_user_func ($operator, $instanceValue, $criteriaValue);
            }
            # If the instance passes the conditions
            if ($pass)
                $result[] = $instance;
        }
        # Return result
        return $result;
    }
}
