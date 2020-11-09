# Lab Virus

## Deploy

```
docker-compose up -d --build
```

## Solution

- Use format string vulnerability to leak stack address
- Use one-byte heap overflow to corrupt the heap metadata
- Allign the corrupted heap so the return address in the stack frame can be overwritten
