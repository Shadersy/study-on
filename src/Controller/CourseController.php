<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Security\User;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * @Route("/course")
 */
class CourseController extends AbstractController
{


    private $bilingService;
    private $tokenStorage;

    public function __construct(BillingClient $billingService, TokenStorageInterface $tokenStorage)
    {
        $this->bilingService = $billingService;
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @Route("/", name="course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {

        if ($this->tokenStorage->getToken()->getUser() == 'anon.') {
            return $this->render('course/index.html.twig', [
                'courses' => $courseRepository->findAll()
            ]);
        }


        $token = $this->tokenStorage->getToken()->getUser()->getApiToken();
        $billingCourses = json_decode($this->bilingService->getCourses($token));
        $studyCourses = $courseRepository->findAll();


        $result = [];

        foreach ($studyCourses as $item) {
            $result[$item->getCode()] = [
                'code' => $item->getCode(),
                'id' => $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),

            ];
        }
        foreach ($billingCourses as $item) {
            $item = (array)$item;
            $result[$item['code']] = array_merge($item, $result[$item['code']]);
        }



        return $this->render('course/index.html.twig', [
            'courses' => $result
        ]);
    }

    /**
     * @Route("/new", name="course_new", methods={"GET","POST"})
     */
    public function new(Request $request, AuthorizationCheckerInterface $authChecker): Response
    {
        if (false === $authChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('course_index');
        }

        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userToken = $this->tokenStorage->
            getToken()->getUser()->getApiToken();

            $params = [
                'title' => $request->request->get('course')['name'],
                'code' =>  $request->request->get('course')['code'],
                'price' => $request->request->get('course')['cost'],
                'type' => $request->request->get('course')['type']
            ];


            $billingCreatingCourse = (array)$this->bilingService->createCourse($userToken, $params);

            if (!$billingCreatingCourse) {
                $this->addFlash('wrong', "Сервис временно не доступен");
            } elseif (array_key_exists('success', $billingCreatingCourse)) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($course);
                $entityManager->flush();

                return $this->redirectToRoute('course_index');
            } else {
                    $this->addFlash('wrong', $billingCreatingCourse['message']);

            }

        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_show", methods={"GET"})
     */
    public function show(Course $course): Response
    {

        $lesson = $this->getDoctrine()->getRepository(Lesson::class)->findBy(["course" => $course->getId()]);
        $courseId = $course->getId();


        return $this->render('course/show.html.twig', [
            'lessons' => $lesson,
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="course_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Course $course, AuthorizationCheckerInterface $authChecker): Response
    {
        if (false === $authChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('course_index');
        }

        $currentCode =  $course->getCode();

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $this->tokenStorage->getToken()->getUser()->getApiToken();

            $params = [
                'title' => $request->request->get('course')['name'],
                'code' =>  $request->request->get('course')['code'],
                'price' => $request->request->get('course')['cost'],
                'type' => $request->request->get('course')['type'],
            ];

            $billingEditCourse = (array)$this->bilingService->editCourse($token, $params, $currentCode);

            if (!$billingEditCourse) {
                $this->addFlash('wrong', "Сервис временно не доступен");
            } elseif (array_key_exists('success', $billingEditCourse)) {
                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
            } else {
                    $this->addFlash('wrong', $billingEditCourse['message']);
            }
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="course_delete", methods={"POST"})
     */
    public function delete(Request $request, Course $course, AuthorizationCheckerInterface $authChecker): Response
    {
        if (false === $authChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('course_index');
        }

        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('course_index');
    }



    /**
     * @Route("/{code}/pay", name="course_pay", methods={"GET"})
     */
    public function payCourse(Request $request, string $code, AuthorizationCheckerInterface $authChecker): Response
    {
        $token = $this->tokenStorage->getToken()->getUser()->getApiToken();

        $response = $this->bilingService->payCourse($token, $code);


        if (array_key_exists('success', json_decode($response))) {
            $this->addFlash('notice', 'Курс успешно оплачен');
        } else {
            $this->addFlash('notice', 'Недостаточно средств для покупки');
        }

        return $this->redirectToROute('course_index');
    }


    /**
     * @Route("/find/{code}", name="course_show_by_code", methods={"GET"})
     */
    public function showByCode(string $code): Response
    {
        $token = $this->tokenStorage->getToken()->getUser()->getApiToken();
        $courseAvailable = json_decode($this->bilingService->checkAvailableCourse($token, $code));

        if (!$courseAvailable) {
            throw new AccessDeniedException();
        }


        $course = $this->getDoctrine()
            ->getRepository(Course::class)->findOneBy(["code" => $code]);


        $lesson = $this->getDoctrine()
            ->getRepository(Lesson::class)->findBy(["course" =>
                $course->getId()
            ]);


        return $this->render('course/show.html.twig', [
            'lessons' => $lesson,
            'course' => $course,
        ]);
    }
}
