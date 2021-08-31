<?php

declare(strict_types=1);

namespace App\ExtractInterface\Before
{
    use Psr\Http\Message\RequestInterface;
    use RuntimeException;

    class Customer
    {
        public function getName(): string
        {
            // ...
        }
    }

    class CustomerRepository
    {
        /**
         * @param string[] $emailAddresses
         * @return Customer[]
         */
        public function getCustomersByEmailAddresses(array $emailAddresses): array
        {
            // ...
        }
    }

    class WelcomeMessageSender
    {
        public function sendWelcomeMessage(Customer $newCustomer): void
        {
            // ...
        }

        public function sendWelcomeBackMessage(Customer $customer): void
        {
            // ...
        }
    }

    class FrontDesk
    {
        public function __construct(
            private CustomerRepository $customerRepository,
            private WelcomeMessageSender $welcomeMessageSender
        ) {}

        /**
         * @param string[] $emailAddresses
         */
        public function addMemberships(array $emailAddresses): void
        {
            $customers = $this->customerRepository->getCustomersByEmailAddresses(
                $emailAddresses
            );

            $previousCustomers = [];
            $newCustomers = [];

            foreach ($customers as $customer) {
                if ($this->hadMembership($customer)) {
                    $previousCustomers[] = $customer;
                } else {
                    $newCustomers[] = $customer;
                }
            }

            foreach ($newCustomers as $customer) {
                $this->addMembership($customer);
                $this->welcomeMessageSender->sendWelcomeMessage($customer);
            }

            foreach ($previousCustomers as $customer) {
                $this->renewMembership($customer);
                $this->welcomeMessageSender->sendWelcomeBackMessage($customer);
            }
        }

        /**
         * @param Customer $customer
         * @throws RuntimeException
         */
        private function addMembership(Customer $customer): void
        {
            // ...
        }

        private function renewMembership(Customer $customer): void
        {
            // ...
        }

        private function hadMembership(Customer $customer): bool
        {
            // ...

            return false;
        }
    }

    class Controller
    {
        public function __construct(private FrontDesk $frontDesk)
        {
        }

        public function addMemberships(RequestInterface $request): void
        {
            $customerEmails = $request->getBody()['customerEmails'];
            $this->frontDesk->addMemberships($customerEmails);
        }
    }
}
