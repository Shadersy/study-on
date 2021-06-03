<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        $barberCourse = new Course();
        $barberCourse->setName('Барбер-мужской парикмахер');
        $barberCourse->setDescription('Курс по Барберингу для тех, к
        то хочет виртуозно владеть различными приемами в мужских стрижках, 
        моделировании бороды и усов, а так же кто хочет разбираться в последних 
        тенденциях мужской моды и барберинга.');
        $barberCourse->setCode('barber-muzhskoy-parikmaher');
        $barberCourse->setType(0);
        $barberCourse->setCost(0);
        $barberLesson = new Lesson();
        $barberLesson->setName('24 практические работы');
        $barberLesson->setContent('some content');
        $barberLesson->setNumber(1);
        $barberLesson->setCourse($barberCourse);
        $barberLessonTwo = new Lesson();
        $barberLessonTwo->setName('Мытье и массаж головы');
        $barberLessonTwo->setContent('Какой-то контент');
        $barberLessonTwo->setNumber(2);
        $barberLessonTwo->setCourse($barberCourse);



        $guitarCourse = new Course();
        $guitarCourse->setName('Гитарный профи');
        $guitarCourse->setCode('samiy-dorogoi-kurs');
        $guitarCourse->setType(2);
        $guitarCourse->setCost(10000);
        $guitarCourse->setDescription('Обучение игры на гитаре');
        $guitarLesson = new Lesson();
        $guitarLesson->setName('Постановка рук');
        $guitarLesson->setContent('Ловкость рук и никакого мошеничества');
        $guitarLesson->setNumber(3);
        $guitarLesson->setCourse($guitarCourse);


        $gosCourse = new Course();
        $gosCourse->setName('Государственно-частное партнерство');
        $gosCourse->setCode('gosudarstvenno-chastnoe-partnerstv');
        $gosCourse->setDescription('Государственно-частное партнерство');
        $gosCourse->setCost(20);
        $gosCourse->setType(1);
        $gosLesson = new Lesson();
        $gosLesson->setName('Урок по государственно-частному партнерству');
        $gosLesson->setNumber(4);
        $gosLesson->setContent('test');
        $gosLesson->setCourse($gosCourse);



        $landshaftCourse = new Course();
        $landshaftCourse->setName('Ландшафтное проектирование');
        $landshaftCourse->setCode('landshaftnoe-proektirovanie');
        $landshaftCourse->setType(2);
        $landshaftCourse->setCost(30.9);
        $landshaftCourse->setDescription('После обучения на этом курсе 
        Вы с уверенностью сможете устроиться на позицию джуниора в Java-разработке.');
        $landshaftLesson = new Lesson();
        $landshaftLesson->setName('Урок по ландшафтному проектированию');
        $landshaftLesson->setNumber(5);
        $landshaftLesson->setContent('some content');
        $landshaftLesson->setCourse($landshaftCourse);


        $testCourse = new Course();
        $testCourse->setName('Тестовый курс');
        $testCourse->setCode('testCourse');
        $testCourse->setCost(400);
        $testCourse->setType(2);
        $testCourse->setDescription("");
        $testLesson = new Lesson();
        $testLesson->setName('empty lesson');
        $testLesson->setContent('TODO');
        $testLesson->setNumber(6);
        $testLesson->setCourse($testCourse);




        $manager->persist($barberLesson);
        $manager->persist($barberLessonTwo);
        $manager->persist($guitarLesson);
        $manager->persist($gosLesson);
        $manager->persist($landshaftLesson);
        $manager->persist($testLesson);

        $manager->persist($barberCourse);
        $manager->persist($guitarCourse);
        $manager->persist($gosCourse);
        $manager->persist($landshaftCourse);
        $manager->persist($testCourse);


        $manager->flush();
    }
}
