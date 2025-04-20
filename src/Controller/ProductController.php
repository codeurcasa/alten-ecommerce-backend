<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'get_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();
        return $this->json($products, 200, [], ['groups' => 'product:read']);
    }

    #[Route('/products/{id}', name: 'get_product', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        return $this->json($product, 200, [], ['groups' => 'product:read']);
    }

    #[Route('/products', name: 'create_product', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        // Only admin can create
        if ($this->getUser()->getUserIdentifier() !== 'admin@admin.com') {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->getContent();
        $product = $serializer->deserialize($data, Product::class, 'json');
        $product->setCreatedAt(new \DateTimeImmutable());
        $product->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($product);
        $em->flush();

        return $this->json($product, Response::HTTP_CREATED, [], ['groups' => 'product:read']);
    }

    #[Route('/products/{id}', name: 'update_product', methods: ['PATCH'])]
    public function update(Request $request, Product $product, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        if ($this->getUser()->getUserIdentifier() !== 'admin@admin.com') {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = $request->getContent();
        $serializer->deserialize($data, Product::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $product,
        ]);
        $product->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json($product, 200, [], ['groups' => 'product:read']);
    }

    #[Route('/products/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $em): JsonResponse
    {
        if ($this->getUser()->getUserIdentifier() !== 'admin@admin.com') {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($product);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
