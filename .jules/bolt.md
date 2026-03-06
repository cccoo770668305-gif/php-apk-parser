## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-22 - [Stream Copy Optimization]
**Learning:** Using iterative `read()`/`write()` loops for stream-to-stream data copying in PHP is extremely slow due to function call overhead and data passing between PHP and the underlying buffers. `stream_copy_to_stream()` is a native function that handles this efficiently at the engine level. Additionally, when a method reassigns a variable that holds a resource to an object, subsequent `is_resource()` checks will fail, leading to resource leaks or logic errors.
**Action:** Always prefer `stream_copy_to_stream()` for copying data between PHP streams. Maintain a clear distinction between resource handles and wrapper objects to ensure correct resource management.
