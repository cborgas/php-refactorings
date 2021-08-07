<?php

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
            private int $cents
        ) {}

        public function getPayer(): User { return $this->payer; }
        public function getPayee(): User { return $this->payee; }
        public function getCents(): int { return $this->cents; }
    }

    class PaymentLedger
    {
        private $ledger = [];

        public function __construct(\Library\DBConnection $dBConnection) {}

        public function lodge(Payment $payment): void
        {
            // This would actually use the dbConnection
            $this->ledger[] = $payment;
        }

        public function getPayment(int $paymentNumber): Payment
        {
            // This would actually use the dbConnection
            return $this->ledger[$paymentNumber];
        }
    }

    class PaymentGateway
    {
        // Must preserve signature
        public function __construct()
        {
            /**
             * This hard-coded initialization inside the constructor is the 
             * issue we're trying to solve
             */
            $dbConnection = new \Library\DBConnection();
            $this->repository = new UserRepository($dbConnection);
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
            int $cents
        ): void {
            // ...

            $fromUser = $this->repository->findByName($fromName);
            $toUser = $this->repository->findByName($toName);

            $payment = new Payment($fromUser, $toUser, $cents);

            $this->ledger->lodge($payment);
        }
    }
}

/**
 * Here is some code that is executed at runtime
 * 
 * Be careful not to break it...
 */
namespace Runtime
{
    use App;

    class PaymentGateway extends App\PaymentGateway
    {
        public function getLedger(): App\PaymentLedger { return $this->ledger; }
    }

    $paymentGateway = new PaymentGateway();
    $paymentGateway->makePayment('Me', 'You', 100); // $1.00 from me to you

    $lodgedPayment = $paymentGateway->getLedger()->getPayment(0);
    assert('Me' === $lodgedPayment->getPayer()->getName());
    assert('You' === $lodgedPayment->getPayee()->getName());
    assert(100 === $lodgedPayment->getCents());
}
