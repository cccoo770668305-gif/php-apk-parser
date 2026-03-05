## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Inefficient Stream Copying and Resource Management]
**Learning:** Manual loops for copying stream data (especially byte-by-byte) are extremely slow in PHP. Additionally, reassigning a resource variable to an object (e.g., `$destination = new Stream($destination)`) can break `is_resource()` checks and lead to premature resource closure.
**Action:** Use `stream_copy_to_stream()` for efficient data transfer between streams. Always track whether a resource was opened internally to ensure `fclose()` is only called on resources the library is responsible for.
