FROM alpine:3.10

RUN apk add curl 

COPY usr /usr 
RUN chmod +x /usr/local/bin/*

CMD /usr/local/bin/tests