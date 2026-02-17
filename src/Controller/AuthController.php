<?php

namespace App\Controller;

use App\Dto\RegisterUserDto;
use App\Entity\Country;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route(path: '/login', name: 'app_login', methods: ['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'email' => $lastUsername, //as email is our username field, we pass it to form to fill email input
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register', methods: ['GET','POST'])]
    public function register
    (Request $request,
     UserPasswordHasherInterface $userPasswordHasher,
     EntityManagerInterface $entityManager,
     CsrfTokenManagerInterface $csrfTokenManager, // we use this to validate the CSRF token, because we dont use symfony form, we need to do it manually
     SerializerInterface $serializer,
     ValidatorInterface $validator,
    ): Response
    {
        if(empty($request->getContent())) {
            return $this->render('auth/register.html.twig', [ 'errors' => [] ]);
        }

        $submittedToken = $request->request->get('_csrf_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $submittedToken))) {
            $this->addFlash('error', 'Nieprawidłowy token CSRF.');
            return $this->redirectToRoute('app_register');
        }

        $json = json_encode($request->request->all());
        $registerUserDto = $serializer->deserialize($json, RegisterUserDto::class, 'json');
        $errors = $validator->validate($registerUserDto);
        $errorsResult = [];
        if (count($errors) > 0) {
            foreach ($errors as $violation) {
                $errorsResult[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            return $this->render('auth/register.html.twig', [
                'errors' => $errorsResult
            ]);
        }


        $user = new User();
        // encode the plain password
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $registerUserDto->password
            )
        );
        $user->setEmail($registerUserDto->email);
        $user->setNickName($registerUserDto->nickName);
        $country = $this->em->getRepository(Country::class)->findOneBy(['id' => $registerUserDto->country]);
        $user->setCountry($country);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_landing');

    }

}
