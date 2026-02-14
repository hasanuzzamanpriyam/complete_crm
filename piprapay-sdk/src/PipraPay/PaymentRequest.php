<?php

namespace PipraPay;

class PaymentRequest
{
    private $amount;
    private $currency = 'BDT';
    private $invoiceId;
    private $customerName;
    private $customerEmail;
    private $customerPhone;
    private $gateway = 'bkash';
    private $callbackUrl;
    private $successUrl;
    private $cancelUrl;
    private $description;
    private $metadata = [];

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        return $this;
    }

    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    public function setCustomerName($name)
    {
        $this->customerName = $name;
        return $this;
    }

    public function getCustomerName()
    {
        return $this->customerName;
    }

    public function setCustomerEmail($email)
    {
        $this->customerEmail = $email;
        return $this;
    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function setCustomerPhone($phone)
    {
        $this->customerPhone = $phone;
        return $this;
    }

    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    public function setCallbackUrl($url)
    {
        $this->callbackUrl = $url;
        return $this;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    public function setSuccessUrl($url)
    {
        $this->successUrl = $url;
        return $this;
    }

    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

    public function setCancelUrl($url)
    {
        $this->cancelUrl = $url;
        return $this;
    }

    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function addMetadata($key, $value)
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function toArray()
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'invoice_id' => $this->invoiceId,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'gateway' => $this->gateway,
            'callback_url' => $this->callbackUrl,
            'success_url' => $this->successUrl,
            'cancel_url' => $this->cancelUrl,
            'description' => $this->description,
            'metadata' => $this->metadata
        ];
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->amount) || $this->amount <= 0) {
            $errors[] = 'Amount must be greater than 0';
        }

        if (empty($this->currency)) {
            $errors[] = 'Currency is required';
        }

        if (empty($this->invoiceId)) {
            $errors[] = 'Invoice ID is required';
        }

        if (empty($this->gateway)) {
            $errors[] = 'Gateway is required';
        }

        if (empty($this->callbackUrl)) {
            $errors[] = 'Callback URL is required';
        }

        if (empty($this->successUrl)) {
            $errors[] = 'Success URL is required';
        }

        if (!filter_var($this->callbackUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Callback URL is invalid';
        }

        if (!filter_var($this->successUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Success URL is invalid';
        }

        if (!empty($this->cancelUrl) && !filter_var($this->cancelUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Cancel URL is invalid';
        }

        if (!empty($this->customerEmail) && !filter_var($this->customerEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Customer email is invalid';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function isValid(): bool
    {
        return $this->validate()['valid'];
    }
}
