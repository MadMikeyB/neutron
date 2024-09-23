<?php

namespace Neutron\Queue;

class Queue
{
    protected string $queueFile;

    public function __construct()
    {
        // Path to the file where jobs are stored
        $this->queueFile = __DIR__ . '/../../queue.json';
        
        // Ensure the queue file exists
        if (!file_exists($this->queueFile)) {
            file_put_contents($this->queueFile, json_encode([]));
        }
    }

    /**
     * Push a job onto the queue.
     */
    public function push(Job $job): void
    {
        $queue = $this->getQueue();
        $queue[] = serialize($job); // Serialize the job object
        file_put_contents($this->queueFile, json_encode($queue));
    }

    /**
     * Pop the next job off the queue.
     */
    public function pop(): ?Job
    {
        $queue = $this->getQueue();
        
        if (empty($queue)) {
            return null; // No jobs in the queue
        }

        // Get the first job
        $serializedJob = array_shift($queue);

        // Save the updated queue back to the file
        file_put_contents($this->queueFile, json_encode($queue));

        // Unserialize and return the job
        return unserialize($serializedJob);
    }

    /**
     * Get the queue (load from file).
     */
    protected function getQueue(): array
    {
        return json_decode(file_get_contents($this->queueFile), true) ?? [];
    }
}