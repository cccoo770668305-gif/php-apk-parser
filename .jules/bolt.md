## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-05-20 - [Inefficient Stream Copying and Resource Management Bug]
**Learning:** Iterative byte-by-byte copying between streams is a major bottleneck in PHP. Replacing manual loops with `stream_copy_to_stream` significantly reduces overhead. Additionally, reassigning a resource variable to an object (as seen in the original `Stream::save`) breaks `is_resource()` checks, leading to incorrect resource management and potential invalid handle errors.
**Action:** Use `stream_copy_to_stream` for high-performance data transfer between resources. Maintain strict separation between resource handles and wrapper objects to ensure correct ownership and closing logic.
