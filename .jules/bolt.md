## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-15 - [Massive speedup with stream_copy_to_stream]
**Learning:** Iterative `read()`/`write()` loops for stream copying are extremely slow in PHP compared to `stream_copy_to_stream()`. For a 10MB file, the difference was ~16.5s vs ~0.035s. Also, reassignment of resource variables to objects can break resource management logic (e.g., `is_resource()` checks).
**Action:** Use `stream_copy_to_stream()` for high-throughput data transfers between resources. Keep track of resource ownership explicitly to ensure only internally opened resources are closed with `fclose()`.
