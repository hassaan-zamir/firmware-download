<?php

namespace App\Command;

use App\Entity\SoftwareVersion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-software-versions',
    description: 'Load software versions from the bundled JSON fixture data into the database',
)]
class LoadSoftwareVersionsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $jsonPath = dirname(__DIR__, 2) . '/data/softwareversions.json';
        if (!file_exists($jsonPath)) {
            $io->error('JSON fixture file not found at: ' . $jsonPath);
            return Command::FAILURE;
        }

        $json = file_get_contents($jsonPath);
        $versions = json_decode($json, true);

        if (!is_array($versions)) {
            $io->error('Failed to parse JSON file.');
            return Command::FAILURE;
        }

        // Clear existing data
        $connection = $this->em->getConnection();
        $connection->executeStatement('DELETE FROM software_version');

        $count = 0;
        foreach ($versions as $entry) {
            $sv = new SoftwareVersion();
            $sv->setName($entry['name']);
            $sv->setSystemVersion($entry['system_version']);
            $sv->setSystemVersionAlt($entry['system_version_alt']);
            $sv->setLink($entry['link'] ?? '');
            $sv->setStLink($entry['st'] ?? '');
            $sv->setGdLink($entry['gd'] ?? '');
            $sv->setIsLatest($entry['latest'] ?? false);
            // computeLciFields is called automatically via @PrePersist

            $this->em->persist($sv);
            $count++;
        }

        $this->em->flush();

        $io->success(sprintf('Successfully loaded %d software versions.', $count));
        return Command::SUCCESS;
    }
}
