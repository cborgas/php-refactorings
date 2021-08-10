import { testing } from './deps.ts';
import { App } from './index.ts';

const { assertEquals } = testing;

namespace RunTime {
  export class PaymentGateway extends App.PaymentGateway {
    public getLedger(): App.PaymentLedger {
      return this.ledger;
    }
  }

  export class PaymentGatewayTest {
    public regression(): void {
      const paymentGateway = new RunTime.PaymentGateway();
      paymentGateway.makePayment('Me', 'You', 100, 'AUD');
      const lodgedPayment = paymentGateway.getLedger().getPayment(0);

      Deno.test('must return the payer name', (): void => {
        assertEquals('Me', lodgedPayment.getPayer().getName());
      });
      Deno.test('must return the payee name', (): void => {
        assertEquals('You', lodgedPayment.getPayee().getName());
      });
      Deno.test('must return the cents', (): void => {
        assertEquals(100, lodgedPayment.getCents());
      });
    }
  }
}

new RunTime.PaymentGatewayTest().regression();
