<?php

namespace Neutron\Controller;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Neutron\Queue\Queue;
use Neutron\Jobs\SendEmailJob;

/**
 * Controller for handling the home page.
 */
class HomeController extends BaseController
{
    /**
     * Handle the request and return the home page response.
     *
     * @param #[\SensitiveParameter] ServerRequestInterface $request The incoming HTTP request.
     *
     * @return ResponseInterface The HTTP response containing the rendered home page.
     */
    public function index(ServerRequestInterface $request): string|ResponseInterface
    {
        $this->log(self::LOG_LEVEL_DEBUG, 'Home page accessed');
        
        return $this->view('home.twig', ['name' => 'Mikey']);
    }
}
