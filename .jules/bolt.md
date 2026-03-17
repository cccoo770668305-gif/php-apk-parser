## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Optimized Stream Copying]
**Learning:** In `Stream::save`, reassigning the `$destination` variable (a resource) to a `new Stream()` object caused logic errors in resource management because subsequent `is_resource()` checks were performed on the object instead of the original resource handle.
**Action:** Use `stream_copy_to_stream` for high-throughput copying and track whether resources were opened internally to ensure `fclose()` is only called on resources that were opened within the method.
