<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 18.05.17
 */

namespace Nutnet\LaravelSms\Providers;

use Psr\Log\LoggerInterface as Writer;
use Nutnet\LaravelSms\Contracts\Provider;

class Log implements Provider
{
    private Writer $logWriter;

	/**
	 * @param array{channels?: null|string|list<string>}&array<array-key, mixed> $options
	 */
    public function __construct(Writer $logWriter, array $options = [])
    {
        $channels = $options['channels'] ?? [];

        if (!is_array($channels)) {
            $channels = [$channels];
        }

        // support for logging messages into custom channels
        if (!empty($channels)) {
            $logWriter = $this->makeStackedLogger($logWriter, $channels);
        }

        $this->logWriter = $logWriter;
    }

	/**
	 * @param array<array-key, mixed> $options
	 */
    public function send(string $phone, string $text, array $options = []) : bool
    {
        $this->getWriter()->debug(sprintf(
            'Sms is sent to %s: "%s"',
            $phone,
            $text
        ));

        return true;
    }

	/**
	 * @param array<array-key, mixed> $options
	 */
    public function sendBatch(array $phones, string $message, array $options = []) : bool
    {
        foreach ($phones as $phone) {
            $this->send($phone, $message);
        }

        return true;
    }

    public function getWriter(): Writer
    {
        return $this->logWriter;
    }

	/**
	 * @param list<string> $channels
	 */
    private function makeStackedLogger(Writer $logWriter, array $channels): Writer
    {
		if (!method_exists($logWriter, 'channel') || !method_exists($logWriter, 'stack')) {
			throw new \DomainException(sprintf(
				'Writer of type %s doesnt support channels.',
				get_class($logWriter)
			));
		}

        if (count($channels) > 1) {
            $logWriter = $logWriter->stack($channels);
        } else {
            $logWriter = $logWriter->channel(reset($channels));
        }

        return $logWriter;
    }
}
