<?php

/*
 * This file is part of the "GlavwebCompositeObjectBundle" package.
 *
 * (c) GLAVWEB <info@glavweb.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glavweb\CompositeObjectBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Entity\ObjectInstance;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SortObjectsByIdCommand
 *
 * @package Glavweb\CompositeObjectBundle\Command
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class SortObjectsByIdCommand extends Command
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * SortObjectsByIdCommand constructor.
     * @param Registry $doctrine
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(Registry $doctrine, $name = null)
    {
        parent::__construct($name);

        $this->doctrine = $doctrine;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('composite-object:sort-by-id')
            ->setDescription('Sort objects by id.')
            ->addArgument(
                'class_name',
                InputArgument::REQUIRED,
                'Class name'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ObjectRepository $objectInstanceRepository */
        /** @var EntityRepository $objectClassRepository */
        $objectInstanceRepository = $this->doctrine->getRepository(ObjectInstance::class);
        $objectClassRepository = $this->doctrine->getRepository(ObjectClass::class);
        $className = $input->getArgument('class_name');

        $objectClass = $objectClassRepository->findOneBy(['name' => $className]);
        if (!$objectClass instanceof ObjectClass) {
            throw new \RuntimeException('Object class not found by name "' . $className . '".');
        }

        /** @var ObjectInstance[] $objectInstances */
        $objectInstances = $objectInstanceRepository->findBy(['class' => $objectClass], ['id' => 'ASC']);

        $i = 0;
        foreach ($objectInstances as $objectInstance) {
            $objectInstance->setPosition($i);
            $i++;
        }

        $this->doctrine->getManager()->flush();
    }
}