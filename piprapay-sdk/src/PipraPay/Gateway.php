<?php

namespace PipraPay;

class Gateway
{
    private string $code;
    private string $name;
    private bool $active;
    private string $icon;
    private array $currencies;
    private array $features;
    private array $metadata;
    private float $minAmount;
    private float $maxAmount;
    private string $region;
    private string $type;

    public function __construct(array $data = [])
    {
        $this->code = $data['code'] ?? '';
        $this->name = $data['name'] ?? '';
        $this->active = $data['active'] ?? false;
        $this->icon = $data['icon'] ?? '';
        $this->currencies = $data['currencies'] ?? [];
        $this->features = $data['features'] ?? [];
        $this->metadata = $data['metadata'] ?? [];
        $this->minAmount = $data['min_amount'] ?? 0.0;
        $this->maxAmount = $data['max_amount'] ?? PHP_FLOAT_MAX;
        $this->region = $data['region'] ?? 'global';
        $this->type = $data['type'] ?? 'payment';
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), array_map('strtoupper', $this->currencies));
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features);
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function getMinAmount(): float
    {
        return $this->minAmount;
    }

    public function getMaxAmount(): float
    {
        return $this->maxAmount;
    }

    public function isValidAmount(float $amount): bool
    {
        return $amount >= $this->minAmount && $amount <= $this->maxAmount;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'active' => $this->active,
            'icon' => $this->icon,
            'currencies' => $this->currencies,
            'features' => $this->features,
            'metadata' => $this->metadata,
            'min_amount' => $this->minAmount,
            'max_amount' => $this->maxAmount,
            'region' => $this->region,
            'type' => $this->type
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
