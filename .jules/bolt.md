## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-03-18 - [Byte-by-Byte Stream Copying]
**Learning:** Copying data between streams byte-by-byte or using very small buffers (e.g., 128 bytes) in PHP is extremely inefficient due to high function call overhead. Additionally, reassigning resource variables to objects in a method that accepts both can break `is_resource()` checks for final cleanup.
**Action:** Use `stream_copy_to_stream()` for high-throughput stream-to-stream data transfer. Track whether a resource was opened internally to ensure `fclose()` is only called on resources that are not managed by the caller.
