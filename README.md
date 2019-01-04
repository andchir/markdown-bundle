
MarkdownBundle
==============

Usage example:
~~~
{{ currentPage.description | markdown }}
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
