<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;



class ProductController extends AbstractController
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $products = $entityManager->getRepository(Product::class)->findAll();

        return new JsonResponse($products);
    }


    /**
    * @Route("/product/add", name="product_add")
    * Method({"GET","POST"})
    */
    public function add(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        //Fetch the request and convert to json data
        $productJson = json_decode($request->getContent(), true);
        $category = $entityManager->getRepository(Category::class)->find($productJson['category']);

        if (!$category) {
            throw $this->createNotFoundException(
                'No Category found for id '.$productJson['category']
            );
        }

        $product = new Product();
        $product->setName($productJson['name']);
        $product->setCategory($category);
        $product->setPrice($productJson['price']);
        $product->setSku($productJson['sku']);
        $product->setQuantity($productJson['quantity']);
        $product->setCreatedAt(new \DateTime("now"));

        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->persist($product);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Product added successfully with name : '.$product->getName());
    }


    /**
    * @Route("/product/update", name="product_update")
    * Method({"GET","POST"})
    */
    public function update(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = new Product();

        //Fetch the request and convert to json data
        $productJson = json_decode($request->getContent(), true);
        $product = $entityManager->getRepository(Product::class)->find($productJson['id']);

        if (!$product) {
            throw $this->createNotFoundException(
                'No product found for id '.$productJson['id']
            );
        }

        $category = $entityManager->getRepository(Category::class)->find($productJson['category']);

        $product->setName($productJson['name']);
        $product->setCategory($category);
        $product->setPrice($productJson['price']);
        $product->setSku($productJson['sku']);
        $product->setQuantity($productJson['quantity']);
        $product->setModifiedAt(new \DateTime("now"));

        $entityManager->flush();

        return new Response('Product updated successfully with name : '.$product->getName());
    }

    /**
    * @Route("/product/{id}", name="product_show")
    */
    public function show($id)
    {
        $product = $this->getDoctrine()
            ->getRepository(Product::class)
            ->find($id);

        if (empty($product)) {
   
            $response=array(
                'code'=>1,
                'message'=>'product not found',
                'error'=>null,
                'result'=>null
            );
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        
        $data = $serializer->serialize($product, 'json');;
        $response=array(
            'code'=>0,
            'message'=>'success',
            'error'=>null,
            'result'=>json_decode($data)
        );

        return new JsonResponse($response, 200);

    }

    /**
    * @Route("/product/delete/{id}")
    */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
        $entityManager->remove($product);
        $entityManager->flush();
        return new Response('Product deleted successfully');
    }
}
