## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-17 - [Manual Stream Copying Overhead]
**Learning:** Manual loops for copying stream data (using `fread`/`fwrite` or `read`/`write`) are significantly slower than built-in PHP functions and prone to logic errors in resource management. For example, `Stream::save` incorrectly attempted to close external handles because it wrapped them in an object and checked `is_resource` on the object instead of the handle.
**Action:** Use `stream_copy_to_stream()` for efficient stream-to-stream data transfer. It is implemented in C, handles non-seekable streams better, and minimizes function call overhead. Always track if a resource was opened internally to ensure `fclose()` is only called on resources the method is responsible for.
