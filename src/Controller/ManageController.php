<?php

namespace App\Controller;

use ReflectionClass;
use App\Entity\Category;
use App\Entity\Product;
use App\Utility\ReflectionUtility;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ManageController extends AbstractController
{
    /**
     * @Route("/insert", name="insert")
     */
    public function insert(Request $request)
    {
        # To retrieve all the post data
        $request->request->all();

        $manager = $this->getDoctrine()->getManager ();

        $c = new Category ();
        $c->setName ('Condiments');

        $manager->persist ($c);
        $manager->flush ();

        return new Response();
    }

    /**
     * @Route("/update", name="update")
     */
    public function update()
    {
        return new Response();
    } 

    /**
     * @Route("/delete", name="delete")
     */
    public function delete()
    {
        return new Response();
    }

    /**
     * @Route("/test", name="test")
     */
    public function test ()
    {
        $string = "";

        $reflector = new ReflectionClass(Category::class);

        $reader = new AnnotationReader ();

        foreach ($reflector->getProperties () as $p)
        {
            if (!$p->isPrivate ()) continue;

            $string .= $p->getName () . "<br>";
            $string .= count ($reader->getPropertyAnnotations ($p)) . "<br>";
            foreach ($reader->getPropertyAnnotations ($p) as $a)
                $string .= get_class($a) . "<br>";
        }

        return new Response ($string);
    }

    /**
     * @Route("/setter", name="setter")
     */
    public function testSetter ()
    {
        $reflector = new ReflectionClass(Category::class);
        $name = $reflector->getProperty('name');

        $plain = new Category();

        $setter = ReflectionUtility::getSetter($name);
        $setter->invoke ($plain, "Toys");

        return new Response($plain->getName());
    }

    /**
     * @Route("/build", name="build")
     */
    public function testBuilder ()
    {
        $data = [
            'id' => 1,
            'name' => 'Henry\'s',
            'category' => 1,
            'quantity' => 5,
            'price' => 2
        ];

        $manager = $this->getDoctrine()->getManager();

        $i = ReflectionUtility::toObject($data, Product::class, $manager);

        $manager->persist ($i);
        $manager->flush ();

        return new Response();
    }
}