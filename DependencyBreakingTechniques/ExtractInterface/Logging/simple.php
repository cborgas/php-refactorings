<?php

declare(strict_types=1);

namespace App\ExtractInterface\Simple
{
    interface EmployeeInterface
    {
        public function pay(): void;
    }

    class Employee implements EmployeeInterface
    {
        public function pay(): void
        {
            $amount = 0;
            
            foreach ($this->timecards as $timecard) {
                if ($this->payPeriod->contains($this->date)) {
                    $amount += ($timecard->getHours() * $this->payRate);
                }
            }
        }
    }

    class LoggingEmployee implements EmployeeInterface
    {
        public function __construct(private Employee $employee) {}

        public function pay(): void
        {
            $this->log();
            $this->employee->pay();
        }

        protected function log(): void
        {
            // ... do some logging
        }
    }

    class Service 
    {
        public function do(EmployeeInterface $employee): void
        {
            // ...
            $employee->pay();
        }
    }
}