<?php

namespace PipraPay;

use InvalidArgumentException;
use ArrayIterator;

class GatewayCollection implements \IteratorAggregate, \Countable
{
    private array $gateways = [];
    private string $defaultCurrency = 'BDT';

    public function __construct(array $gateways = [])
    {
        foreach ($gateways as $gateway) {
            $this->addGateway($gateway);
        }
    }

    public function addGateway(Gateway $gateway): self
    {
        $this->gateways[$gateway->getCode()] = $gateway;
        return $this;
    }

    public function removeGateway(string $code): self
    {
        unset($this->gateways[$code]);
        return $this;
    }

    public function getGateway(string $code): ?Gateway
    {
        return $this->gateways[$code] ?? null;
    }

    public function hasGateway(string $code): bool
    {
        return isset($this->gateways[$code]);
    }

    public function getActive(): self
    {
        $activeGateways = array_filter($this->gateways, function ($gateway) {
            return $gateway->isActive();
        });

        return new self($activeGateways);
    }

    public function getByCurrency(string $currency): self
    {
        $supportedGateways = array_filter($this->gateways, function ($gateway) use ($currency) {
            return $gateway->supportsCurrency($currency);
        });

        return new self($supportedGateways);
    }

    public function getByRegion(string $region): self
    {
        $regionGateways = array_filter($this->gateways, function ($gateway) use ($region) {
            return strtolower($gateway->getRegion()) === strtolower($region);
        });

        return new self($regionGateways);
    }

    public function getByType(string $type): self
    {
        $typeGateways = array_filter($this->gateways, function ($gateway) use ($type) {
            return strtolower($gateway->getType()) === strtolower($type);
        });

        return new self($typeGateways);
    }

    public function withFeature(string $feature): self
    {
        $featureGateways = array_filter($this->gateways, function ($gateway) use ($feature) {
            return $gateway->hasFeature($feature);
        });

        return new self($featureGateways);
    }

    public function filter(callable $callback): self
    {
        $filtered = array_filter($this->gateways, $callback);
        return new self($filtered);
    }

    public function sortBy(string $field, string $direction = 'asc'): self
    {
        $sorted = $this->gateways;

        usort($sorted, function ($a, $b) use ($field, $direction) {
            $method = 'get' . ucfirst($field);

            if (!method_exists($a, $method) || !method_exists($b, $method)) {
                return 0;
            }

            $valueA = $a->$method();
            $valueB = $b->$method();

            if ($valueA == $valueB) {
                return 0;
            }

            $comparison = $valueA < $valueB ? -1 : 1;
            return $direction === 'desc' ? -$comparison : $comparison;
        });

        return new self($sorted);
    }

    public function toArray(): array
    {
        return array_map(function ($gateway) {
            return $gateway->toArray();
        }, array_values($this->gateways));
    }

    public function getCodes(): array
    {
        return array_keys($this->gateways);
    }

    public function getNames(): array
    {
        return array_map(function ($gateway) {
            return $gateway->getName();
        }, $this->gateways);
    }

    public function first(): ?Gateway
    {
        return reset($this->gateways) ?: null;
    }

    public function last(): ?Gateway
    {
        return end($this->gateways) ?: null;
    }

    public function isEmpty(): bool
    {
        return empty($this->gateways);
    }

    public function count(): int
    {
        return count($this->gateways);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->gateways);
    }

    public function setDefaultCurrency(string $currency): self
    {
        $this->defaultCurrency = strtoupper($currency);
        return $this;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function getForCurrency(string $currency = null): self
    {
        $currency = $currency ?? $this->defaultCurrency;
        return $this->getActive()->getByCurrency($currency);
    }

    public static function fromApiResponse(array $response): self
    {
        $gateways = [];

        foreach ($response as $gatewayData) {
            $gateways[] = Gateway::fromArray($gatewayData);
        }

        return new self($gateways);
    }

    public static function fromArray(array $data): self
    {
        return self::fromApiResponse($data);
    }

    public function merge(self $collection): self
    {
        $merged = $this->gateways;

        foreach ($collection as $gateway) {
            $code = $gateway->getCode();
            if (!$this->hasGateway($code) || $gateway->isActive()) {
                $merged[$code] = $gateway;
            }
        }

        return new self($merged);
    }

    public function find(callable $callback): ?Gateway
    {
        foreach ($this->gateways as $gateway) {
            if ($callback($gateway)) {
                return $gateway;
            }
        }

        return null;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->gateways);
    }
}
