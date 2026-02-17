<?php

use ApkParser\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    private $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'stream_test');
        file_put_contents($this->tempFile, 'Hello World');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testSaveToPath()
    {
        $fp = fopen($this->tempFile, 'r');
        $stream = new Stream($fp);

        $destFile = tempnam(sys_get_temp_dir(), 'dest_test');
        $stream->save($destFile);

        $this->assertEquals('Hello World', file_get_contents($destFile));
        $this->assertTrue(is_resource($fp), 'Source stream should still be open');

        unlink($destFile);
        fclose($fp);
    }

    public function testSaveToResource()
    {
        $fp = fopen($this->tempFile, 'r');
        $stream = new Stream($fp);

        $destFile = tempnam(sys_get_temp_dir(), 'dest_test_res');
        $destFp = fopen($destFile, 'w+');

        $stream->save($destFp);

        $this->assertTrue(is_resource($destFp), 'Destination resource should NOT be closed by save()');

        fclose($destFp);
        $this->assertEquals('Hello World', file_get_contents($destFile));

        unlink($destFile);
        fclose($fp);
    }

    public function testSaveFromMiddleOfStream()
    {
        $fp = fopen($this->tempFile, 'r');
        fseek($fp, 6); // Move to 'World'
        $stream = new Stream($fp);

        $destFile = tempnam(sys_get_temp_dir(), 'dest_test_mid');
        $stream->save($destFile);

        $this->assertEquals('World', file_get_contents($destFile));

        unlink($destFile);
        fclose($fp);
    }
}
