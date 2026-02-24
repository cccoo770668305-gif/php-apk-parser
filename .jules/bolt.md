## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-24 - [Massive speedup with stream_copy_to_stream]
**Learning:** Manual loops for copying data between streams (especially byte-by-byte) are extremely slow in PHP due to function call overhead. Additionally, reassigning a resource variable to an object wrapper before checking `is_resource` breaks resource management logic.
**Action:** Always use `stream_copy_to_stream()` for data movement between resources. It is implemented in C and much faster. Correctly track whether a resource was opened internally to ensure proper cleanup.
