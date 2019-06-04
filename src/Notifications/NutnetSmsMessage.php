<?php
/**
 * @author Maksim Khodyrev<maximkou@gmail.com>
 * 04.06.19
 */

namespace Nutnet\LaravelSms\Notifications;

/**
 * Class NutnetSmsMessage
 * @package Nutnet\LaravelSms\Notifications
 */
class NutnetSmsMessage
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var array
     */
    private $options = [];

    /**
     * NutnetSmsMessage constructor.
     * @param $text
     * @param array $options
     */
    public function __construct($text, array $options = [])
    {
        $this
            ->content($text)
            ->options($options);
    }

    /**
     * @param $text
     * @return $this
     */
    public function content($text)
    {
        $this->content = $text;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function options(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
