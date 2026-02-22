## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Inefficient Stream Copying]
**Learning:** Copying data between streams using manual loops (even with small buffers like 128 bytes) is significantly slower than using PHP's native `stream_copy_to_stream()` function, which is implemented at the C level. For 1MB files, the speedup can be over 600x compared to byte-by-byte loops.
**Action:** Use `stream_copy_to_stream()` for any stream-to-stream data transfer. It is more efficient, handles non-seekable streams better, and reduces PHP-level function call overhead.
