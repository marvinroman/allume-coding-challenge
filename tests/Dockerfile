FROM alpine:3.10

RUN apk add curl 

ENV LOGDIR /var/log/tests
ENV LOGFILE test.log 
ENV TESTSDIR /usr/local/share/tests

COPY usr /usr 
RUN chmod +x /usr/local/bin/*

CMD /usr/local/bin/tests > ${LOGDIR}/${LOGFILE}