# Sorting Game

## Deploy

```
docker-compose up -d --build
```

## Solution

- Out of bound
- Binary search to leak stack address
- Overwrite return address in the stack frame
