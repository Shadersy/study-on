<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Form\CourseType;
use App\Security\BillingAuthenticator;
use App\Service\BillingClient;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $bilingService;
    private $tokenStorage;

    public function __construct(BillingClient $billingService, TokenStorageInterface $tokenStorage)
    {
        $this->bilingService = $billingService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authChecker): Response
    {

        if ($this->getUser() != null) {
            return $this->redirectToRoute('course_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/profile", name="app_profile", methods={"GET"})
     */
    public function profile(): Response
    {

        $userFromToken = $this->tokenStorage->getToken()->getUser();
        $balance = $this->bilingService->getBalanceToProfile($userFromToken->getApiToken());

        return $this->render('security/profile.html.twig', [
            'username' => $userFromToken->getEmail(),
            'roles' => $userFromToken->getRoles(),
            'balance' => $balance
        ]);
    }


    /**
     * @Route("/profile/transactions", name="app_transactions", methods={"GET"})
     */
    public function transactions(): Response
    {
        $userFromToken = $this->tokenStorage->getToken()->getUser();
        $transactions = $this->bilingService->getTransactions($userFromToken->getApiToken());


        return $this->render('security/transactions.html.twig', array(
            'transactions' => json_decode($transactions, true)
        ));
    }



    /**
     * @Route("/signup", name="app_registry",  methods={"GET","POST"})
     */
    public function signup(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        BillingAuthenticator $authenticator
    ): Response {


        if ($this->getUser() != null) {
            return $this->redirectToRoute('main_course_index');
        }

        $user = new \App\Security\User();

        $form = $this->createFormBuilder($user)
            ->add('email', TextType::class)
            ->add('password', PasswordType::class, array('label' => 'Пароль'))
            ->add('conformationPassword', PasswordType::class, array('label' => 'Подтвердите пароль'))
            ->add('save', SubmitType::class, array('label' => 'Принять'))
            ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()->getEmail();
            $password = $form->getData()->getPassword();

            $res  = $this->bilingService->doSignup($email, $password);


            if ($res == null) {
                $response = new Response();
                $response->setStatusCode('404');


                return $this->render('security/register.html.twig', array(
                    'form' => $form->createView(), 'error' => "Сервис временно недоступен", $response));
            }

            if (is_array($res) && array_key_exists('error', $res)) {
                $response = new Response();
                $response->setStatusCode('404');

                return $this->render('security/register.html.twig', array(
                    'form' => $form->createView(), 'error' => "Пользователь с таким email  существует", $response));
            }



            $user->setEmail($email);
            $user->setPassword($password);
            $user->setApiToken($res->getApiToken());


            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,          // the User object you just created
                $request,
                $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                'main'          // the name of your firewall in security.yaml
            );

        }

        return $this->render('security/register.html.twig', array(
            'form' => $form->createView(), "error" => null
        ));
    }
}
