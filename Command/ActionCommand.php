<?php

namespace Andchir\MarkdownBundle\Command;

use App\Controller\ProductController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

class ActionCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('markdown:action')
            ->setDescription('Application actions commands.')
            ->setHelp('Available actions: filters_update')
            ->addArgument('action', InputArgument::REQUIRED, 'Action name.')
            ->addArgument('option', InputArgument::OPTIONAL, 'Action option.')
            ->addArgument('option2', InputArgument::OPTIONAL, 'Action option2.')
            ->addArgument('option3', InputArgument::OPTIONAL, 'Action option3.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $time_start = microtime(true);

        $action = $input->getArgument('action');
        $option = $input->getArgument('option');

        switch ($action) {
            case 'update_content':

                if (!$option) {
                    $io->error("Collection name is empty.");
                    return;
                }
                $fieldNameFilePath = $input->getArgument('option2');
                $fieldNameContent = $input->getArgument('option3');
                if (!$fieldNameFilePath || !$fieldNameContent) {
                    $io->error("Field names is empty.");
                    return;
                }

                $productController = new ProductController();
                $productController->setContainer($this->getContainer());

                $collection = $productController->getCollection($option);
                $documents = $collection->find();
                $count = 0;

                foreach ($documents as $document) {
                    if (empty($document[$fieldNameFilePath])) {
                        continue;
                    }
                    $filePath = $this->getMdFullFilePath($document[$fieldNameFilePath]);
                    $content = file_exists($filePath) ? file_get_contents($filePath) : '';

                    $result = $collection->update(
                        ['_id' => $document['_id']],
                        ['$set' => [$fieldNameContent => $content]]
                    );
                    if (!empty($result['ok'])) {
                        $count++;
                    }

                    if (!empty($document['translations'][$fieldNameFilePath]) && is_array($document['translations'][$fieldNameFilePath])) {
                        foreach ($document['translations'][$fieldNameFilePath] as $lang => $value) {
                            $filePath = $this->getMdFullFilePath($value);
                            $content = file_exists($filePath) ? file_get_contents($filePath) : '';
                            $result = $collection->update(
                                ['_id' => $document['_id']],
                                ['$set' => ["translations.{$fieldNameContent}.{$lang}" => $content]]
                            );
                            if (!empty($result['ok'])) {
                                $count++;
                            }
                        }
                    }
                }

                $io->success('Documents total: ' . $documents->count() . '. Updated: ' . $count);

                break;
        }

        $time_end = microtime(true);
        $time = round($time_end - $time_start, 3);

        $io->note("The operation has been processed in time {$time} sec.");
    }

    /**
     * @param $filePath
     * @return string
     */
    public function getMdFullFilePath($filePath)
    {
        $rootPath = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../..');
        if (substr($filePath, 0, 1) !== '/') {
            $filePath = '/' . $filePath;
        }
        return $rootPath . $filePath;
    }
}

