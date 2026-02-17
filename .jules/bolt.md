## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-17 - [Inefficient Stream Copying]
**Learning:** Copying a stream in PHP using a byte-by-byte loop (`while(!$this->feof()) { $dest->write($this->read()); }`) is extremely slow due to massive overhead of function calls and small I/O operations. For a 1MB file, this resulted in >1.8s execution time.
**Action:** Always use `stream_copy_to_stream()` for copying data between resources. It is implemented in C and can be >200x faster for large streams.
