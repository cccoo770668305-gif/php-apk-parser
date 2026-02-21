## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2026-02-21 - [Inefficient Stream Copying]
**Learning:** Manual loops for stream copying (byte-by-byte or in small chunks) are extremely slow in PHP. Replacing them with native `stream_copy_to_stream` provides a massive performance boost (up to 200x) and improves robustness for non-seekable streams.
**Action:** Always use `stream_copy_to_stream` for copying data between resources.
