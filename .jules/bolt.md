## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2026-02-27 - [Stream Copy Optimization]
**Learning:** Using `stream_copy_to_stream` for stream-to-memory-stream operations is significantly faster (3.5x-6x) than manual loops reading small chunks (e.g., 128 bytes). It also eliminates the need for manual `fseek` on the source stream when copying a specific length, as the native function handles the internal pointer correctly and doesn't require the source to be seekable if it's already at the right start position.
**Action:** Always prefer native PHP stream functions like `stream_copy_to_stream` over iterative `fread`/`fwrite` loops for data transfer between streams.
