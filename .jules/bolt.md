## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Extreme Slowness in Stream Copying]
**Learning:** Copying streams byte-by-byte in PHP (especially when wrapped in object methods) is incredibly slow. `Stream::save` was taking ~1.8s per MB because it performed 1 million reads and 1 million writes.
**Action:** Use `stream_copy_to_stream()` for any stream-to-stream data transfer. It is implemented in C and can be hundreds of times faster than manual PHP loops.
