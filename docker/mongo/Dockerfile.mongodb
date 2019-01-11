FROM mongo:3.4

ARG mongodb_username
ARG mongodb_password
ARG mongodb_initdb_database

COPY mongo-setup.js /docker-entrypoint-initdb.d/

RUN sed -i -e "s/MONGODB_USERNAME/$mongodb_username/g" /docker-entrypoint-initdb.d/mongo-setup.js
RUN sed -i -e "s/MONGODB_PASSWORD/$mongodb_password/g" /docker-entrypoint-initdb.d/mongo-setup.js
RUN sed -i -e "s/MONGO_INITDB_DATABASE/$mongodb_initdb_database/g" /docker-entrypoint-initdb.d/mongo-setup.js
