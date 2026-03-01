## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-15 - [Shadowing Resources with Objects]
**Learning:** Reassigning a resource variable to a new object (e.g., `$destination = new Stream($destination)`) causes subsequent `is_resource($destination)` checks to fail. In the original `Stream::save`, this led to incorrectly closing externally provided resource handles because the logic assumed it had opened the resource itself when the check failed.
**Action:** Always track resource ownership with an explicit boolean flag (e.g., `$opened = true`) instead of relying on type checks if the variable might be reassigned to an object.
