## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-19 - [Stream Copy Optimization]
**Learning:** Manual loops for copying data between streams in PHP (e.g., using fread/fwrite) are significantly slower than the built-in 'stream_copy_to_stream()' function. Iterating one byte at a time can be over 200x slower for even modest 1MB files. Furthermore, manual loops that use fseek to rewind after over-reading chunks will fail on non-seekable streams like Zip entry streams.
**Action:** Always use 'stream_copy_to_stream()' for stream-to-stream data transfer to ensure maximum performance and compatibility with non-seekable streams.
