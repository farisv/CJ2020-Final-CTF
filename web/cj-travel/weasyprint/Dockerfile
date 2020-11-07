FROM python:3.7.3-alpine

RUN apk add --no-cache \
    ca-certificates \
    gcc \
    musl-dev \
    jpeg-dev \
    zlib-dev \
    libffi-dev \
    cairo-dev \
    pango-dev \
    gdk-pixbuf-dev \
    ttf-freefont

WORKDIR /app
COPY requirements.txt /app/requirements.txt
RUN pip install -r requirements.txt

USER nobody
CMD ["python3", "/app/app.py"]
