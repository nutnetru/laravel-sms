<?php
namespace Nutnet\LaravelSms;

use Nutnet\LaravelSms\Contracts\Provider;

class SmsSender
{
	/**
	 * @param array<array-key, mixed> $defaultOptions
	 */
    public function __construct(private Provider $bridge, private array $defaultOptions = [])
    {
    }

	/**
	 * @param array<array-key, mixed> $options
	 */
    public function send(string $phone, string $message, array $options = []): bool
    {
        return $this->bridge->send(
            $this->preparePhone($phone),
            $message,
            array_merge($this->defaultOptions, $options)
        );
    }

	/**
	 * @param list<string> $phones
	 * @param array<array-key, mixed> $options
	 */
    public function sendBatch(array $phones, string $message, array $options = []): bool
    {
        return $this->bridge->sendBatch(
            array_map([$this, 'preparePhone'], $phones),
            $message,
            array_merge($this->defaultOptions, $options)
        );
    }

    private function preparePhone(string $phone): string
    {
        return (string)preg_replace('/[^\d]+/', '', $phone);
    }
}
