## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-16 - [Iterative String Scanning Overheads]
**Learning:** Scanning for null terminators byte-by-byte in a binary stream is extremely slow in PHP due to high function call overhead (`readByte()` or `read(1)` in a loop). Furthermore, binary formats like Android's StringPool often provide length prefixes that are ignored by naive implementations.
**Action:** Always check the binary format specification for length prefixes. Use these prefixes to perform bulk `read($length)` operations and then apply encoding conversions (like `mb_convert_encoding`) to the entire string at once for a significant performance boost.
