<?php

namespace Andchir\MarkdownBundle\Twig;

use Parsedown;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
    /** @var ContainerInterface */
    protected $container;
    /** @var array */
    protected $cache = [];

    /**
     * AppExtension constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('markdown', [$this, 'markdownFilter'], [
                'is_safe' => ['html']
            ])
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('includeFileContent', [$this, 'includeFileContentFunction'])
        ];
    }

    /**
     * @param $content
     * @return string
     */
    public function markdownFilter($content)
    {
        $parsedown = new Parsedown();
        return $parsedown->text($content);
    }

    /**
     * @param string $filePath
     * @return string
     */
    public function includeFileContentFunction($filePath)
    {
        $rootPath = realpath($this->container->getParameter('kernel.root_dir').'/../..');
        if (substr($filePath, 0, 1) !== '/') {
            $filePath = '/' . $filePath;
        }
        $filePath = $rootPath . $filePath;
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }
        return '';
    }
}
