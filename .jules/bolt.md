## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-22 - [Variable-length string pool prefix parsing]
**Learning:** Android's `ResStringPool` uses variable-length prefixes for string lengths. For UTF-8, it's 1 or 2 bytes each for UTF-16 and UTF-8 lengths. For UTF-16, it's 1 or 2 shorts. Blindly skipping bytes or searching for null terminators is both slow and brittle.
**Action:** Always use the length-prefix encoding to determine exact read sizes. If the high bit is set (0x80 for bytes, 0x8000 for shorts), the length spans two units. Bulk read after determining the correct length.
