## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-25 - [Inefficient Stream Copying]
**Learning:** Using manual loops to copy data between streams in PHP (especially byte-by-byte or with small buffers like 128 bytes) is extremely slow due to function call overhead. `stream_copy_to_stream()` is a native function that performs this operation in C, providing a massive speedup (up to 2000x for 10MB files).
**Action:** Always use `stream_copy_to_stream()` when copying data between streams. Also, be careful when wrapping resources in objects within a method, as it can break `is_resource()` checks used for resource management.
