<?php

declare(strict_types=1);

namespace WrapClass\App
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
            protected WelcomeMessageSender $welcomeMessageSender,
            protected CustomerMembershipManagerInterface $customerMembershipManager
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
                if ($this->customerMembershipManager->hadMembership($customer)) {
                    $previousCustomers[] = $customer;
                } else {
                    $newCustomers[] = $customer;
                }
            }

            foreach ($newCustomers as $customer) {
                $this->customerMembershipManager->addMembership($customer);
                $this->welcomeMessageSender->sendWelcomeMessage($customer);
            }

            foreach ($previousCustomers as $customer) {
                $this->customerMembershipManager->renewMembership($customer);
                $this->welcomeMessageSender->sendWelcomeBackMessage($customer);
            }
        }
    }

    class LoggingFrontDesk implements FrontDeskInterface
    {
        public function __construct(
            private FrontDesk $frontDesk,
            private LoggerInterface $logger
        ) {}

        public function addMemberships(array $emailAddresses): void
        {
            $this->logger->debug(sprintf('Adding memberships for %s', implode(', ', $emailAddresses)));
            $this->frontDesk->addMemberships($emailAddresses);
        }
    }

    interface CustomerMembershipManagerInterface
    {
        /**
         * @param Customer $customer
         * @throws RuntimeException
         */
        public function addMembership(Customer $customer): void;

        public function renewMembership(Customer $customer): void;

        public function hadMembership(Customer $customer): bool;
    }

    class CustomerMembershipManager implements CustomerMembershipManagerInterface
    {
        /**
         * @param Customer $customer
         * @throws RuntimeException
         */
        public function addMembership(Customer $customer): void
        {
            // ...
        }

        public function renewMembership(Customer $customer): void
        {
            // ...
        }

        public function hadMembership(Customer $customer): bool
        {
            // ...

            return false;
        }
    }

    class LoggingCustomerMemembershipManager implements CustomerMembershipManagerInterface
    {
        public function __construct(
            private CustomerMembershipManagerInterface $customerMembershipManager,
            private LoggerInterface $logger
        ) {}

        public function addMembership(Customer $customer): void
        {
            $this->logger->debug(sprintf('Adding membership for %s', $customer->getName()));
            try {
                $this->customerMembershipManager->addMembership($customer);
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

        public function renewMembership(Customer $customer): void
        {
            $this->logger->debug(sprintf('Renewing membership for %s', $customer->getName()));
            $this->customerMembershipManager->renewMembership($customer);
        }

        public function hadMembership(Customer $customer): bool
        {
            return $this->customerMembershipManager->hadMembership($customer);
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

namespace WrapClass\Test
{

    use Psr\Log\LoggerInterface;
    use WrapClass\App\Controller;
    use WrapClass\App\CustomerMembershipManager;
    use WrapClass\App\CustomerRepository;
    use WrapClass\App\FrontDesk;
    use WrapClass\App\LoggingCustomerMemembershipManager;
    use WrapClass\App\LoggingFrontDesk;
    use WrapClass\App\WelcomeMessageSender;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\RequestInterface;

    class ControllerTest extends TestCase
    {
        /**
         * @test
         * @doesNotPerformAssertions
         */
        public function canRequestAddMembershipsForNonLoggingClass(): void
        {
            $frontDesk = new FrontDesk(
                $this->createMock(CustomerRepository::class),
                $this->createMock(WelcomeMessageSender::class),
                $this->createMock(CustomerMembershipManager::class)
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
        public function canRequestAddMembershipsForLoggingClass(): void
        {
            $logger = $this->createMock(LoggerInterface::class);
            $customerMembershipManager = $this->createMock(CustomerMembershipManager::class);

            $frontDesk = new FrontDesk(
                $this->createMock(CustomerRepository::class),
                $this->createMock(WelcomeMessageSender::class),
                new LoggingCustomerMemembershipManager($customerMembershipManager, $logger)
            );
            $loggingFrontDesk = new LoggingFrontDesk($frontDesk, $logger);

            $controller = new Controller($loggingFrontDesk);
            $request = $this->createMock(RequestInterface::class);
            $request->method('getBody')->willReturn(['customerEmails' => []]);
            $controller->addMemberships($request);
        }
    }
}
