<?php

declare(strict_types=1);

namespace App
{
    class User
    {
        public function __construct(private string $name) {}

        public function getName(): string { return $this->name; }
    }

    class UserRepository
    {
        public function __construct(private \Library\DBConnection $dBConnection) {}

        public function findByName(string $name): User
        {
            // ...

            // $this->dbConnection->executeQuery(/** ... */);

            // This would return a dynamic user
            return new User($name);
        }
    }

    class Payment
    {
        public function __construct(
            private User $payer,
            private User $payee,
            private int $cents,
            private string $currency = 'AUD'
        ) {}

        public function getPayer(): User { return $this->payer; }
        public function getPayee(): User { return $this->payee; }
        public function getCents(): int { return $this->cents; }
        public function getCurrency(): string { return $this->currency; }
    }

    class PaymentLedger
    {
        private $ledger = [];

        public function __construct(private \Library\DBConnection $dBConnection) {}

        public function lodge(Payment $payment): void
        {
            // This would actually use $this->dbConnection
            $this->ledger[] = $payment;
        }

        public function getPayment(int $paymentNumber): Payment
        {
            // This would actually use $this->dbConnection
            return $this->ledger[$paymentNumber];
        }
    }

    class PaymentGateway
    {
        protected PaymentLedger $ledger;
        protected UserRepository $userRepository;

        // Constraint: Must preserve signature
        public function __construct()
        {
            $this->setUp();
        }

        // This is our refactoring "scar"
        protected function setUp(): void
        {
            $dbConnection = new \Library\DBConnection();
            $this->userRepository = new UserRepository($dbConnection);
            $this->ledger = new PaymentLedger($dbConnection);
        }

        /**
         * Challenge:
         * - Add a currency argument to this method with an appropriate test.
         * - Do not use the UserRepository or PaymentLedger to test this class (that would
         *   take far too long to setup!!)
         * - The currency in use before this change was AUD.
         */
        public function makePayment(
            string $fromName,
            string $toName,
            int $cents,
            string $currency = 'AUD'
        ): void {
            // ...

            $fromUser = $this->userRepository->findByName($fromName);
            $toUser = $this->userRepository->findByName($toName);

            $payment = new Payment($fromUser, $toUser, $cents, $currency);

            $this->ledger->lodge($payment);
        }
    }
}

namespace Library
{
    class DBConnection
    {
        // ...

        public function executeCommand(string $query): void
        {
            // ...
        }

        public function executeQuery(string $query): array
        {
            // ...

            // This would return query results
            return [];
        }
    }
}

namespace Test
{
    use App;
    use PHPUnit\Framework\TestCase;
    use PHPUnit\Framework\MockObject\MockObject;

    class PaymentGateway extends App\PaymentGateway
    {
        // Allow us to override the $userRespository property
        public function setRepository(App\UserRepository $userRepository): void
        {
            $this->userRepository = $userRepository;
        }

        // Allow us to override the $ledger property
        public function setPaymentLedger(App\PaymentLedger $paymentLedger): void
        {
            $this->ledger = $paymentLedger;
        }

        protected function setUp(): void
        {
            // null method
            // This will stop the instantiation of the concrete PaymentLedger and UserRespository
        }
    }

    class PaymentGatewayTest extends TestCase
    {
        private PaymentGateway $paymentGateway;
        private App\PaymentLedger|MockObject $paymentLedger;

        public function setUp(): void
        {
            $this->paymentGateway = new PaymentGateway();
            $this->paymentLedger = $this->createMock(App\PaymentLedger::class);
            $this->userRepository = $this->createMock(App\UserRepository::class);
            $this->paymentGateway->setPaymentLedger($this->paymentLedger);
            $this->paymentGateway->setRepository($this->userRepository);
        }

        /** @test */
        public function canMakePaymentWithCurrency(): void
        {
            $this->paymentLedger
                ->expects($this->once())
                ->method('lodge')
                ->will($this->returnCallback(
                    fn (App\Payment $payment) => $this->assertEquals('WON', $payment->getCurrency())
                )
            );

            $this->paymentGateway->makePayment('Me', 'You', 70, 'WON');
        }
    }
}
