## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Small Buffer Stream Copying]
**Learning:** Using a manual loop with a small buffer (e.g., 128 bytes) for stream-to-stream copying is significantly slower than PHP's native `stream_copy_to_stream()`. Furthermore, manual implementations that over-read the buffer and attempt to `fseek` back on the source stream will fail if the source is non-seekable (e.g., a pipe).
**Action:** Use `stream_copy_to_stream($src, $dest, $length)` for efficient and safe stream copying. It is implemented in C and handles the length limit correctly without over-reading.
