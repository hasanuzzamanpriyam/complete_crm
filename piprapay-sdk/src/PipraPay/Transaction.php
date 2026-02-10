<?php

namespace PipraPay;

class Transaction
{
    private $transactionId;
    private $amount;
    private $currency;
    private $status;
    private $gateway;
    private $invoiceId;
    private $customerName;
    private $customerEmail;
    private $customerPhone;
    private $createdAt;
    private $updatedAt;
    private $metadata;

    public function __construct(array $data = [])
    {
        $this->transactionId = $data['transaction_id'] ?? null;
        $this->amount = $data['amount'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->gateway = $data['gateway'] ?? null;
        $this->invoiceId = $data['invoice_id'] ?? null;
        $this->customerName = $data['customer_name'] ?? null;
        $this->customerEmail = $data['customer_email'] ?? null;
        $this->customerPhone = $data['customer_phone'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->metadata = $data['metadata'] ?? [];
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    public function getCustomerName()
    {
        return $this->customerName;
    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function isSuccess()
    {
        return $this->status === 'success' || $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed' || $this->status === 'cancelled';
    }

    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    public function toArray()
    {
        return [
            'transaction_id' => $this->transactionId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'gateway' => $this->gateway,
            'invoice_id' => $this->invoiceId,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'metadata' => $this->metadata
        ];
    }
}
