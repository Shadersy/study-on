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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * @Route("/course")
 */
class CourseController extends AbstractController
{


    private $bilingService;

    public function __construct(BillingClient $billingService)
    {
        $this->bilingService = $billingService;
    }


    /**
     * @Route("/", name="course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {

        $userToken = $this->get('security.token_storage')->
        getToken()->getUser()->getApiToken();

        $billingCourses = json_decode($this->bilingService->getCourses($userToken));
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
//
//
//        echo '<pre>';
//        var_dump($result);
//        echo '</pre>';
//
//        exit();
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
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('course_index');
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

        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('course_show', ['id' => $course->getId()]);
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


        $userToken = $this->get('security.token_storage')->
        getToken()->getUser()->getApiToken();

        $this->bilingService->payCourse($userToken, $code);


       return $this->redirectToROute('course_index');
    }
}
