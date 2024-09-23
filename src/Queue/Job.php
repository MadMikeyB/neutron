<?php

namespace Neutron\Queue;

abstract class Job
{
    /**
     * Method that will be executed when the job is processed.
     */
    abstract public function handle(): void;
}