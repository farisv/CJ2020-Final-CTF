FROM node:12-alpine

ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

RUN set -x \
    && apk update \
    && apk upgrade \
    && apk add --no-cache \
    chromium \
    harfbuzz \
    udev \
    ttf-freefont

WORKDIR /app
COPY package.json /app/package.json
COPY package-lock.json /app/package-lock.json
RUN npm install --production

COPY index.js /app/index.js

CMD ["node", "index.js"]
