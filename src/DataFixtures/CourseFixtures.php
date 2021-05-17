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
        for($i = 5; $i <8; $i++){
        
        	     	
        	$course = new Course();
        	$course->setCode('symbolcode'.$i);
        	$course->setName('curse number'.$i);
        	$course->setDescription('description');
       
        	
        	
        	$lesson = new Lesson();
        	$lesson->setName('Lesson'.$i);
        	$lesson->setContent('Content');
        	$lesson->setNumber($i);
        	$lesson->setCourse($course);
        	
        	$manager->persist($lesson);
        	$manager->persist($course);
        	
        	
        }
        
        $manager->flush();
    }
}
