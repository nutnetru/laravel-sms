<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 04.06.19
 */

namespace Nutnet\LaravelSms\Notifications;

class NutnetSmsMessage
{
    private string $content;

    private array $options = [];

    public function __construct(string $text, array $options = [])
    {
        $this
            ->content($text)
            ->options($options);
    }

    public function content(string $text): static
    {
        $this->content = $text;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
