<?php

declare(strict_types=1);

namespace App
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

namespace Test
{
    use App\Controller;
    use App\CustomerRepository;
    use App\FrontDesk;
    use App\WelcomeMessageSender;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\RequestInterface;

    class ControllerTest extends TestCase
    {
        /**
         * @test
         * @doesNotPerformAssertions
         */
        public function canRequestAddMemberships(): void
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
    }
}
