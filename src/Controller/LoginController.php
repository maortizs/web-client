<?php
// src/Controller/LoginController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    public function __construct(private HttpClientInterface $client) {}

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, SessionInterface $session): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            try {
                $response = $this->client->request('POST', 'https://api-platform-project.onrender.com/api/login', [
                    'json' => ['email' => $email, 'password' => $password],
                ]);

                $data = $response->toArray();
                $session->set('jwt', $data['token']);

                return $this->redirectToRoute('product_list');
            } catch (\Exception $e) {
                $error = 'Credenciales incorrectas o fallo de conexión.';
            }
        }

        return $this->render('login/index.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();
        return $this->redirectToRoute('app_login');
    }
}
