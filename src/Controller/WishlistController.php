<?php

namespace App\Controller;

use App\Entity\WishlistItem;
use App\Entity\Product;
use App\Repository\WishlistItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wishlist')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class WishlistController extends AbstractController
{
    #[Route('', name: 'get_wishlist', methods: ['GET'])]
    public function index(WishlistItemRepository $wishlistRepo): JsonResponse
    {
        $user = $this->getUser();
        $items = $wishlistRepo->findBy(['user' => $user]);

        return $this->json($items, 200, [], ['groups' => 'product:read']);
    }

    #[Route('', name: 'add_to_wishlist', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $productId = $data['product_id'] ?? null;

        if (!$productId) {
            return $this->json(['error' => 'Product ID required'], 400);
        }

        $product = $em->getRepository(Product::class)->find($productId);

        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $wishlistItem = new WishlistItem();
        $wishlistItem->setUser($this->getUser());
        $wishlistItem->setProduct($product);

        $em->persist($wishlistItem);
        $em->flush();

        return $this->json($wishlistItem, 201, [], ['groups' => 'product:read']);
    }

    #[Route('/{id}', name: 'remove_from_wishlist', methods: ['DELETE'])]
    public function remove(WishlistItem $wishlistItem, EntityManagerInterface $em): JsonResponse
    {
        if ($wishlistItem->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $em->remove($wishlistItem);
        $em->flush();

        return $this->json(null, 204);
    }
}
