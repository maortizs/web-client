<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Route('/products', name: 'product_list')]
    public function index(SessionInterface $session): Response
    {
        $token = $session->get('jwt');
        if (!$token) return $this->redirectToRoute('app_login');

        try {
            $response = $this->client->request('GET', 'https://api-platform-project.onrender.com/api/products', [
                'headers' => ['Authorization' => "Bearer $token"]
            ]);
            $products = $response->toArray();
        } catch (\Exception) {
            $products = [];
        }

        return $this->render('product/index.html.twig', compact('products'));
    }

    #[Route('/products/new', name: 'product_new')]
    public function new(Request $request, SessionInterface $session): Response
    {
        $token = $session->get('jwt');
        if (!$token) return $this->redirectToRoute('app_login');

        if ($request->isMethod('POST')) {
            $data = [
                'name' => $request->request->get('name'),
                'stock' => (int) $request->request->get('stock'),
                'price' => (float) $request->request->get('price')
            ];

            try {
                $this->client->request('POST', 'https://api-platform-project.onrender.com/api/products', [
                    'headers' => ['Authorization' => "Bearer $token"],
                    'json' => $data
                ]);
                return $this->redirectToRoute('product_list');
            } catch (\Exception) {}
        }

        return $this->render('product/new.html.twig');
    }

    #[Route('/products/{id}/edit', name: 'product_edit')]
    public function edit(string $id, Request $request, SessionInterface $session): Response
    {
        $token = $session->get('jwt');
        if (!$token) return $this->redirectToRoute('app_login');

        // Obtener producto existente
        try {
            $res = $this->client->request('GET', "https://api-platform-project.onrender.com/api/products/$id", [
                'headers' => ['Authorization' => "Bearer $token"]
            ]);
            $product = $res->toArray();
        } catch (\Exception) {
            return $this->redirectToRoute('product_list');
        }

        // Enviar PATCH si se ha enviado el formulario
        if ($request->isMethod('POST')) {
            $updated = [
                'name' => $request->request->get('name'),
                'stock' => (int) $request->request->get('stock'),
                'price' => (float) $request->request->get('price')
            ];

            try {
                $this->client->request('PATCH', "https://api-platform-project.onrender.com/api/products/$id", [
                    'headers' => ['Authorization' => "Bearer $token"],
                    'json' => $updated
                ]);
                return $this->redirectToRoute('product_list');
            } catch (\Exception) {}
        }

        return $this->render('product/edit.html.twig', compact('product'));
    }

    #[Route('/products/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function delete(string $id, SessionInterface $session): Response
    {
        $token = $session->get('jwt');
        if (!$token) return $this->redirectToRoute('app_login');

        try {
            $this->client->request('DELETE', "https://api-platform-project.onrender.com/api/products/$id", [
                'headers' => ['Authorization' => "Bearer $token"]
            ]);
        } catch (\Exception) {}

        return $this->redirectToRoute('product_list');
    }
}
