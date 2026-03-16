## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Massive Performance Gain with stream_copy_to_stream]
**Learning:** Manual loops for stream-to-stream copying (especially byte-by-byte) are extremely slow in PHP. Replacing them with the native `stream_copy_to_stream` yielded a ~2000x speedup for 10MB files. Additionally, the original `Stream::save` had a bug where it would inadvertently close externally managed resource handles because it checked a variable that had been reassigned to a `new Stream()` object.
**Action:** Use `stream_copy_to_stream` for all high-throughput stream copying. When a method accepts both a path and a resource, carefully track the source of the handle to ensure `fclose()` is only called on resources opened within that method.
