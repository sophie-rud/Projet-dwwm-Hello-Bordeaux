<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/403', name: 'error_403')]
    public function accessDenied(): Response
    {
        return $this->render('public/page/page403.html.twig');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $currentUser = $this->getUser();

        if ($currentUser !== null && $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_list_activities');
        }

        if ($currentUser !== null && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('user_show_profile', ['id' => $currentUser->getId()]);
        }


        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }



    #[Route(path: '/admin/login', name: 'app_admin_login')]
    public function loginAdmin(AuthenticationUtils $authenticationUtils): Response
    {
        $currentUser = $this->getUser();


        if($currentUser ==! null && $this->isGranted(['ROLE_ADMIN'])) {
            return $this->redirectToRoute('admin_list_activities');
        }

        /*if($currentUser ==! null && $this->isGranted(['ROLE_USER'])) {
            //$html403 = $this->renderView('public/page/page403.html.twig');
            //return new Response($html403, 403);
            return $this->redirectToRoute('app_logout');
        }*/

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.admin.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
