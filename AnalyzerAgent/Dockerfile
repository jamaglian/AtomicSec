FROM node:22-alpine

RUN mkdir -p /home/node/app/node_modules

WORKDIR /home/node/app

COPY package*.json ./

ENV CHROME_BIN="/usr/bin/chromium-browser"\
    PUPPETEER_SKIP_CHROMIUM_DOWNLOAD="true"

RUN apk add --no-cache chromium \
    && npm install

COPY . .

EXPOSE 8080