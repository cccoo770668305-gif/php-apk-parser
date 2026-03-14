## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Inefficient Stream Copying]
**Learning:** For copying data between streams (e.g., saving to a file or into a memory buffer), iterative `read`/`write` loops in PHP are extremely slow (~17s for 10MB) compared to native `stream_copy_to_stream` (~0.01s). Also, manual lifecycle management of external vs. internal resources must be handled carefully with an explicit `$opened` flag to avoid closing resources owned by the caller.
**Action:** Use `stream_copy_to_stream()` for all stream-to-stream data transfers. Ensure methods that accept both paths and resource handles only `fclose()` what they `fopen()`.
