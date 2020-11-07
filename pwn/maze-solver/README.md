# Maze Solver

## Deploy

```
docker-compose up -d --build
```

## Solution

There is out of bound during the process of depth-first algorithm to solve the maze. We also can control the symbol for the path of maze solution. A malicious maze can be crafted so the return address in the stack frame will be overwritten with the path symbol. There is a gadget for read file in the binary that can be used to read the flag file.

See [solution.txt](solution.txt) for example solution.
