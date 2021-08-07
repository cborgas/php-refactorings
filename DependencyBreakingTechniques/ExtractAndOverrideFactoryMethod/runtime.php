<?php

declare(strict_types=1);

/**
 * Here is some code that is executed at runtime
 * 
 * Be careful not to break it...
 */
namespace Runtime
{
    // !! @todo Remove this when after.php is created
    require_once 'before.php';

    use App;
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
    }
}