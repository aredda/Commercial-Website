<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Category;
use App\Entity\Favorite;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\User;
use Doctrine\Migrations\Version\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
    /**
     * @Route("/welcome", name="welcome")
     */
    public function index()
    {
        $this->prepareSession ();

        return $this->render('main/index.html.twig', [
            'recommended' => [],
            'categories' => $this->getRows(Category::class),
            'products' => $this->getRows(Product::class)
        ]);
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard ()
    {
        $this->prepareSession();

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
        $s = $this->prepareSession();

        $products = NavigateController::getProducts(Cart::class, $this->getRows(Cart::class), [ 'customer_id' => $this->getFromSession($s, 'user')->getId () ]);
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
        $s = $this->prepareSession();

        $products = NavigateController::getProducts(Favorite::class, $this->getRows(Favorite::class), [ 'customer_id' => $this->getFromSession($s, 'user')->getId () ]);
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
        $purchases = NavigateController::filter(Purchase::class, $this->getRows(Purchase::class), [
            'customer_id' => $this->getFromSession($this->prepareSession (), 'user')->getId ()
        ]);

        return $this->render ('main/history.html.twig', ['purchases' => $purchases]);
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

    # Prepare Test Session
    public function prepareSession ()
    {
        $session = new Session();
        $session->start();

        if (!$session->has('user'))
            $session->set ('user', $this->getRows(User::class) [0]);

        return $session;
    }
}
