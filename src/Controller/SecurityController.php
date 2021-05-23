<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\User;
use App\Form\CourseType;
use App\Security\BillingAuthenticator;
use App\Service\BillingClient;
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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $bilingService;

    public function __construct(BillingClient $billingService)
    {
        $this->bilingService = $billingService;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authChecker): Response
    {

        if ($this->getUser() != null) {
            return $this->redirectToRoute('main_course_index');
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

        $userFromToken = $this->get('security.token_storage')->
        getToken()->getUser();



        $balance = $this->bilingService->getBalanceToProfile($userFromToken->getApiToken());


        return $this->render('security/profile.html.twig', [
            'username' => $userFromToken->getEmail(),
            'roles' => $userFromToken->getRoles(),
            'balance' => $balance
        ]);
    }


    /**
     * @Route("/signup", name="app_registry",  methods={"GET","POST"})
     */
    public function signup(Request $request, AuthorizationCheckerInterface $authChecker, BillingAuthenticator $authenticator): Response
    {


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


            if(is_array($res) && array_key_exists ( 'error' , $res )){
                $response = new Response();
                $response->setStatusCode('404');

                echo 'Пользователь с таким Email уже существует';
                return $this->render('security/register.html.twig', array(
                    'form' => $form->createView(), $response));
            }

            if ($res == null) {

                $response = new Response();
                $response->setStatusCode('404');

                echo 'Сервис временно недоступен, попробуйте повторить позже';
                return $this->render('security/register.html.twig', array(
                    'form' => $form->createView(), $response));
            }


            //перед этим не получилось засэтить токен регистрации в токен сторедж, поскольку
            //тот должен реализовывать токен интерфейс а у нас стринга
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('security/register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
