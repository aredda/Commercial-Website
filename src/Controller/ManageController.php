<?php

namespace App\Controller;

use App\Entity\Cart;
use ReflectionClass;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseDetail;
use App\Entity\User;
use App\Utility\ReflectionUtility;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManageController extends BaseController
{
    /**
     * @Route("/insert", name="insert")
     */
    public function insert(Request $request)
    {
        try
        {
            $this->validateRequest ($request, ['entity']);
    
            $manager = $this->getManager ();
            $entity = $this->getEntityName($request);
    
            $manager->persist (ReflectionUtility::toObject ($request->request->all(), $entity, $manager));
            $manager->flush ();
    
            return new JsonResponse(['success' => 'Insertion has succeeded']);
        }
        catch (Exception $x)
        {
            return new JsonResponse(['error' => $x->getMessage ()]);
        }
    }

    /**
     * @Route("/update", name="update")
     */
    public function update(Request $request)
    {
        try
        {
            $this->validateRequest ($request, ['entity', 'id']);

            $manager = $this->getManager ();
            $entity = $this->getEntityName($request);
    
            ReflectionUtility::toObject ($request->request->all(), $entity, $manager, $manager->find($entity, $request->get('id')));

            $manager->flush ();
    
            return new JsonResponse(['success' => 'Updating has succeeded']);
        }
        catch (Exception $x)
        {
            return new JsonResponse(['error' => $x->getMessage()]);
        }
    } 

    /**
     * @Route("/delete", name="delete")
     */
    public function delete(Request $request)
    {
        try
        {
            $this->validateRequest($request, ['entity']);

            $entity = $this->getEntityName($request);
            $manager = $this->getManager();

            $instances = NavigateController::filter($entity, $this->getRows($entity), $request->request->all());

            if (count($instances) == 0)
                throw new Exception('0 matching records, therefore no record has been deleted');

            foreach ($instances as $i)
                $manager->remove($i);

            $manager->flush();

            return new JsonResponse(['success' => count($instances) . ' record(s) has been deleted successfully!']);
        }
        catch (Exception $x)
        {
            return new JsonResponse(['error' => $x->getMessage()]);
        }
    }

    /**
     * @Route("/addToUser", name="addToUser")
     */
    public function addToUser (Request $request)
    {
        $request->request->add (['customer' => $request->getSession()->get ('user')->getId ()]);

        return $this->insert ($request);
    }

    /**
     * @Route("/deleteToUser", name="deleteToUser")
     */
    public function deleteToUser (Request $request)
    {
        $request->request->add (['customer_id' => $request->getSession()->get ('user')->getId ()]);

        return $this->delete ($request);
    }

    /**
     * @Route("/purchase", name="purchase")
     */
    public function purchase (Request $request)
    {
        try
        {
            $order = new Purchase();
            $order->setCustomer($this->getManager()->find(User::class, $request->getSession()->get('user')->getId ()));
            $order->setDate(new DateTime());

            $this->getManager()->persist($order);
            $this->getManager()->flush();

            $ids = $request->get('ids');
            $qts = $request->get('quantities');

            for ($i=0; $i<count($ids); $i++)
            {
                $product = $this->getManager()->find(Product::class, $ids[$i]);
                $product->setQuantity ($product->getQuantity() - $qts[$i]);

                $detail = new PurchaseDetail();
                $detail->setPurchase($order);
                $detail->setProduct($product);
                $detail->setQuantity($qts[$i]);

                $this->getManager()->persist($detail);
            }

            $this->getManager()->flush();

            return new JsonResponse(['success' => 'okay']);
        }
        catch (Exception $x)
        {
            return new JsonResponse(['error' => $x->getMessage()]);
        }
    }

}