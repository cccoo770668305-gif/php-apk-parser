## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-14 - [High-Throughput Stream Saving]
**Learning:** Using `stream_copy_to_stream` for stream-to-stream data transfer (e.g., `Stream::save`) provides a massive (~440x) performance boost over manual byte-by-byte loops by leveraging PHP's internal optimizations and reducing function call overhead.
**Action:** Always prefer `stream_copy_to_stream` for bulk data movement between PHP streams.
