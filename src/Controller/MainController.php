<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Favorite;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseDetail;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
    /**
     * @Route("/welcome", name="welcome")
     */
    public function index()
    {
        return $this->render('main/index.html.twig', [
            'recommended' => $this->getTopProducts(),
            'categories' => $this->getRows(Category::class),
            'products' => $this->getRows(Product::class)
        ]);
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard ()
    {
        if ($this->getFromSession('user') == null)
            return $this->redirectToRoute('welcome');

        if (strcmp ($this->getFromSession('user')->getType(), 'admin') != 0)
            return $this->redirectToRoute('welcome');

        return $this->render ('main/dashboard.html.twig', [
            'categories' => $this->getRows(Category::class),
            'products' => $this->getRows(Product::class)
        ]);
    }

    /**
     * @Route("/cart", name="cart")
     */
    public function cart()
    {
        if ($this->getFromSession('user') == null)
            return $this->redirectToRoute('welcome');

        $products = NavigateController::getProducts(Cart::class, $this->getRows(Cart::class), [ 'customer_id' => $this->getSessionId() ]);
        $categories = $this->extractCategories($products);

        return $this->render ('main/cart.html.twig', [
            'categories' => $categories,
            'products' => $products
        ]);
    }

    /**
     * @Route("/favorite", name="favorite")
     */
    public function favorite()
    {
        if ($this->getFromSession('user') == null)
            return $this->redirectToRoute('welcome');

        $products = NavigateController::getProducts(Favorite::class, $this->getRows(Favorite::class), [ 'customer_id' => $this->getSessionId() ]);
        $categories = $this->extractCategories($products);

        return $this->render ('main/favorite.html.twig', [
            'categories' => $categories,
            'products' => $products
        ]);
    }

    /**
     * @Route("/history", name="history")
     */
    public function history()
    {
        if ($this->getFromSession('user') == null)
            return $this->redirectToRoute('welcome');

        $purchases = NavigateController::filter(Purchase::class, $this->getRows(Purchase::class), [ 'customer_id' => $this->getSessionId() ]);
        
        // Calculating the total of all purchases
        $total = 0;
        foreach ($purchases as $p) $total += $p->getTotalPrice ();

        return $this->render ('main/history.html.twig', ['purchases' => $purchases, 'total' => $total]);
    }

    # Extract list of distinct categories
    public function extractCategories (array $products)
    {
        $categories = [];

        foreach ($products as $p)
            if (!in_array($p->getCategory(), $categories))
                $categories[] = $p->getCategory();

        return $categories;
    }

    # A method to retrieve top consumed products
    private function getTopProducts ()
    {
        $map = [];
        $top = [];

        foreach ($this->getRows(PurchaseDetail::class) as $detail)
        {
            $productId = $detail->getProduct()->getId();

            if (!array_key_exists($productId, $map))
                $map[$productId] = 0;

            $map[$productId] += 1;
        }

        while (count ($map) > 5)
        {
            $minKey = array_key_first($map);
            $minCounter = $map[$minKey];

            foreach ($map as $key => $counter)
                if ($counter < $minCounter)
                {
                    $minKey = $key;
                    $minCounter = $counter;    
                }

            unset($map[$minKey]);
        }

        foreach ($map as $key => $record)
            $top[] = $this->getManager()->find(Product::class, $key);

        return $top;
    }
}
