## 2025-05-15 - Stream I/O Optimizations
**Learning:** Replacing byte-by-byte loops with bulk operations (, , ) provides a massive performance boost in PHP, even when the data must ultimately be converted back to an array.
**Action:** Always look for iterative  or  calls in core I/O classes and replace them with bulk equivalents.
## 2025-05-15 - Stream I/O Optimizations
**Learning:** Replacing byte-by-byte loops with bulk operations (stream_get_contents, unpack, stream_copy_to_stream) provides a massive performance boost in PHP, even when the data must ultimately be converted back to an array.
**Action:** Always look for iterative fread(1) or fwrite() calls in core I/O classes and replace them with bulk equivalents.
