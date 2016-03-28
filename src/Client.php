<?php

/*
 * This file is part of the iguanair PHP library.
 *
 * (c) Martin Janser <martin@duss-janser.ch>
 *
 * This source file is subject to the GPL license that is bundled
 * with this source code in the file LICENSE.
 */

namespace IguanaIr;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class Client
{
    /**
     * @var string
     */
    private static $command = 'igclient';

    /**
     * @var string|null
     */
    private $device;

    /**
     * Sets the igclient command to use.
     *
     * @param string $command
     */
    public static function setCommand($command)
    {
        self::$command = $command;
    }

    /**
     * @param string|null $device Specific device to use
     */
    public function __construct($device = null)
    {
        $this->device = $device;
    }

    /**
     * Sends signals from a file to the specified channels.
     *
     * If no channels are specified the currently configured will be used.
     *
     * @param string $file     Path to the signal file to send
     * @param int[]  $channels List of channels to activate
     *
     * @throws CommandFailedException If the command failed.
     */
    public function send($file, array $channels = [])
    {
        $builder = $this->getProcessBuilder();

        if ($channels) {
            $builder->add('--channels='.$this->channelsToHex($channels));
        }
        $builder->add('--send='.$file);

        $process = $builder->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new CommandFailedException($process);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->device ?: '[default]';
    }

    /**
     * Creates and prepares a process builder.
     *
     * @return ProcessBuilder
     */
    private function getProcessBuilder()
    {
        $builder = ProcessBuilder::create(explode(' ', self::$command));
        $builder->setTimeout(3);

        if ($this->device) {
            $builder->add('--device='.$this->device);
        }

        return $builder;
    }

    /**
     * Converts a list of active channels to its hex representation.
     *
     * @param int[] $channels List of active channels
     *
     * @return string
     */
    private function channelsToHex(array $channels)
    {
        $binary = [];
        for ($index = 4; $index > 0; --$index) {
            $binary[] = in_array($index, $channels, true) ? '1' : '0';
        }

        $decimal = bindec(implode('', $binary));

        return sprintf('0x%02x', $decimal);
    }
}
