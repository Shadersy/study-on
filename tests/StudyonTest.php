<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use http\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;


class StudyonTest extends WebTestCase
{

    use FixturesTrait;


    public function makeClient()
    {
        $client = static::createClient();

        $client->disableReboot();

        $client->getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock()
        );

        return $client;
    }

    private function doAuth(& $client, string $email, string $pass)
    {

        $crawler = $client->request('GET', '/login');

        $buttonCrawlerNode = $crawler->selectButton('submit');

        $form = $buttonCrawlerNode->form(array(
            'email' => 'admin@mail.ru',
            'password' => 'qweasd',
        ));

        $client->submit($form);


        return $crawler;
    }

    private function doAuthClient( $client)
    {

        $crawler = $client->request('GET', '/login');

        $buttonCrawlerNode = $crawler->selectButton('submit');

        $form = $buttonCrawlerNode->form(array(
            'email' => 'admin@mail.ru',
            'password' => 'qweasd',
        ));

        $client->submit($form);


        return $client;
    }

    private function setFixtures()
    {
        $this->loadFixtures(array(
            'App\DataFixtures\CourseFixtures'
        ));
    }

    public function testRegisterSuccessfull(): void
    {
        $client = $this->makeClient();

        $crawler = $client->request('POST', '/signup');


        $buttonCrawlerNode = $crawler->selectButton('Принять');

        $form = $buttonCrawlerNode->form(array(
            'form[email]' => 'admin@mail.ru',
            'form[password]' => 'qweasd',
            'form[conformationPassword]' => 'qweasd'
        ));

       $client->submit($form);



        $this->assertTrue(
            $client->getResponse()->isRedirect('http://study-on.local:81/course'));

    }

  // тест на успешную авторизацию
    public function testAuthorization(): void
    {
        $client = $this->makeClient();
        $this->doAuth($client, 'shadersy98@mail.ru', 'qwerty');


        $this->assertTrue(
            $client->getResponse()->isRedirect('http://study-on.local:81/course'));
    }

//проверяем возможность анонима видеть список курсов и невозможность читать содержимое урока
    public function testCoursePage() : void
    {
        $client = $this->makeClient();
        $this->loadFixtures(array(
            'App\DataFixtures\CourseFixtures'
        ));


        $crawler = $client->request('GET', '/course');


        $crawler = $client->followRedirect();

        $this->assertCount(5, $crawler->filter('h2'));

        $link = $crawler
            ->filter('a:contains("Гитарный")')
            ->link();

        $crawler = $client->click($link);

        $this->assertStringContainsString(
            'Уроки',
            $client->getResponse()->getContent()
        );

        $link = $crawler
            ->filter('a:contains("Постановка рук")')
            ->link();

        $crawler = $client->click($link);

        $this->assertTrue(
            $client->getResponse()->isRedirect('/login'));


        $crawler = $client->request('GET', '/lesson/1');
        $this->assertTrue(
            $client->getResponse()->isRedirect('/login'));
    }



//    public function testDeletingOportunity() {
//        $client = $this->makeClient();
//        $this->loadFixtures(array(
//            'App\DataFixtures\CourseFixtures'
//        ));
//        $crawler = $this->doAuth($client, "admin@mail.ru", "qwerty");
//
//        $client->followRedirect();
//        $client->followRedirect();
//
//    }
//
    public function testOpeningCoursePage(): void
    {
        $client = $this->makeClient();
        $crawler = $this->doAuth($client, 'shadersy98@mail.ru', 'qwerty');
        $crawler = $client->followRedirect();
        $client->followRedirect();

        $link = $crawler
            ->filter('a:contains("Новый")')
            ->link();

        $crawler = $client->click($link);

        $this->assertEquals(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $client->getResponse()->getStatusCode()
        );

    }


    public function testNewCourse(): void
    {
        $client = $this->makeClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client, "admin@mail.ru", "qwerty");
        $crawler = $client->followRedirect();
        $crawler = $client->followRedirect();

        $link = $crawler
            ->filter('a:contains("Новый")')
            ->link();
        $crawler = $client->click($link);


        $buttonCrawlerNode = $crawler->selectButton('Создать');
        $form = $buttonCrawlerNode->form();

        //все значения являются валидными
        $client->submit($form, array(
            'course[code]' => '15',
            'course[name]' => 'Guitar master',
            'course[description]' => 'some description'
        ));


//        //Проверяем, что редиректит  на главную после создания
        $this->assertTrue(
            $client->getResponse()->isRedirect('/course/'));

        $crawler = $client->followRedirect();

        //Количество курсов изменилось
        $this->assertCount(4, $crawler->filter('h2'));

        $linkCourse = $crawler->filter('a:contains("curse number6")')->link();
        $crawler = $client->click($linkCourse);

        //Проверяем, что можно перейти на страницу курса
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );


    }

////    переход на страницу создания урока и создаем урок
    public function testNewLesson()
    {
        $client = $this->makeClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client, "admin@mail.ru", "qwerty");
        $crawler = $client->followRedirect();

        $link = $crawler
            ->filter('a:contains("Новый")')
            ->link();
        $crawler = $client->click($link);


        $buttonCrawlerNode = $crawler->selectButton('Создать');
        $form = $buttonCrawlerNode->form();

        $client->submit($form, array(
            'course[code]' => '126',
            'course[name]' => 'Guitar master',
            'course[description]' => 'some description'
        ));


        $this->assertTrue(
            $client->getResponse()->isRedirect('/course/'));

        $crawler = $client->followRedirect();

        $linkCourse = $crawler->filter('a:contains("curse number6")')->link();
        $crawler = $client->click($linkCourse);


        $linkCourse = $crawler->filter('a:contains("Добавить урок")')->link();
        $crawler = $client->click($linkCourse);

        $buttonCrawlerNode = $crawler->selectButton('Создать');
        $form = $buttonCrawlerNode->form();

        $client->submit($form, array(
            'form[name]' => 'Lesson8',
            'form[content]' => 'some content for lesson',
            'form[number]' => '15'
        ));


        //Переход на страницу курса к котоу привязан урок
        $this->assertTrue(
            $client->getResponse()->isRedirect('/course/2'));

        $crawler = $client->followRedirect();

        //Проверяем, что количество уроков изменилось
        $this->assertCount(2, $crawler->filter('li'));


    }

    //проверка статус-кода 404 при несуществующем курсе
    public function testInvalidUrlCourse() {

        $client = $this->makeClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client, "admin@mail.ru", "qwerty");
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', 'http://study-on.local:81/course/32131');


        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );

    }

    public function testInvalidLesson() {
        $client = $this->makeClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client, "admin@mail.ru", "qwerty");
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', 'http://study-on.local:81/lesson/150');

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }


    public function testUniqueCodeCourse() {
        $client = $this->makeClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client, "admin@mail.ru", "qwerty");
        $crawler = $client->followRedirect();


        $link = $crawler
            ->filter('a:contains("Новый")')
            ->link();
        $crawler = $client->click($link);


        $buttonCrawlerNode = $crawler->selectButton('Создать');
        $form = $buttonCrawlerNode->form();

        //coursecode = 6 уже задействован в фикстуре
        $client->submit($form, array(
            'course[code]' => '6',
            'course[name]' => 'Guitar master',
            'course[description]' => 'some description'
        ));

        //проверяем на уникальность
        $this->assertStringContainsString(
            'This value is already used',
            $client->getResponse()->getContent()
        );

    }
}
