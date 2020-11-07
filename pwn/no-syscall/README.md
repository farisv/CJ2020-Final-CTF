# No Syscall

## Deploy

```
docker-compose up -d --build
```

## Solution

We can run shellcode but without syscall. However, we know the memory address of the flag so we can brute force byte per byte with inferential analysis (check true/false with delay or error).
