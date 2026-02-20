## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-17 - [Inefficient Stream Copying]
**Learning:** Manual loops for copying data between streams (even with small buffers like 128 bytes) are extremely slow in PHP due to the overhead of many function calls and buffer management in user-land. Replacing a byte-by-byte loop in `Stream::save` with `stream_copy_to_stream` yielded a ~640x speedup for 1MB files.
**Action:** For all stream-to-stream data transfers, use `stream_copy_to_stream()` to maximize throughput and minimize memory usage.
