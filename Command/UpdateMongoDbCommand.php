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
use Doctrine\ORM\EntityRepository;
use Glavweb\CompositeObjectBundle\Entity\ObjectClass;
use Glavweb\CompositeObjectBundle\Service\ObjectManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateMongoDbCommand
 *
 * @package Glavweb\CompositeObjectBundle\Command
 * @author Andrey Nilov <nilov@glavweb.ru>
 */
class UpdateMongoDbCommand extends Command
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var ObjectManipulator
     */
    private $objectManipulator;

    /**
     * UpdateMongoDbCommand constructor.
     *
     * @param Registry $doctrine
     * @param ObjectManipulator $objectManipulator
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     */
    public function __construct(Registry $doctrine, ObjectManipulator $objectManipulator, $name = null)
    {
        parent::__construct($name);

        $this->doctrine = $doctrine;
        $this->objectManipulator = $objectManipulator;
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('composite-object:update-mongo-db')
            ->setDescription('Update MongoDB.')
            ->addArgument(
                'class_name',
                InputArgument::OPTIONAL,
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
        $className = $input->getArgument('class_name');

        if ($className) {
            /** @var EntityRepository $objectClassRepository */
            $em = $this->doctrine->getManager();
            $objectClassRepository = $em->getRepository(ObjectClass::class);
            $objectClass = $objectClassRepository->findOneBy(['name' => $className]);

            if ($objectClass instanceof ObjectClass) {
                $this->objectManipulator->updateInMongoDBByClass($objectClass);

            } else {
                throw new \RuntimeException('Class by name "' . $className . '" not found.');
            }

        } else {
            $this->objectManipulator->updateInMongoDB();
        }
    }
}