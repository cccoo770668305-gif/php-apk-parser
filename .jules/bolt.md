## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-21 - [PHP 8 Null Byte Comparison and String Pool Optimization]
**Learning:** In PHP 8+, comparing a binary null byte (`"\x00"`) to the integer `0` using non-strict comparison (`==`) returns `false`. This breaks legacy logic that expects null bytes to be falsy when compared to numbers. Additionally, scanning for null terminators byte-by-byte in string pools is extremely slow due to function call overhead.
**Action:** Use length-prefix parsing according to the binary specification instead of null-terminator scanning. This enables bulk `read($length)` operations, which are significantly faster and avoid the null-byte comparison pitfall.
