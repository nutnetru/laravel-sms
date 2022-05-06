<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Contracts;

interface Provider
{
	/**
	 * @param array<array-key, mixed> $options
	 */
    public function send(string $phone, string $message, array $options = []) : bool;

    /**
     * @param array<array-key, string> $phones
	 * @param array<array-key, mixed> $options
     */
    public function sendBatch(array $phones, string $message, array $options = []) : bool;
}
