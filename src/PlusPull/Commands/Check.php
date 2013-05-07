<?php
namespace PlusPull\Commands;

use Github\Client;
use PlusPull\GitHub;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class Check extends Command
{

    protected function configure()
    {
        $this->setName('check');
        $this->setDescription('Check pull requests');
        $this->addOption(
            'pull',
            'p',
            InputOption::VALUE_NONE,
            'Pull the request if all conditions are met'
        );
        $this->addOption(
            'limit',
            'l',
            InputOption::VALUE_REQUIRED,
            'Maximum numbers of pull',
            1
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getYaml()->parse('config.yml');

        if (!is_array($config) || empty($config)) {
            throw new \InvalidArgumentException('Empty or missing config file');
        }

        $github = $this->getGitHub();

        $github->authenticate(
            $config['authorization']['username'],
            $config['authorization']['password']
        );

        $username = $config['repository']['username'];
        $repository = $config['repository']['name'];
        $checkStatus = !empty($config['repository']['status']);

        $plusRequired = 3;
        if (!empty($config['repository']['required'])) {
            $plusRequired = $config['repository']['required'];
        }

        $maxPulls = $input->getOption('limit');

        $github->setRepository($username, $repository);

        foreach ($github->getPullRequests() as $pullRequest) {
            $pull = $input->getOption('pull');

            $output->write($pullRequest->number.' ('.$pullRequest->title.')');

            if ($pullRequest->checkComments($plusRequired)) {
                $output->write(' +1');
            } else {
                $output->write(' -1');
                $pull = false;
            }

            if ($checkStatus) {
                if ($pullRequest->checkStatuses()) {
                    $output->write(' success');
                } else {
                    $output->write(' fail');
                    $pull = false;
                }
            }

            if ($pullRequest->isMergeable) {
                $output->write(' mergeable');
            } else {
                $output->write(' conflicts');
                $pull = false;
            }

            if ($pull) {
                $github->merge($pullRequest->number);
                $output->write(' pulled');
                $maxPulls--;
            }

            $output->writeln('');

            if ($maxPulls<=0) {
                break;
            }
        }
    }

    protected function getGitHub()
    {
        return new GitHub(new Client());
    }

    protected function getYaml()
    {
        return new Yaml();
    }
}