## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2026-03-22 - [Performance Pattern: stream_copy_to_stream]
**Learning:** For stream-to-stream data copying in PHP (e.g., `Stream::save` or `SeekableStream::toMemoryStream`), use `stream_copy_to_stream()` instead of iterative `read()`/`write()` loops to maximize throughput, minimize memory usage, and handle non-seekable streams correctly.
**Action:** When copying data between streams, always check if `stream_copy_to_stream` can be used. It is significantly faster as it is implemented in C at the PHP engine level.

## 2026-03-22 - [Resource Management Logic]
**Learning:** In `Stream::save`, reassigning the `$destination` variable (a resource) to a `new Stream()` object caused logic errors in resource management because subsequent `is_resource()` checks were performed on the object instead of the original resource handle.
**Action:** In methods that accept both resource handles and file paths, track whether the resource was opened internally to ensure `fclose()` is only called on resources that were opened within that method, preventing invalid handle errors on externally managed resources.
