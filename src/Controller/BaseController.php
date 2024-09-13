<?php

namespace Neutron\Controller;

use Twig\Environment;
use Psr\Log\LoggerInterface;

/**
 * Base controller that provides common functionality for controllers.
 */
class BaseController
{
    public readonly Environment $twig;
    public readonly LoggerInterface $logger;
    public const LOG_LEVEL_INFO = 'info';
    public const LOG_LEVEL_WARNING = 'warning';
    public const LOG_LEVEL_ERROR = 'error';
    public const LOG_LEVEL_DEBUG = 'debug';

    /**
     * BaseController constructor.
     *
     * @param Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(Environment $twig, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->logger = $logger;
    }

    /**
     * Render a Twig template.
     *
     * @param string $template The name of the Twig template.
     * @param array $data Data to pass to the template.
     *
     * @return string The rendered template as a string.
     */
    protected function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Log a message using the provided logger.
     *
     * @param string $level The log level (e.g., 'info', 'error', 'warning').
     * @param string $message The log message.
     *
     * @return void
     */
    protected function log(string $level, string $message): void
    {
        $this->logger->log($level, $message);
    }
}