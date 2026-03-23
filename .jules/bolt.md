## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Iterative Stream Copying]
**Learning:** Copying data between PHP streams using iterative `read()` and `write()` loops (especially byte-by-byte or with small buffers like 128 bytes) is extremely slow due to high function call overhead in PHP.
**Action:** Use `stream_copy_to_stream()` for high-throughput stream copying. It moves the loop into PHP's native C code, providing massive performance gains (up to 2400x for 10MB in benchmarks). Also, ensure proper resource management by tracking whether resources were opened internally.
