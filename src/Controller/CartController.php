<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CartController extends AbstractController
{
    #[Route('', name: 'get_cart', methods: ['GET'])]
    public function index(CartItemRepository $cartItemRepo): JsonResponse
    {
        $user = $this->getUser();
        $items = $cartItemRepo->findBy(['user' => $user]);

        return $this->json($items, 200, [], ['groups' => 'product:read']);
    }

    #[Route('', name: 'add_to_cart', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], 400);
        }

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $cartItem = new CartItem();
        $cartItem->setUser($this->getUser());
        $cartItem->setProduct($product);
        $cartItem->setQuantity($quantity);

        $em->persist($cartItem);
        $em->flush();

        return $this->json($cartItem, 201, [], ['groups' => 'product:read']);
    }

    #[Route('/{id}', name: 'remove_from_cart', methods: ['DELETE'])]
    public function remove(CartItem $cartItem, EntityManagerInterface $em): JsonResponse
    {
        if ($cartItem->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $em->remove($cartItem);
        $em->flush();

        return $this->json(null, 204);
    }
}
