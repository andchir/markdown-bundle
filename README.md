
MarkdownBundle
==============

~~~
composer require andchir/markdown-bundle
~~~

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

Save content from file to the document field:
~~~
{% if currentPage.description is defined and currentPage.description is not empty %}
    {{ currentPage.description | markdown }}
{% else %}
    {{ includeFileContent(currentPage.file_content_path, 'documentation', currentPage.id, 'description') | markdown }}
{% endif %}
~~~

bundles.php:
~~~
return [
    ...,
    App\Plugin\MarkdownBundle\MarkdownBundle::class => ['all' => true]
];
~~~

Command to update content from file:
~~~
php bin/console markdown:action update_content documentation file_content_path description
~~~

~~~
composer require erusev/parsedown
~~~
