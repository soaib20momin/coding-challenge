<?php

namespace App\Controller;
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

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $categories = $entityManager->getRepository(Category::class)->findAll();

        if(!count($categories)){
            $response=array(
                'code'=>1,
                'message'=>'No categories found!',
                'error'=>null,
                'result'=>null
            );
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        
        $data = $serializer->serialize($categories, 'json');
        $response=array(
            'code'=>0,
            'message'=>'success',
            'error'=>null,
            'result'=>json_decode($data)
        );

        return new JsonResponse($response, 200);
    }
}
