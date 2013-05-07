<?php

namespace tests\PlusPull\Commands;

use PlusPull\Commands\Show;
use PlusPull\GitHub\PullRequest;
use Symfony\Component\Console\Tester\CommandTester;

class ShowTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $show = new Show();
        $this->assertEquals('show', $show->getName());
    }

    public function testExecute()
    {
        $pullRequests = array(
            new PullRequest()
        );

        $config = array(
            'authorization' => array(
                'username' => 'testuser',
                'password' => 'secret',
            ),
            'repository' => array(
                'name' => 'test-repo',
                'username' => 'test-owner',
                'status' => false,
            ),
        );

        $yaml = $this->getMockBuilder('Symfony\Component\Yaml\Yaml')
            ->disableOriginalConstructor()
            ->getMock();
        $yaml->staticExpects($this->any())
            ->method('parse')
            ->will($this->returnValue($config));

        $github = $this->getMockBuilder('PlusPull\GitHub')
            ->disableOriginalConstructor()
            ->getMock();
        $github->expects($this->once())
            ->method('getPullRequests')
            ->will($this->returnValue($pullRequests));

        $show = $this->getMockBuilder('PlusPull\Commands\Show')
            ->setMethods(array('getGitHub', 'getYaml'))
            ->getMock();
        $show->expects($this->once())
            ->method('getGitHub')
            ->will($this->returnValue($github));
        $show->expects($this->once())
            ->method('getYaml')
            ->will($this->returnValue($yaml));

        $tester = new CommandTester($show);
        $tester->execute(array());
    }
}