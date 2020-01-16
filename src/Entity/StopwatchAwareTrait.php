<?php
namespace App\Entity;
use Symfony\Component\Stopwatch\Stopwatch;

trait StopwatchAwareTrait
{
    protected Stopwatch $stopwatch;
    /**
     * @see https://symfony.com/doc/current/service_container/autowiring.html#autowiring-other-methods-e-g-setters
     *
     * @required
     *
     * @param Stopwatch $stopwatch
     *
     * @return self
     */
    public function setStopwatch(Stopwatch $stopwatch): self
    {
        $this->stopwatch = $stopwatch;
        return $this;
    }
}