<?php

declare(strict_types=1);

namespace App\ExtractInterface\Bad
{
    use Psr\Http\Message\RequestInterface;
    use Psr\Log\LoggerInterface;
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
            private WelcomeMessageSender $welcomeMessageSender,
            private LoggerInterface $logger
        ) {}

        /**
         * @param string[] $emailAddresses
         */
        public function addMemberships(array $emailAddresses): void
        {
            $this->logger->debug(sprintf('Adding memberships for %s', implode(', ', $emailAddresses)));
            $customers = $this->customerRepository->getCustomersByEmailAddresses(
                $emailAddresses
            );

            $this->logger->debug(sprintf('Found %s customers', count($customers)));

            $previousCustomers = [];
            $newCustomers = [];

            foreach ($customers as $customer) {
                if ($this->hadMembership($customer)) {
                    $this->logger->debug(sprintf('Customer %s had membership', $customer->getName()));
                    $previousCustomers[] = $customer;
                } else {
                    $this->logger->debug(sprintf('Cusomer %s has never had a membership', $customer->getName()));
                    $newCustomers[] = $customer;
                }
            }

            foreach ($newCustomers as $customer) {
                $this->logger->debug(sprintf('Adding membership for %s', $customer->getName()));
                try {
                    $this->addMembership($customer);
                    $this->logger->debug(sprintf('Membership added for %s', $customer->getName()));
                    $this->welcomeMessageSender->sendWelcomeMessage($customer);
                } catch (RuntimeException $exception) {
                    $this->logger->error(
                        sprintf('Failed to add membership for %s', $customer->getName()),
                        [
                            'exception' => $exception
                        ]
                    );
                }
            }

            foreach ($previousCustomers as $customer) {
                $this->logger->debug(sprintf('Renewing membership for %s', $customer->getName()));
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
