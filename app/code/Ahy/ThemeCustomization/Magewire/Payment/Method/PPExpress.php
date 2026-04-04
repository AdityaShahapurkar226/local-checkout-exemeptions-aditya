<?php 
namespace Ahy\ThemeCustomization\Magewire\Payment\Method;

use Hyva\CheckoutPayPal\Magewire\Payment\Method\PPExpress as OriginalPPExpress;

class PPExpress extends OriginalPPExpress
{
    /**
     * @see \Magento\Paypal\Controller\Express\OnAuthorization::execute()
     */
    public function authorize(string $token, string $payer, string $funding = null)
    {
        try {
            $this->ppExpressPayment->authorize($token, $payer, $funding);
            // if ($this->ppExpressPayment->getCheckout()->canSkipOrderReviewStep()) {
                $this->placeOrderServiceProcessor->process($this, $this->ppExpressPayment->getQuote());
            // }
        } catch (ApiProcessableException $exception) {
            $this->dispatchErrorMessage($exception->getUserMessage());
        } catch (LocalizedException $exception) {
            $this->dispatchErrorMessage($exception->getMessage());
        } catch (Exception $exception) {
            $this->dispatchErrorMessage('We can\'t process Express Checkout approval.');
        }
    }
}
?>