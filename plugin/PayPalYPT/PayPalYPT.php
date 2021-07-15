<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/PayPalYPT/Objects/PayPalYPT_log.php';

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Api\ShippingAddress;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use PaypalPayoutsSDK\Payouts\PayoutsGetRequest;

require_once $global['systemRootPath'] . 'plugin/PayPalYPT/PayPalClient.php';

class PayPalYPT extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        return "Paypal module for several purposes<br>
            Go to Paypal developer <a href='https://developer.paypal.com/developer/applications' target='_blank'>Site here</a> (you must have Paypal account, of course)
    <br>Click on Create App on right side of page
    <br>Choose name of your app and click Create App
    <br>Now you can see and manage everything include client ID and secret.";
    }

    public function getName() {
        return "PayPalYPT";
    }

    public function getUUID() {
        return "5f613a09-c0b6-4264-85cb-47ae076d949f";
    }

    public function getPluginVersion() {
        return "2.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->ClientID = "ASUkHFpWX0T8sr8EiGdLZ05m-RAb8l-hdRxoq-OXWmua2i7EUfqFkMZvSoGgH2LhK7zAqt29IiS2oRTn";
        $obj->ClientSecret = "ECxtMBsLr0cFwSCgI0uaDiVzEUbVlV3r_o_qaU-SOsQqCEOKPq4uGlr1C0mhdDmEyO30mw7-PF0bOnfo";
        $obj->subscriptionButtonLabel = "Subscribe With PayPal";
        $obj->paymentButtonLabel = "Pay With PayPal";
        $obj->ClientSecret = "ECxtMBsLr0cFwSCgI0uaDiVzEUbVlV3r_o_qaU-SOsQqCEOKPq4uGlr1C0mhdDmEyO30mw7-PF0bOnfo";
        $obj->disableSandbox = false;
        return $obj;
    }

    public function setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $description = "") {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";
        // After Step 2
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal($total);
        $amount->setCurrency($currency);

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount);
        $transaction->setNotifyUrl($notify_url);
        $transaction->setInvoiceNumber($invoiceNumber);
        $transaction->setDescription($description);

        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl($redirect_url)
                ->setCancelUrl($cancel_url);

        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions(array($transaction))
                ->setRedirectUrls($redirectUrls);

        // After Step 3
        try {
            $payment->create($apiContext);
            return $payment;
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            _error_log("PayPal Error: " . $ex->getData());
        }
        return false;
    }

    public function getPlanDetails($plan_id) {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        try {
            $plan = Plan::get($plan_id, $apiContext);
        } catch (Exception $ex) {
            return $ex;
        }
        return $plan;
    }

    private function executePayment() {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        // ### Approval Status
        // Determine if the user approved the payment or not
        // Get the payment Object by passing paymentId
        // payment id was previously stored in session in
        // CreatePaymentUsingPayPal.php
        $paymentId = $_GET['paymentId'];
        $payment = Payment::get($paymentId, $apiContext);
        $amount = self::getAmountFromPayment($payment);
        $total = $amount->total;
        $currency = $amount->currency;
        // ### Payment Execute
        // PaymentExecution object includes information necessary
        // to execute a PayPal account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from paypal back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        // ### Optional Changes to Amount
        // If you wish to update the amount that you wish to charge the customer,
        // based on the shipping address or any other reason, you could
        // do that by passing the transaction object with just `amount` field in it.
        // Here is the example on how we changed the shipping to $1 more than before.
        $transaction = new Transaction();
        $amount = new Amount();
        //$details = new Details();
        $amount->setCurrency($currency);
        $amount->setTotal($total);
        //$amount->setDetails($details);
        $transaction->setAmount($amount);
        // Add the above transaction object inside our Execution object.
        $execution->addTransaction($transaction);
        try {
            // Execute the payment
            // (See bootstrap.php for more on `ApiContext`)
            $result = $payment->execute($execution, $apiContext);
            try {
                $payment = Payment::get($paymentId, $apiContext);
            } catch (Exception $ex) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }
        return $payment;
    }

    private function createBillingPlan($redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement', $plans_id = 0) {
        global $global;
        _error_log("createBillingPlan: start: " . json_encode(array($redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name)));

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";
        // Create a new billing plan
        $plan = new Plan();
        $plan->setName(substr(cleanString($name), 0, 126))
                ->setDescription(substr(cleanString($name), 0, 126))
                ->setType('INFINITE');

        $paymentDefinitionArray = array();

        if (!empty($plans_id)) {
            $subs = new SubscriptionPlansTable($plans_id);
            $trialDays = $subs->getHow_many_days_trial();
            if (!empty($trialDays)) {
                $trialPaymentDefinition = new PaymentDefinition();
                $trialPaymentDefinition->setName('Trial Payment')
                        ->setType('TRIAL')
                        ->setFrequency('Day')
                        ->setFrequencyInterval($trialDays)
                        ->setCycles("1")
                        ->setAmount(new Currency(array('value' => 0, 'currency' => $currency)));
                $paymentDefinitionArray[] = $trialPaymentDefinition;
            }
        }

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
                ->setType('REGULAR')
                ->setFrequency($frequency)
                ->setFrequencyInterval($interval)
                ->setCycles('0')
                ->setAmount(new Currency(array('value' => $total, 'currency' => $currency)));
        $paymentDefinitionArray[] = $paymentDefinition;

        $plan->setPaymentDefinitions($paymentDefinitionArray);

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        // if there is a trial do not charge a setup fee
        if (empty($trialDays)) {
            $merchantPreferences->setReturnUrl($redirect_url)
                    ->setCancelUrl($cancel_url)
                    //->setNotifyUrl($notify_url)
                    ->setAutoBillAmount('yes')
                    ->setInitialFailAmountAction('CONTINUE')
                    ->setMaxFailAttempts('0')
                    ->setSetupFee(new Currency(array('value' => $total, 'currency' => $currency)));
        } else {
            $merchantPreferences->setReturnUrl($redirect_url)
                    ->setCancelUrl($cancel_url)
                    //->setNotifyUrl($notify_url)
                    ->setAutoBillAmount('yes')
                    ->setInitialFailAmountAction('CONTINUE')
                    ->setMaxFailAttempts('0');
        }
        $plan->setMerchantPreferences($merchantPreferences);

        //create plan
        try {
            $createdPlan = $plan->create($apiContext);

            try {
                $patch = new Patch();
                $value = new PayPalModel('{"state":"ACTIVE"}');
                $patch->setOp('replace')
                        ->setPath('/')
                        ->setValue($value);
                $patchRequest = new PatchRequest();
                $patchRequest->addPatch($patch);
                $createdPlan->update($patchRequest, $apiContext);

                $plan = Plan::get($createdPlan->getId(), $apiContext);
                _error_log("createBillingPlan: " . json_encode(array($redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name)));
                // Output plan id
                return $plan;
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                _error_log("PayPal Error createBillingPlan 1: " . $ex->getData());
            } catch (Exception $ex) {
                _error_log("PayPal Error createBillingPlan 2: " . $ex->getData());
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("PayPal Error createBillingPlan 3: " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("PayPal Error createBillingPlan 4: " . $ex->getData());
        }
        return false;
    }

    private function getPlanId() {
        global $global;
        if (!empty($_POST['plans_id'])) {
            $s = new SubscriptionPlansTable($_POST['plans_id']);
            $plan_id = $s->getPaypal_plan_id();
            require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
            try {
                $plan = Plan::get($plan_id, $apiContext);
                if (!empty($plan)) {
                    return $plan->getId();
                }
            } catch (Exception $ex) {
                return false;
            }
        }
        return false;
    }

    public function setUpSubscription($invoiceNumber, $redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipn.php";

        $planId = $this->getPlanId();
        if (empty($planId)) {
            //createBillingPlan($redirect_url, $cancel_url, $total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = 'Base Agreement') 
            $plan = $this->createBillingPlan($redirect_url, $cancel_url, $total, $currency, $frequency, $interval, $name, $_POST['plans_id']);

            if (empty($plan)) {
                _error_log("PayPal Error setUpSubscription Plan ID is empty ");
                return false;
            }
            $planId = $plan->getId();
            // save the paypal plan ID for reuse
            if (!empty($_POST['plans_id'])) {
                $s = new SubscriptionPlansTable($_POST['plans_id']);
                $s->setPaypal_plan_id($planId);
                $s->save();
            }
        }
        // Create new agreement
        // the setup fee will be the first payment and start date is the next payment

        $subs = new SubscriptionPlansTable($_POST['plans_id']);
        if (!empty($subs)) {
            $trialDays = $subs->getHow_many_days_trial();
        }
        if (!empty($trialDays)) {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+12 hour"));
        } else {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+{$interval} {$frequency}"));
        }
        $agreement = new Agreement();
        $agreement->setName(substr(cleanString($name), 0, 126))
                ->setDescription(substr(cleanString($name), 0, 126))
                ->setStartDate($startDate);

        $plan = new Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        // Add payer type
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            // Create agreement
            $agreement = $agreement->create($apiContext);

            // Extract approval URL to redirect user
            return $agreement;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("PayPal Error createBillingPlan 5: startDate: {$startDate} " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("PayPal Error createBillingPlan 6: startDate: {$startDate} " . $ex->getData());
        }
        return false;
    }

    public function setUpSubscriptionV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $trialDays = 0) {
        global $global;

        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        //createBillingPlanV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $trialDays = 0)
        $plan = $this->createBillingPlanV2($total, $currency, $frequency, $interval, $name, $json, $trialDays);

        if (empty($plan)) {
            _error_log("setUpSubscriptionV2: PayPal Error setUpSubscription Plan ID is empty ");
            return false;
        }
        $planId = $plan->getId();
        
        // Create new agreement
        // the setup fee will be the first payment and start date is the next payment
        if (!empty($trialDays)) {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+12 hour"));
        } else {
            $startDate = date("Y-m-d\TH:i:s.000\Z", strtotime("+{$interval} {$frequency}"));
        }
        $agreement = new Agreement();
        $agreement->setName(substr(cleanString($name), 0, 126))
                ->setDescription(substr(cleanString($json), 0, 126))
                ->setStartDate($startDate);

        $plan = new Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        // Add payer type
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        try {
            // Create agreement
            $agreement = $agreement->create($apiContext);

            // Extract approval URL to redirect user
            return $agreement;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("setUpSubscriptionV2: PayPal Error createBillingPlan 5: startDate: {$startDate} " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("setUpSubscriptionV2: PayPal Error createBillingPlan 6: startDate: {$startDate} " . $ex->getData());
        }
        return false;
    }

    private function createBillingPlanV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $trialDays = 0) {
        global $global;
        $currency = strtoupper($currency);
        _error_log("createBillingPlanV2: createBillingPlan: start: " . json_encode(array($total, $currency, $frequency, $interval, $name, $trialDays)));
        
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $notify_url = "{$global['webSiteRootURL']}plugin/PayPalYPT/ipnV2.php";
        $notify_url = addQueryStringParameter($notify_url, 'json', $json);
        
        $cancel_url = addQueryStringParameter($notify_url, 'success', 0);
        $success_url = addQueryStringParameter($notify_url, 'success', 1);
        
        // Create a new billing plan
        $plan = new Plan();
        $plan->setName(substr(cleanString($name), 0, 126))
                ->setDescription(substr(cleanString($name), 0, 126))
                ->setType('INFINITE');

        $paymentDefinitionArray = array();

        if (!empty($trialDays)) {
            $trialPaymentDefinition = new PaymentDefinition();
            $trialPaymentDefinition->setName('Trial Payment')
                    ->setType('TRIAL')
                    ->setFrequency('Day')
                    ->setFrequencyInterval($trialDays)
                    ->setCycles("1")
                    ->setAmount(new Currency(array('value' => 0, 'currency' => $currency)));
            $paymentDefinitionArray[] = $trialPaymentDefinition;
        }

        // Set billing plan definitions
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
                ->setType('REGULAR')
                ->setFrequency($frequency)
                ->setFrequencyInterval($interval)
                ->setCycles('0')
                ->setAmount(new Currency(array('value' => $total, 'currency' => $currency)));
        $paymentDefinitionArray[] = $paymentDefinition;

        $plan->setPaymentDefinitions($paymentDefinitionArray);

        // Set merchant preferences
        $merchantPreferences = new MerchantPreferences();
        // if there is a trial do not charge a setup fee
        if (empty($trialDays)) {
            $merchantPreferences->setReturnUrl($success_url)
                    ->setCancelUrl($cancel_url)
                    ->setNotifyUrl($notify_url)
                    ->setAutoBillAmount('yes')
                    ->setInitialFailAmountAction('CONTINUE')
                    ->setMaxFailAttempts('0')
                    ->setSetupFee(new Currency(array('value' => $total, 'currency' => $currency)));
        } else {
            $merchantPreferences->setReturnUrl($success_url)
                    ->setCancelUrl($cancel_url)
                    ->setNotifyUrl($notify_url)
                    ->setAutoBillAmount('yes')
                    ->setInitialFailAmountAction('CONTINUE')
                    ->setMaxFailAttempts('0');
        }
        $plan->setMerchantPreferences($merchantPreferences);

        //create plan
        try {
            $createdPlan = $plan->create($apiContext);

            try {
                $patch = new Patch();
                $value = new PayPalModel('{"state":"ACTIVE"}');
                $patch->setOp('replace')
                        ->setPath('/')
                        ->setValue($value);
                $patchRequest = new PatchRequest();
                $patchRequest->addPatch($patch);
                $createdPlan->update($patchRequest, $apiContext);

                $plan = Plan::get($createdPlan->getId(), $apiContext);
                _error_log("createBillingPlanV2: createBillingPlan: " . json_encode(array($total, $currency, $frequency, $interval, $name, $trialDays, $plan)));
                // Output plan id
                return $plan;
            } catch (PayPal\Exception\PayPalConnectionException $ex) {
                _error_log("createBillingPlanV2: PayPal Error createBillingPlan 1: " . $ex->getData());
            } catch (Exception $ex) {
                _error_log("createBillingPlanV2: PayPal Error createBillingPlan 2: " . $ex->getData());
            }
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("createBillingPlanV2: PayPal Error createBillingPlan 3: " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("createBillingPlanV2: PayPal Error createBillingPlan 4: " . $ex->getData());
        }
        return false;
    }

    private function executeBillingAgreement() {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        $token = $_GET['token'];
        $agreement = new \PayPal\Api\Agreement();

        try {
            // Execute agreement
            _error_log("PayPal Try to execute ");
            $agreement->execute($token, $apiContext);
            return $agreement;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            _error_log("PayPal Error executeBillingAgreement: " . $ex->getData());
        } catch (Exception $ex) {
            _error_log("PayPal Error executeBillingAgreement: " . $ex);
        }
        return false;
    }

    static function getBillingAgreement($agreement_id) {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';
        return Agreement::get($agreement_id, $apiContext);
    }

    static function cancelAgreement($agreement_id) {
        global $global;
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        //Create an Agreement State Descriptor, explaining the reason to suspend.
        $agreementStateDescriptor = new AgreementStateDescriptor();
        $agreementStateDescriptor->setNote("Canceled by user");

        $createdAgreement = self::getBillingAgreement($agreement_id);

        try {
            $createdAgreement->suspend($agreementStateDescriptor, $apiContext);
            // Lets get the updated Agreement Object
            return Agreement::get($createdAgreement->getId(), $apiContext);
        } catch (Exception $ex) {
            return false;
        }
    }

    function execute() {
        if (!empty($_GET['paymentId'])) {
            _error_log("PayPal Execute payment ");
            return $this->executePayment();
        } else if (!empty($_GET['token'])) {
            _error_log("PayPal Billing Agreement ");
            return $this->executeBillingAgreement();
        }
        _error_log("PayPal no payment to execute ");
        return false;
    }

    static function getAmountFromPayment($payment) {
        if (!is_object($payment)) {
            return false;
        }
        if (get_class($payment) === 'PayPal\Api\Agreement') {
            $amount = new stdClass();
            //_error_log("getAmountFromPayment: ".json_encode($payment));
            //_error_log("getAmountFromPayment: ". print_r($payment, true));
            //_error_log("getAmountFromPayment: ".($payment->getId()));
            //_error_log("getAmountFromPayment: ".($payment->getPlan()));
            //_error_log("getAmountFromPayment: ".($payment->getPlan()->payment_definitions->amount->value));
            //_error_log("getAmountFromPayment: ".($payment->getPlan()->merchant_preferences->setup_fee->value));
            //$amount->total = $payment->agreement_details->last_payment_amount->value;
            if (!empty(@$payment->getPlan()->payment_definitions->amount->value)) {
                $amount->total = $payment->getPlan()->payment_definitions->amount->value;
            } else if (!empty(@$payment->getPlan()->merchant_preferences->setup_fee->value)) {
                $amount->total = $payment->getPlan()->merchant_preferences->setup_fee->value;
            } else {
                $amount->total = 0;
            }
            $amount->total = floatval($amount->total);
            return $amount;
        } else {
            return $payment->getTransactions()[0]->amount;
        }
    }

    function sendToPayPal($invoiceNumber, $redirect_url, $cancel_url, $total, $currency) {
        $payment = $this->setUpPayment($invoiceNumber, $redirect_url, $cancel_url, $total, $currency);
        if (!empty($payment)) {
            header("Location: {$payment->getApprovalLink()}");
            exit;
        }
    }

    static function updateBillingPlan($plan_id, $total = '1.00', $currency = "USD", $interval = 1, $name = 'Base Agreement') {
        global $global;
        if (empty($plan_id)) {
            return false;
        }
        require $global['systemRootPath'] . 'plugin/PayPalYPT/bootstrap.php';

        $createdPlan = Plan::get($plan_id, $apiContext);

        try {
            $patch1 = new Patch();
            $patch1->setOp('replace')
                    ->setPath('/')
                    ->setValue(_json_decode('{"name": "' . $name . '"}'));

            $paymentDefinitions = $createdPlan->getPaymentDefinitions();
            $paymentDefinition = $paymentDefinitions[0];
            $paymentDefinitionId = $paymentDefinition->getId();

            $patch2 = new Patch();
            $patch2->setOp('replace')
                    ->setPath('/payment-definitions/' . $paymentDefinitionId)
                    ->setValue(_json_decode('{
                                                "amount": {
                                                    "currency": "' . $currency . '",
                                                    "value": "' . $total . '"
                                                },
                                                "frequency_interval": "' . $interval . '"
                                            }'));
            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch1);
            $patchRequest->addPatch($patch2);

            $createdPlan->update($patchRequest, $apiContext);

            return Plan::get($createdPlan->getId(), $apiContext);
        } catch (Exception $ex) {
            _error_log("PayPal Error updateBillingPlan: " . $ex->getData());
        }
        return false;
    }

    static function IPNcheck() {
        $obj = AVideoPlugin::getDataObject('PayPalYPT');
        
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Step 2: POST IPN data back to PayPal to validate
        $ipnURL = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        if(!empty($obj->disableSandbox)){
            $ipnURL = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        }
        _error_log("IPNcheck: URL {$ipnURL} [$req]");
        
        $ch = curl_init($ipnURL);        
        //curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-IPN-VerificationScript');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: PHP-IPN-Verification-Script',
            'Connection: Close',
        ));
        // In wamp-like environments that do not come bundled with root authority certificates,
        // please download 'cacert.pem' from "https://curl.haxx.se/docs/caextract.html" and set
        // the directory path of the certificate as shown below:
        // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
        if (!($res = curl_exec($ch))) {
            _error_log("IPNcheck: Got " . curl_error($ch) . " when processing IPN data");
            curl_close($ch);
            exit;
        }
        // inspect IPN validation result and act accordingly
        if (strcmp($res, "VERIFIED") == 0) {
            _error_log("IPNcheck SUCCESS: The response from IPN was: " . $res . "");
            return true;
        } else if (strcmp($res, "INVALID") == 0) {
            // IPN invalid, log for manual investigation
            _error_log("IPNcheck ERROR: The response from IPN was: " . $res . "");
            return false;
        }
        _error_log("IPNcheck ERROR: Unknow response from IPN : " . $res . "");
        return false;
        curl_close($ch);
    }

    public static function setUserReceiverEmail($users_id, $email) {
        $user = new User($users_id);
        $paramName = 'PayPalReceiverEmail';
        return $user->addExternalOptions($paramName, $email);
    }

    public static function getUserReceiverEmail($users_id) {
        $user = new User($users_id);
        $paramName = 'PayPalReceiverEmail';
        return $user->getExternalOption($paramName);
    }

    public function getMyAccount($users_id) {
        global $global;

        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($obj) || empty($obj->enableAutoWithdrawFundsPagePaypal)) {
            return '';
        }

        include $global['systemRootPath'] . 'plugin/PayPalYPT/payOutReceiverEmailForm.php';
    }

    public static function WalletPayout($users_id_to_be_paid, $value) {
        global $config, $global;
        $obj = new stdClass();
        $obj->error = true;
        $obj->msg = '';
        $obj->response = false;

        if (empty($value)) {
            $obj->msg = 'value is empty';
            return $obj;
        }

        $wallet = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($wallet)) {
            $obj->msg = 'YPTWallet plugin is disabled';
            return $obj;
        }

        // check if the user has a paypal email
        $receiver_email = self::getUserReceiverEmail($users_id_to_be_paid);
        if (empty($receiver_email)) {
            $obj->msg = "The user {$users_id_to_be_paid} does not have a paypal receiver email";
            return $obj;
        }
        mysqlBeginTransaction();
        // transfer money from wallet
        $description = "Paypal payout to {$receiver_email} [users_id=$users_id_to_be_paid]";
        $transfer = YPTWallet::transferBalanceToSiteOwner($users_id_to_be_paid, $value, $description, true);
        if ($transfer) {
            $email_subject = $note = "You received " . YPTWallet::formatCurrency($value) . " from " . $config->getWebSiteTitle() . " ";
            // payout using paypal
            $obj->response = self::Payout($receiver_email, $value, $wallet->currency, $note, $email_subject);
            if (empty($obj->response) || !empty($obj->response->error)) {
                $description = "Paypal refund";
                $obj->msg = 'PayPal Payout error: ' . $obj->response->msg;
                $transfer = YPTWallet::transferBalanceFromSiteOwner($users_id_to_be_paid, $value, $description, true);
                mysqlRollback();
                return $obj;
            }else{
                $payout_batch_id = $obj->response->response->result->batch_header->payout_batch_id;
                $paymentLink = "<br><button class='btn btn-xs btn-default' onclick='avideoModalIframeSmall(\"{$global['webSiteRootURL']}plugin/PayPalYPT/payout.php?payout_batch={$payout_batch_id}\");'>PayPal Info</button>";
                $description .= $paymentLink;
                YPTWallet::setLogInfo($transfer, $obj->response);
                YPTWallet::setLogDescription($transfer, $description);
            }

            $obj->error = false;
        }

        mysqlCommit();
        return $obj;
    }

    public static function Payout($receiver_email, $value, $currency = 'USD', $note = '', $email_subject = '') {
        $obj = new stdClass();
        $obj->msg = '';
        $obj->error = true;
        $obj->response = false;

        if (empty($value)) {
            $obj->msg = 'PayPal::Payout value is empty';
            _error_log($obj->msg);
            return $obj;
        }

        if (empty($receiver_email)) {
            $obj->msg = "PayPal::Payout The user {$users_id_to_be_paid} does not have a paypal receiver email";
            _error_log($obj->msg);
            return $obj;
        }

        $wallet = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($wallet) || empty($wallet->enableAutoWithdrawFundsPagePaypal)) {
            $obj->msg = 'PayPal::Payout Wallet enableAutoWithdrawFundsPagePaypal is disabled';
            _error_log($obj->msg);
            return $obj;
        }
        try {
            $request = new PaypalPayoutsSDK\Payouts\PayoutsPostRequest();
            $request->body = new stdClass();
            $request->body->sender_batch_header = new stdClass();
            $request->body->sender_batch_header->email_subject = $email_subject;
            $item = new stdClass();
            $item->recipient_type = 'EMAIL';
            $item->receiver = $receiver_email;
            $item->note = $note;
            $item->amount = new stdClass();
            $item->amount->currency = $currency;
            $item->amount->value = $value;
            $request->body->items = array($item);

            $request->body = object_to_array($request->body);

            $client = PayPalClient::client();
            $obj->response = $client->execute($request);
            // To toggle printing the whole response body comment/uncomment below line
            $msg = json_encode($obj->response, JSON_PRETTY_PRINT) . PHP_EOL;
            $obj->msg = 'PayPal::Payout ' . $msg;
            _error_log($obj->msg);
            if(is_object($obj->response) && $obj->response->statusCode ==  201){
                $obj->error = false;
            }
            return $obj;
        } catch (HttpException $e) {
            $msg = '';
            //Parse failure response
            $msg .= $e->getMessage() . PHP_EOL;
            $error = json_decode($e->getMessage());
            $msg .= $error->message . PHP_EOL;
            $msg .= $error->name . PHP_EOL;
            $msg .= $error->debug_id . PHP_EOL;
            $obj->msg = 'PayPal::Payout ' . $msg;
            _error_log($obj->msg);
        }
        return $obj;
    }
    
    public static function getPayoutInfo($payout_batch_id) {
        try {
            $request = new PaypalPayoutsSDK\Payouts\PayoutsGetRequest($payout_batch_id);
            $client = PayPalClient::client();
            return $client->execute($request);
        } catch (HttpException $e) {
            $msg = '';
            //Parse failure response
            $msg .= $e->getMessage() . PHP_EOL;
            $error = json_decode($e->getMessage());
            $msg .= $error->message . PHP_EOL;
            $msg .= $error->name . PHP_EOL;
            $msg .= $error->debug_id . PHP_EOL;
            $obj->msg = 'PayPal::PayoutInfo ' . $msg;
            _error_log($obj->msg);
        }
        return $obj;
    }

    public function getWalletConfigurationHTML($users_id, $wallet, $walletDataObject) {
        global $global;
        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (empty($obj->enableAutoWithdrawFundsPagePaypal)) {
            return '';
        }
        include_once $global['systemRootPath'] . 'plugin/PayPalYPT/getWalletConfigurationHTML.php';
    }
    
    
    public function getPluginMenu() {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/PayPalYPT/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';        
    }
    
    static function isTokenUsed($token){
        $row = PayPalYPT_log::getFromToken($token);
        return !empty($row);
    }
    
    static function isRecurringPaymentIdUsed($recurring_payment_id){
        $row = PayPalYPT_log::getFromRecurringPaymentId($recurring_payment_id);
        return !empty($row);
    }

}
