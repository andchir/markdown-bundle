<?php

namespace Andchir\MarkdownBundle\Twig;

use App\Controller\CatalogController;
use Parsedown;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

class AppExtension extends AbstractExtension
{
    /** @var ContainerInterface */
    protected $container;
    /** @var  RequestStack */
    protected $requestStack;
    /** @var array */
    protected $cache = [];

    /**
     * AppExtension constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
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
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $localeDefault = $this->container->getParameter('locale');
        $locale = $request->getLocale();
        
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
            if ($localeDefault === $locale) {
                $result = $collection->updateOne(
                    ['_id' => $itemId],
                    ['$set' => [$fieldName => $content]]
                );
            } else {
                $document = $collection->findOne(['_id' => $itemId]);
                if ($document) {
                    $translations = $document['translations'] ?? [];
                    if (!isset($translations[$fieldName])) {
                        $translations[$fieldName] = [];
                    }
                    $translations[$fieldName][$locale] = $content;
                    $result = $collection->updateOne(
                        ['_id' => $itemId],
                        ['$set' => ['translations' => $translations]]
                    );
                }
            }
        }
        return $content;
    }
}
