
MarkdownBundle
==============

Usage example:
~~~
{{ currentPage.description | markdown }}
~~~

~~~
{{ currentPage.description | markdown({urlsLinked: false, safeMode: true, breaksEnabled: true, markupEscaped: true}) }}
~~~

~~~
{{ includeFileContent(currentPage.file_content_path) | markdown }}
~~~

bundles.php:
~~~
return [
    ...,
    App\Plugin\MarkdownBundle\MarkdownBundle::class => ['all' => true]
];
~~~

~~~
composer require erusev/parsedown
~~~
