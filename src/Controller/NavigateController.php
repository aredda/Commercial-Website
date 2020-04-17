<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Favorite;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseDetail;
use App\Utility\OperatorUtility;
use App\Utility\ReflectionUtility;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NavigateController extends BaseController
{
    /**
     * @Route("/filter", name="filter")
     */
    public function index(Request $request)
    {
        $targetEntity = $this->getEntityName ($request);

        // If the filter requires the customer relativity add the registered id in the session to the criteria
        if (in_array($targetEntity, [Cart::class, Favorite::class, Purchase::class]))
            $request->request->add (['customer_id' => $this->getSessionId ()]);

        return new JsonResponse(['success' => self::filter($targetEntity, $this->getRows($targetEntity), $request->request->all ())]);
    }

    /**
     * @Route("/products", name="products");
     */
    public function getCustomerProducts (Request $request)
    {
        try
        {
            $this->validateRequest ($request, ['entity']);

            $entity = $this->getEntityName ($request);

            $criteria = [
                'customer_id' => $this->getSessionId ()
            ];

            return new JsonResponse(['success' => self::getProducts($entity, $this->getRows($entity), $criteria)]);
        }
        catch (Exception $x)
        {
            return new JsonResponse (['error' => $x->getMessage()]);
        }
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
                # Verify the operator
                if (!is_callable($operator)) $operator = OperatorUtility::get($operator);
                # Try to get property with the same name as column
                $property = ReflectionUtility::getProperty ($targetEntity, $column);
                # Check if the column is a path to the actual property
                if (strpos($column, '_') !== false)
                {
                    # Split the string
                    $pathTree = explode ('_', $column);
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
            if ($pass) $result[] = $instance;
        }
        # Return result
        return $result;
    }

    public static function getProducts (string $entity, array $data, array $criteria)
    {
        $products = [];

        $records = NavigateController::filter($entity, $data, $criteria);
            
        foreach ($records as $record)
            $products[] = $record->getProduct ();

        return $products;
    }
}
