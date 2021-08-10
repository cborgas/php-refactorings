<?php

declare(strict_types=1);

/**
 * Here is some code that is executed at runtime
 * 
 * Be careful not to break it...
 */
namespace Runtime
{
    if (!file_exists(__DIR__ . '/after.php')) {
        require_once 'before.php';
    }
    
    use App;
    use Library;
    use PHPUnit\Framework\TestCase;

    class PaymentGateway extends App\PaymentGateway
    {
        public function getLedger(): App\PaymentLedger { return $this->ledger; }
    }

    class PaymentGatewayTest extends TestCase
    {
        /** @test */
        public function regression(): void
        {
            $paymentGateway = new PaymentGateway();
            $paymentGateway->makePayment('Me', 'You', 100); // $1.00 from me to you

            $lodgedPayment = $paymentGateway->getLedger()->getPayment(0);

            $this->assertEquals('Me', $lodgedPayment->getPayer()->getName());
            $this->assertEquals('You', $lodgedPayment->getPayee()->getName());
            $this->assertEquals(100, $lodgedPayment->getCents());
        }

        /**
         * @test
         * @doesNotPerformAssertions
         */
        public function signaturesArePreserved(): void
        {
            $dbConnection = new Library\DBConnection();
            new App\UserRepository($dbConnection);
            new App\PaymentLedger($dbConnection);
            new App\Payment(new App\User(''), new App\User(''), 0);
            new App\PaymentGateway();
        }
    }
}
