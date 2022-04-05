<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 17.07.17
 */

namespace Nutnet\LaravelSms\Contracts;

interface Provider
{
    public function send(string $phone, string $message, array $options = []) : bool;

    /**
     * @param array<array-key, string> $phones
     */
    public function sendBatch(array $phones, string $message, array $options = []) : bool;
}
