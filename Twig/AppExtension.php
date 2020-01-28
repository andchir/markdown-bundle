<?php

namespace Andchir\MarkdownBundle\Twig;

use App\Controller\CatalogController;
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
     * @param array $options
     * @return string
     */
    public function markdownFilter($content, $options = [])
    {
        $parsedown = new Parsedown();
        foreach ($options as $key => $val) {
            if (method_exists($parsedown,"set{$key}")) {
                call_user_func(array($parsedown, "set{$key}"), $val);
            }
        }
        return $parsedown->text($content);
    }

    /**
     * @param string $filePath
     * @param string $collectionName
     * @param int $itemId
     * @param string $fieldName
     * @return string
     */
    public function includeFileContentFunction($filePath, $collectionName = '', $itemId = 0, $fieldName = '')
    {
        $rootPath = realpath($this->container->getParameter('kernel.root_dir').'/../..');
        if (substr($filePath, 0, 1) !== '/') {
            $filePath = '/' . $filePath;
        }
        $filePath = $rootPath . $filePath;
        $content = file_exists($filePath) ? file_get_contents($filePath) : '';
        if ($collectionName && $itemId && $fieldName) {
            /** @var CatalogService $catalogService */
            $catalogService = $this->container->get('app.catalog');
            $collection = $catalogService->getCollection($collectionName);
            $result = $collection->updateOne(
                ['_id' => $itemId],
                ['$set' => [$fieldName => $content]]
            );
        }
        return $content;
    }
}
