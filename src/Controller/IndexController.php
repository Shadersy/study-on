<?php

namespace App\Controller;


use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    /**
     * @Route("/", name="main_course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {
        return  $this->redirectToRoute('course_index');
    }

}
