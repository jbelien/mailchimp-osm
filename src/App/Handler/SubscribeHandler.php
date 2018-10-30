<?php

declare(strict_types=1);

namespace App\Handler;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class SubscribeHandler implements RequestHandlerInterface
{
    /** @var string */
    private $containerName;

    /** @var RouterInterface */
    private $router;

    /** @var TemplateRendererInterface */
    private $template;

    public function __construct(
        RouterInterface $router,
        TemplateRendererInterface $template,
        string $containerName
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->containerName = $containerName;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $query = $request->getQueryParams();

        $iframe = isset($query['iframe']);

        $key = $request->getAttribute('list');

        $file = 'data/cache/lists.json';
        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception(sprintf('Unable to read file "%s" !', $file));
        }

        $fileContent = file_get_contents($file);
        if ($fileContent === false) {
            throw new Exception(sprintf('Invalid JSON in file "%s" !', $file));
        }

        $lists = json_decode($fileContent);
        $list = current(array_filter($lists, function ($value) use ($key) {
            return $value->identifier === $key;
        }));

        if ($list === false) {
            throw new Exception(sprintf('The list "%s" does not exist !', $key));
        }

        $data = [
            'iframe' => $iframe,
            'list'   => $list,
        ];

        return new HtmlResponse($this->template->render('app::subscribe', $data));
    }
}
