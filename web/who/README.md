# WHO

## Deploy

Run `docker-compose up -d` and then set up the WordPress + RSVPMaker 8.0.5 plugin by yourself.

## Scenario

During the competition, there were two flags in this problem. Both require zero day vulnerability (unknown vulnerability at that time - no CVE!).

- First flag is the guest name of a private event. We need to find IDOR to get it.
- Second flag is in the database. We need to find SQL injection vulnerability by reviewing the RSVPMaker 8.0.5 source code. There are multiple bugs, but but only one team managed to get the flag by the end of the competition.
