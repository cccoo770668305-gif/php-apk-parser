## 2025-02-15 - [Inefficient Byte Array Conversion]
**Learning:** Reading a binary stream one byte at a time using `fread(1)` in a loop is extremely slow in PHP due to function call overhead. Additionally, the original implementation had a bug where `while (!$this->feof())` combined with `readByte()` would append an extra `0` byte at the end of every stream, because `feof()` only becomes true *after* a failed read.
**Action:** Always prefer bulk reading with `stream_get_contents` or `fread($h, $size)` and converting to an array with `unpack('C*', $data)` when a byte array is needed. This is faster and avoids the trailing zero bug.

## 2025-02-15 - [Massive Speedup with stream_copy_to_stream]
**Learning:** Manual `while (!$this->feof()) { $dest->write($this->read()); }` loops are extremely slow in PHP (10MB took ~17.8s). Additionally, reassigning a variable that holds a resource to a new object (e.g., `$destination = new Stream(...)`) can break subsequent `is_resource()` checks on the original resource.
**Action:** Use `stream_copy_to_stream()` for high-throughput copying (10MB took ~0.11s, a 160x speedup). Always track resource status in a separate boolean variable if the original variable might be reassigned.
