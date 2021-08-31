<?php

declare(strict_types=1);

namespace App
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

    interface FrontDeskInterface
    {
        /**
         * @param string[] $emailAddresses
         */
        public function addMemberships(array $emailAddresses): void;
    }

    class FrontDesk implements FrontDeskInterface
    {
        public function __construct(
            protected CustomerRepository $customerRepository,
            protected WelcomeMessageSender $welcomeMessageSender
        ) {}

        /**
         * @param string[] $emailAddresses
         */
        public function addMemberships(array $emailAddresses): void
        {
            $customers = $this->getCustomers($emailAddresses);

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
        protected function addMembership(Customer $customer): void
        {
            // ...
        }

        protected function renewMembership(Customer $customer): void
        {
            // ...
        }

        protected function hadMembership(Customer $customer): bool
        {
            // ...

            return false;
        }
        
        protected function getCustomers(array $emailAddresses): array
        {
            return $this->customerRepository->getCustomersByEmailAddresses(
                $emailAddresses
            );
        }
    }

    class LoggingFrontDesk extends FrontDesk implements FrontDeskInterface
    {
        public function __construct(
            CustomerRepository $customerRepository,
            WelcomeMessageSender $welcomeMessageSender,
            private LoggerInterface $logger
        ) {
            parent::__construct($customerRepository, $welcomeMessageSender);
        }

        public function addMemberships(array $emailAddresses): void
        {
            $this->logger->debug(sprintf('Adding memberships for %s', implode(', ', $emailAddresses)));
            parent::addMemberships($emailAddresses);
        }

        protected function addMembership(Customer $customer): void
        {
            $this->logger->debug(sprintf('Adding membership for %s', $customer->getName()));
            try {
                parent::addMembership($customer);
                $this->logger->debug(sprintf('Membership added for %s', $customer->getName()));
            } catch (RuntimeException $exception) {
                $this->logger->error(
                    sprintf('Failed to add membership for %s', $customer->getName()),
                    [
                        'exception' => $exception
                    ]
                );
            }
        }

        protected function renewMembership(Customer $customer): void
        {
            $this->logger->debug(sprintf('Renewing membership for %s', $customer->getName()));
            parent::renewMembership($customer);
        }

        protected function getCustomers(array $emailAddresses): array
        {
            $customers = parent::getCustomers($emailAddresses);
            $this->logger->debug(sprintf('Found %s customers', count($customers)));
            return $customers;
        }
    }

    class Controller
    {
        public function __construct(private FrontDeskInterface $frontDesk)
        {
        }

        public function addMemberships(RequestInterface $request): void
        {
            $customerEmails = $request->getBody()['customerEmails'];
            $this->frontDesk->addMemberships($customerEmails);
        }
    }
}

namespace Test
{
    use App\Controller;
    use App\CustomerRepository;
    use App\FrontDesk;
    use App\LoggingFrontDesk;
    use App\WelcomeMessageSender;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\RequestInterface;
    use Psr\Log\LoggerInterface;

    class ControllerTest extends TestCase
    {
        /**
         * @test
         * @doesNotPerformAssertions
         */
        public function canRequestAddMembershipsForFrontDesk(): void
        {
            $frontDesk = new FrontDesk(
                $this->createMock(CustomerRepository::class),
                $this->createMock(WelcomeMessageSender::class)
            );
            $controller = new Controller($frontDesk);
            $request = $this->createMock(RequestInterface::class);
            $request->method('getBody')->willReturn(['customerEmails' => []]);
            $controller->addMemberships($request);
        }

        /**
         * @test
         * @doesNotPerformAssertions
         */
        public function canRequestAddMembershipsForLoggingFrontDesk(): void
        {
            $frontDesk = new LoggingFrontDesk(
                $this->createMock(CustomerRepository::class),
                $this->createMock(WelcomeMessageSender::class),
                $this->createMock(LoggerInterface::class)
            );
            $controller = new Controller($frontDesk);
            $request = $this->createMock(RequestInterface::class);
            $request->method('getBody')->willReturn(['customerEmails' => []]);
            $controller->addMemberships($request);
        }
    }
}
