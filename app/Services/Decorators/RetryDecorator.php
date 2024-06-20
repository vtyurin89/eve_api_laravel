<?php

namespace App\Services\Decorators;
use Exception;

class RetryDecorator
{
    protected $function;
    protected $maxRetries;
    protected $sleepSeconds;

    public function __construct(callable $function, $maxRetries = 10, $sleepSeconds = 1)
    {
        $this->function = $function;
        $this->maxRetries = $maxRetries;
        $this->sleepSeconds = $sleepSeconds;
    }

    public function execute()
    {
        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            try {
                return call_user_func($this->function);
            } catch (Exception $e) {
                $attempts++;
                usleep($this->sleepSeconds * 1000000);
                echo "Attempt $attempts failed: " . $e->getMessage() . "\n";
                if ($attempts >= $this->maxRetries) {
                    throw new Exception("Max retries reached: " . $e->getMessage());
                }
            }
        }
    }

}

?>