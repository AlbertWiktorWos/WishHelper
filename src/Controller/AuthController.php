<?php

namespace App\Controller;

use App\Dto\RegisterUserDto;
use App\Entity\Country;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Mailer;
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
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

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
     Mailer $mailer, //our service to send emails
     VerifyEmailHelperInterface $verifyEmailHelper //from symfonycasts/verify-email-bundle
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

        try {
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

        }catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while processing your registration. Please try again later.');
            return $this->redirectToRoute('app_register');
        }

        try {
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email', //route of verification route
                $user->getId(), //user id to verify
                $user->getEmail(), //user email
                ['id' => $user->getId()]
            );

            $mailer->sendEmailVerificationMessage($user,$signatureComponents->getSignedUrl() );
        }catch (\Exception $e) {
            $this->addFlash('error', 'Failed to send verification email. Please try again later.');
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Email verification sent! Please check your inbox and click the link to verify your account.');
        return $this->redirectToRoute('app_landing');
    }

    #[Route(path: '/verify-email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $user = $userRepository->find($request->query->get('id'));
        if (!$user) {
            throw $this->createNotFoundException();
        }
        // if everything goes well lets verify our user
        $user->setVerified(true);
        $entityManager->flush();
        // we don't need to persist because our entity manager already aware of our user, because it become from database, so we just need to flush it

        return $this->render('auth/email_verified.html.twig');
    }

    #[Route('/verify-email/resend', name: 'app_verify_resend_email', methods: ['GET','POST'])]
    public function resendVerifyEmail(
        Request $request,
        UserRepository $userRepository,
        Mailer $mailer,
        VerifyEmailHelperInterface $verifyEmailHelper
    ): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('app_verify_resend_email');
            }

            if ($user->isVerified()) {
                $this->addFlash('info', 'Email already verified.');
                return $this->redirectToRoute('app_login');
            }

            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $mailer->sendEmailVerificationMessage($user, $signatureComponents->getSignedUrl());

            $this->addFlash('success', 'Verification email sent!');
            return $this->redirectToRoute('app_login');
        }
        $email = $request->get('email');
        return $this->render('auth/resend_verification_email.html.twig', [
            'email' => $email ?? '', // if email is not empty we pass it to form to fill email input, otherwise we pass empty string
        ]);
    }

}
