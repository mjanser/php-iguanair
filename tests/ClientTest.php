<?php

/*
 * This file is part of the iguanair PHP library.
 *
 * (c) Martin Janser <martin@duss-janser.ch>
 *
 * This source file is subject to the GPL license that is bundled
 * with this source code in the file LICENSE.
 */

namespace IguanaIr\tests;

use IguanaIr\Client;
use IguanaIr\CommandFailedException;
use Symfony\Component\Process\Process;

/**
 * @covers IguanaIr\Client::__construct
 * @covers IguanaIr\Client::setCommand
 * @covers IguanaIr\Client::<private>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $commandFilename;

    /**
     * @var string
     */
    private $callCountFilename;

    /**
     * @var int
     */
    private $callCount = 1;

    protected function setUp()
    {
        $this->callCount = 1;
    }

    protected function tearDown()
    {
        if ($this->commandFilename) {
            unlink($this->commandFilename);
            $this->commandFilename = null;
        }
        if ($this->callCountFilename) {
            unlink($this->callCountFilename);
            $this->callCountFilename = null;
        }
    }

    /**
     * @covers IguanaIr\Client::__toString
     */
    public function testDeviceName()
    {
        $client = new Client('my-device');

        $this->assertSame('my-device', (string) $client, 'Device name should match');
    }

    /**
     * @covers IguanaIr\Client::__toString
     */
    public function testDefaultDeviceName()
    {
        $client = new Client();

        $this->assertSame('[default]', (string) $client, 'Device name should match');
    }

    /**
     * @covers IguanaIr\Client::send
     */
    public function testSendSignal()
    {
        $client = $this->getMockedClient();

        $this->expectCall(['--send=signals.txt'], 0);
        $this->expectNoOtherCalls();

        $client->send('signals.txt');

        $this->assertTrue(true);
    }

    /**
     * @covers IguanaIr\Client::send
     */
    public function testSendSignalWithDevice()
    {
        $client = $this->getMockedClient('my-device');

        $this->expectCall(['--device=my-device', '--send=signals.txt'], 0);
        $this->expectNoOtherCalls();

        $client->send('signals.txt');

        $this->assertTrue(true);
    }

    /**
     * @covers IguanaIr\Client::send
     */
    public function testSendSignalToChannels()
    {
        $client = $this->getMockedClient();

        $this->expectCall(['--channels=0x05', '--send=signals.txt'], 0);
        $this->expectNoOtherCalls();

        $client->send('signals.txt', [1, 3]);

        $this->assertTrue(true);
    }

    /**
     * @covers IguanaIr\Client::send
     * @covers IguanaIr\CommandFailedException
     */
    public function testCommandFailureThrowsException()
    {
        $client = $this->getMockedClient();

        $this->expectCall(['--send=signals.txt'], 1);
        $this->expectNoOtherCalls();

        try {
            $client->send('signals.txt');

            $this->fail('Command should fail');
        } catch (CommandFailedException $e) {
            $this->assertInstanceOf(Process::class, $e->getProcess());
        }
    }

    /**
     * Returns a client instance with a mocked igclient command.
     *
     * @param string|null $device Device name
     *
     * @return Client
     */
    private function getMockedClient($device = null)
    {
        $this->commandFilename = tempnam(sys_get_temp_dir(), 'igclient');
        $this->callCountFilename = tempnam(sys_get_temp_dir(), 'igclient');

        file_put_contents($this->callCountFilename, '0');

        file_put_contents($this->commandFilename, '<?php'."\n");
        file_put_contents($this->commandFilename, sprintf(
            '$c = file_get_contents(\'%s\');'."\n",
            $this->callCountFilename
        ), FILE_APPEND);
        file_put_contents($this->commandFilename, sprintf(
            'file_put_contents(\'%s\', ++$c);'."\n",
            $this->callCountFilename
        ), FILE_APPEND);

        Client::setCommand('php '.$this->commandFilename);

        return new Client($device);
    }

    /**
     * Adds an expected call to the igclient command.
     *
     * @param string[] $arguments List of expected arguments
     * @param int      $exitCode  Exit code which the command should return
     */
    private function expectCall(array $arguments, $exitCode)
    {
        $conditions = [];
        $index = 1;
        foreach ($arguments as $argument) {
            $conditions[] = sprintf(
                'isset($argv[%1$d]) && $argv[%1$d] === \'%2$s\'',
                $index++,
                $argument
            );
        }

        $code = sprintf(
            'if (%d == $c && %s) { exit(%d); }'."\n",
            $this->callCount++,
            implode(' && ', $conditions),
            $exitCode
        );

        file_put_contents($this->commandFilename, $code, FILE_APPEND);
    }

    /**
     * Sets no more expected calls to the igclient command.
     */
    private function expectNoOtherCalls()
    {
        $code = 'fwrite(STDERR, "Invalid call count or arguments specified: ".$c.", ".var_export($argv, true)); exit(250);'."\n";

        file_put_contents($this->commandFilename, $code, FILE_APPEND);
    }
}
