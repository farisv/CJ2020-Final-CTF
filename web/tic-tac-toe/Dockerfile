FROM node:12-alpine

WORKDIR /app
COPY package.json /app/package.json
COPY package-lock.json /app/package-lock.json
RUN npm install --production

COPY index.js /app/index.js
COPY src /app/src
COPY public /app/public

CMD ["node", "index.js"]
