FROM node:22-alpine

#RUN mkdir -p /home/node/app/node_modules && chown -R node:node /home/node/app
RUN mkdir -p /home/node/app/node_modules

WORKDIR /home/node/app

#COPY --chown=node:node package*.json ./
COPY package*.json ./

#USER node
RUN rm -rf /home/node/app/result
RUN npm install

#COPY --chown=node:node . .
COPY . .

EXPOSE 8080

#CMD [ "node", "index.js" ]