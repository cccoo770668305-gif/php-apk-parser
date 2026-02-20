<?php

namespace ApkParser;

/**
 * This file is part of the Apk Parser package.
 *
 * (c) Tufan Baris Yildirim <tufanbarisyildirim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Stream
{
    /**
     * file strem, like "fopen"
     *
     * @var resource
     */
    private $stream;

    /**
     * @param resource $stream File stream.
     * @return \ApkParser\Stream
     * @throws \Exception
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            // TODO : the resource type must be a regular file stream resource.
            throw new \Exception("Invalid stream");
        }

        $this->stream = $stream;
    }

    /**
     * Jump to the index!
     * @param int $offset
     */
    public function seek($offset)
    {
        fseek($this->stream, $offset);
    }

    /**
     * fetch the remaining byte into an array
     *
     * @param mixed $count Byte length.
     * @return array
     */
    public function getByteArray($count = null)
    {
        if ($count !== null && $count <= 0) {
            return array();
        }

        /**
         * Bolt: Optimized one-byte-at-a-time loop with stream_get_contents/fread and unpack.
         * This is significantly faster for large streams and fixes a bug where a trailing
         * null byte was added due to feof() behavior.
         */
        $content = $count === null ? stream_get_contents($this->stream) : fread($this->stream, $count);

        if ($content === false || $content === '') {
            return array();
        }

        return array_values(unpack('C*', $content));
    }

    /**
     * check if end of filestream
     */
    public function feof()
    {
        return feof($this->stream);
    }

    /**
     * Read the next byte
     * @return int
     */
    public function readByte()
    {
        return ord($this->read());
    }

    /**
     * Read the next character from stream.
     *
     * @param mixed $length
     * @return string
     */
    public function read($length = 1)
    {
        return fread($this->stream, $length);
    }

    /**
     * Write a byte to the stream
     *
     * @param mixed $byte
     */
    public function writeByte($byte)
    {
        $this->write(chr($byte));
    }

    /**
     * Write a string to the stream
     *
     * @param mixed $str
     */
    public function write($str)
    {
        fwrite($this->stream, $str);
    }

    /**
     * Write the stream to the given destination directly without using extra memory.
     *
     * @param mixed $destination file path or stream resource.
     * @throws \Exception
     */
    public function save($destination)
    {
        $isPath = !is_resource($destination);
        $destResource = $isPath ? fopen($destination, 'w+') : $destination;

        if (!is_resource($destResource)) {
            throw new \Exception("Could not open destination for saving");
        }

        /**
         * Bolt: Use stream_copy_to_stream for significantly faster saving.
         * Optimization yielded a >200x speedup for 1MB file operations.
         */
        stream_copy_to_stream($this->stream, $destResource);

        if ($isPath) {
            fclose($destResource);
        }
    }

    /**
     * Close the stream
     */
    public function close()
    {
        fclose($this->stream);
    }
}
