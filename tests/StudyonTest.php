<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Test\FixturesTrait;


class StudyonTest extends WebTestCase
{

    use FixturesTrait;

    private function doAuth($client)
    {

        $crawler = $client->request('GET', 'http://study-on.local:81/login');

        $buttonCrawlerNode = $crawler->selectButton('submit');

        $form = $buttonCrawlerNode->form(array(
            'email' => 'shadersy98@mail.ru',
            'password' => 'qweasd',
        ));

        $client->submit($form);

        return $crawler;
    }

    private function setFixtures()
    {
        $this->loadFixtures(array(
            'App\DataFixtures\CourseFixtures'
        ));
    }

    //тест на успешную авторизацию под существующим пользователем
    public function testAuthorization(): void
    {
        $client = static::createClient();
        $this->doAuth($client);

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://study-on.local:81/'));
    }

   //тест страницы курса для проверки количества отображаемых курсов (не считает)
    public function testCoursePage() : void
    {

        $client = static::createClient();

        $this->loadFixtures(array(
            'App\DataFixtures\CourseFixtures'
        ));

        $crawler = $this->doAuth($client);

        $client->followRedirect();

        $this->assertCount(3, $crawler->filter('a[href^=\'course1\']'));

    }


    public function testOpeningCoursePage(): void
    {
        $client = static::createClient();
        $crawler = $this->doAuth($client);

        $crawler = $client->followRedirect();


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
        $client = static::createClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client);
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


        //Проверяем, что редиректит  на главную после создания
        $this->assertTrue(
            $client->getResponse()->isRedirect('/course/'));

        $crawler = $client->followRedirect();

        $linkCourse = $crawler->filter('a:contains("curse number6")')->link();
        $crawler = $client->click($linkCourse);

        //Проверяем, что можно перейти на страницу курса
        $this->assertEquals(
            200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
            $client->getResponse()->getStatusCode()
        );

    }


    //переход на страницу создания урока и создаем урок
    public function testNewLesson()
    {
        $client = static::createClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client);
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
            'form[name]' => 'Lesson6',
            'form[content]' => 'some content for lesson',
            'form[number]' => '15'
        ));

        //Переход на страницу курса к котоу привязан урок
        $this->assertTrue(
            $client->getResponse()->isRedirect('/course/2'));

    }

    //проверка статус-кода 404 при несуществующем курсе
    public function testInvalidUrlCourse() {

        $client = static::createClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client);
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', 'http://study-on.local:81/course/32131');


        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );

    }
    
    public function testInvalidLesson() {
        $client = static::createClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client);
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', 'http://study-on.local:81/lesson/150');

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }


    public function testUniqueCodeCourse() {
        $client = static::createClient();
        $this->setFixtures();
        $crawler = $this->doAuth($client);
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
